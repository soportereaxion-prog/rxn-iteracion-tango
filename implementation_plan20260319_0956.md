# Etapa 4 — Switch de Configuración: Pedidos vs Facturas (Plan Revisado)

## Descripción y Criterios Adoptados
Siguiendo las directivas, la meta es lograr una **orquestación limpia** en un flujo maestro unificado, donde el switch entre `FACTURA` y `PEDIDO` ocurra lo más tarde posible. Se respeta escrupulosamente la lógica funcional y fiscal de [ingresoFactura()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#1874-2813) heredando únicamente la infraestructura común (parsing, controles, logging) para [Pedidos](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#685-734).

---

## Cambios del Plan según Observaciones

### 1. Configuración Persistente y Semántica
*   **Base de Datos:** Se creará (mediante script provisto) el campo `MODO_PROCESO VARCHAR(20) DEFAULT 'FACTURA'` en `RXN_PARAMETROS`.
*   **Frontend ([configuraciones/index.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/configuraciones/index.php)):** Se añadirá el control visual `<select>` con opciones PEDIDO / FACTURA.
*   **SemánticaBackend ([configuraciones/modelo.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/configuraciones/modelo.php)):** *Cambio respecto a la versión anterior.* Para no forzar la naturaleza del método histórico [actualizoRutaXml](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/configuraciones/modelo.php#602-613), se creará un método nuevo `actualizarModoProceso($modo_proceso)` de una sola responsabilidad, o bien, si decidimos reemplazar el guardado global, renombraremos la función a `actualizarConfiguracionGeneral()`.
*(Estrategia elegida: Crear `actualizarModoProceso($modo)` y llamarlo desde el index de configuración luego del update principal. Esto es lo más limpio).*

### 2. Bifurcación Tardía en el Flujo Principal
En [procesoPedidos()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#785-916), ocurrirá **todo lo compartido**: lectura del CSV, array encabezado, [ctrlPedi](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#402-417) (para evitar duplicados), etc.
La bifurcación ocurrirá en el bloque central de inyección:

```php
if ($this->ctrlPediRxnApiCtrl['COD_COMP'] == '') {
    
    // --- BIFURCACIÓN TARDÍA ---
    $modo_proceso = $this->leoParametroBd('MODO_PROCESO') ?? 'FACTURA';
    
    if ($modo_proceso === 'PEDIDO') {
        
        // 1. Arma el cuerpo (Pedidos no calcula impuestos como factura)
        $this->buscoPedidoRXN($pedi_enc['N_COMP'], $pedi_enc['COD_CLIENT'], ...);
        
        // 2. Invoca a Tango API (Pedirá ajuste interno en la otra etapa)
        $this->ingresoPedido($pedi_enc['COD_CLIENT'], ..., $this->articulos, ...);
        
        // 3. Resolución de la rama PEDIDO
        // Se definirá lógica análoga pero sin renumeración manual.
        $id = $this->mensaje_api['savedId'] ?? 0;
        $stringConvertido = $this->convertirATexto($this->mensaje_api);
        $grabo = (!empty($this->mensaje_api['Succeeded'])) ? 1 : 0;
        
        $mensaje_api_str = 'ID_INTERNO: ' . $id . ' Mensaje: ' . $stringConvertido . ' ¿Grabó?: ' . ($grabo ? 'true' : 'false');
        $this->ingresoMensajesApi($pedi_enc['N_COMP'], 'PEDIDOS', $mensaje_api_str, $grabo, ...);

    } else {
        // --- FLUJO ORIGINAL EXACTO (FACTURA) ---
        $this->buscoPedido($pedi_enc['N_COMP'], $pedi_enc['COD_CLIENT'], ...);
        
        $this->ingresoFactura($pedi_enc['COD_CLIENT'], ..., implode($this->articulos), ...);
        
        // (Aquí sigue el bloque INTACTO original de facturas, que actualiza
        // ID manuales, genera JSON de debug, y llama a ingresoMensajesApi)
    }
} else {
    // Control de duplicado (compartido)
}
```

### 3. Cierre de la Rama PEDIDOS y el problema de `$this->mensaje_api`
**Diagnóstico Crítico:** Al revisar el código provisto de [ingresoPedido](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#1400-1554), este efectúa el `curl_exec` pero **no mapea** la respuesta hacia `$this->mensaje_api` (que es la variable de clase donde la API vuelca el JSON procesado).
En lugar de eso, hace un `print_r($response)` y un chequeo a `$http_code`.

**Solución en Etapas:**
1.  **En esta etapa (Switch / Orquestación):** Dejaremos preparado el bloque `if ($modo_proceso === 'PEDIDO')` mapeando el cierre esperando leer `$this->mensaje_api['Succeeded']` y registrando via [ingresoMensajesApi()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#509-543).
2.  **En la próxima etapa (Endurecimiento de ingresoPedido):** Deberemos tocar [ingresoPedido()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#1400-1554) para que decodifique `$response` vía `json_decode`, lo asigne a `$this->mensaje_api`, y suprima los `echo`/`print_r` que actualmente ensucian el output. Pero el *orquestador* (que diseñamos aquí) ya debe esperar esa estructura emulando la interoperabilidad de la factura.

### 4. [procesoPedidosRXN()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#2814-2921) en Desuso
No borraremos [procesoPedidosRXN()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#2814-2921). Quedará tal cual en [csv/modelo.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php), actuando como copia de seguridad del intento previo, para su remoción futura.

---

## Detalles de Implementación (Archivos)

1.  **[configuraciones/modelo.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/configuraciones/modelo.php):**
    *   Nuevo método `public function actualizarModoProceso($modo_proceso)` con `UPDATE RXN_PARAMETROS SET MODO_PROCESO = '$modo_proceso'`.
    *   Nuevo selector `public function leoParametroBd($nombre_col)` (ya existe; se reutiliza).
2.  **[configuraciones/index.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/configuraciones/index.php):**
    *   Agregado del combobox `<select name="modo_proceso">` leyendo la paramétrica actual.
    *   Agregado de llamada `$modelo->actualizarModoProceso($_POST['modo_proceso'])` si se guarda.
3.  **[csv/modelo.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php):**
    *   Inserción de la lógica condicional indicada en [procesoPedidos()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#785-916).

## Aprobación solicitada
Si este refinamiento cubre con precisión la semántica de guardado, la separación del código legado y las responsabilidades de la rama "PEDIDOS", indicame para proceder a su ejecución.
