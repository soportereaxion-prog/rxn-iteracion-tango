# Documentación del Módulo de Pedidos y Facturas API

## 1. Descripción General
El módulo actúa como el orquestador principal del proyecto `RXN Lady API`, encargado de leer archivos CSV provenientes de ventas o plataformas externas, procesarlos e inyectarlos directamente en el ERP Tango Gestión vía API REST y comandos SQL (PDO).
Recientemente, el módulo fue refactorizado para unificar dos mecánicas históricamente separadas: el ingreso de Facturas y el ingreso de Pedidos nativos. Para lograrlo, se implementó un switch de configuración global `MODO_PROCESO` que determina dinámicamente el destino de los datos (`FACTURA` o `PEDIDO`) aprovechando todo el bloque común de validación y control, mitigando bugs y abaratando costos de mantención.

## 2. Flujo de Proceso
El proceso comienza invocando a [procesoPedidos($menu)](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#785-900) y transita las siguientes etapas:
1. **Lectura de CSV:** [encPedidos($menu)](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#685-734) carga y filtra los archivos físicos depositados en la carpeta origen.
2. **Armado de Encabezado:** Se mapean las columnas del CSV hacia un array unificado (`$dato_pedi_enc`) que contiene datos como Cliente, Nro de Comprobante, Importes, Zonas y Bonificaciones.
3. **Control de Duplicados en BD:** Se ejecuta [ctrlPedi](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#402-417) buscando emparejar el número de comprobante original, para evitar que el mismo se cargue múltiples veces.
4. **Bifurcación Final (Switch):** El sistema lee localmente la configuración transaccional `MODO_PROCESO` (`$modo_proceso = $this->leoParametroBd('MODO_PROCESO')`).
   - Si es **PEDIDO**: Ejecuta el parser de artículos [buscoPedidoRXN()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#1015-1115) y finaliza inyectando a la API mediante [ingresoPedido()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#1500-1654).
   - Si es **FACTURA**: Ejecuta el parser [buscoPedido()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#1133-1370) y dispara el inyector histórico [ingresoFactura()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#1974-2913).
5. **Cierre y Trazabilidad:** Independientemente del camino escogido, si la API devuelve éxito (procesando el flag `Succeeded` de las respuestas), se registran los resultados a nivel tabla histórico con [ingresoMensajesApi()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#509-543) y se actualiza el archivo de estado físico.

## 3. Modo FACTURA
La funcionalidad histórica y de altísima compatibilidad del módulo para generar cobranza estricta de cara al facturador de Tango.
*   **Método inyector:** [ingresoFactura()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#1974-2913).
*   **Numeración manual:** Involucra métodos asincrónicos ([actIdFac()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#642-653)) encargados de asignar de forma correlativa forzada la numeración cruzada contra el talonario respectivo (B, Ecommerce, Expo).
*   **Lógica Fiscal Integrada:** Durante su orquestador de artículos [buscoPedido()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#1133-1370), el código evalúa pesados cálculos impositivos (IVA `art_total_10_50`, percepciones provinciales, control tributario sobre el catálogo especial `EX` o excluyente `SNC`, discriminación Consumidor Final).
*   **Armado de Payload Complejo:** El request viaja hacia `/FacturadorVenta/registrar` agrupando toda la información bajo nodos jerárquicos estrictos tales como: `ComprobantesRenglones`, `ComprobantesImpuestos`, o `PercepcionesRenglones`.

## 4. Modo PEDIDO
El flujo simplificado, modernizado y en etapa de homologación para inyección nativa al motor de pedidos comerciales.
*   **Método inyector:** [ingresoPedido()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#1500-1654).
*   **Sin Numeración Manual:** El módulo delega inteligentemente a Tango API la iteración de talonario; el sistema local solo se ocupa de mapear la traza con el `savedId` sin interrupciones asincrónicas.
*   **Cero Lógica Fiscal:** Despojado enteramente de cálculos manuales de IVA o multas IIBB, operando bajo el axioma de que el ERP es el responsable final del encuadre tributario basándose en el Perfil de facturación. Solo inyecta el neto pretendido.
*   **Armado Plano:** La lista es despachada en forma secuencial hacia el nodo simple `RENGLON_DTO`.
*   **Composición de Artículos:** Los renglones contemplan los items biunívocos extraídos del CSV, e inyectan además -como se estableció teóricamente y pendiente de finalización técnica- los artículos fijos derivados del pedido matriz de venta (bonificaciones globales que descuentan -`bonif_cosme`, `practicosas`, `bonif_adicional`- y variables logísticos que suman, como `gastadmin`).

## 5. Construcción Cruda: [buscoPedidoRXN()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#1015-1115)
Creado en sustitución a [buscoPedido()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#1133-1370), este iterador fue forjado específicamente para el _payload transparente_ de los Pedidos.
*   Se aboca a filtrar `$this->dato_pedi_cue` por el respectivo número de comprobante único temporal.
*   Construye de base a fin (bajo `[ $this->articulos[] ]`) un array PHP clásico y asociativo mapeando las variables básicas sin tocar acumuladores lógicos: `ID_STA11` resolviendo contra la base de datos de artículos, `CANTIDAD_PEDIDA`, o `PRECIO`.
*   Garantiza que el precio nunca pase a modo negativo bruto, manipulando limpiamente un multiplicador escalar `-1`.

## 6. Switch de Configuración
La orquestación operativa se apoya en el entorno global:
*   **Tabla DB:** `RXN_PARAMETROS` aloja la flag en la columna dedicada `MODO_PROCESO`.
*   **Dominio de Valores:** Opera en un bolead binario de texto `FACTURA` vs `PEDIDO`.
*   **Punto de Acceso:** Gobierna todo el comportamiento sin requerir compilaciones ni cambios en el core backend; el cliente final lo maneja mediante el portal visual "Configuración de directorio" empleando tecnología COMBOBOX.

## 7. Estado Actual
*   [x] **Configuración Persistida Activa:** El Frontend actualiza en vivo la tabla MySQL/SQLServer e [index.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/index.php) exhibe dinámicamente el selector.
*   [x] **Bifurcación (Switch) Instanciada:** Flujo unificado con cortes de ruta simétricos implementados a última etapa del bucle central.
*   [x] **[buscoPedidoRXN](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#1015-1115): Adaptadores Nativos Operativos.** Resuelve la transcodificación de datos al formato llano `RENGLON_Dto`.
*   [x] **URL Normalizadas:** [ingresoPedido()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#1500-1654) superó la etapa de hardcodings, adoptando idéntico ensamble y conexión subyacente que factura (`$this->token['RUTA_LOCAL']`).
*   [ ] **Endurecimiento Final de `ingresoPedido/Mensaje_API`:** PENDIENTE. Resta blindarse de warnings en CURL suprimiendo dumps crudos en vista como los prints manuales, y empaquetar de manera robusta su parseo hacia un array JSON homologable en `$this->mensaje_api` (de igual manera que Opera Factura en el cierre del proceso con flags tipo Succeeded y ErrorMessage).
*   [ ] **Inyección Fija:** Queda confirmar en la iteración contigua cómo sumar puntualmente las líneas por cargos administrativos de la factura hacia [buscoPedidoRXN](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#1015-1115).
