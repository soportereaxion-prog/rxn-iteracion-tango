# Actualización Condicional de Clientes API

## Contexto
Durante el procesamiento de comprobantes CSV (Pedidos/Facturas), la empresa requiere ajustar tributariamente los datos del cliente maestro subyacente de Tango siempre y cuando el comprobante involucrado contenga importes fiscales/comerciales que superen el umbral base prestablecido de `$250`.

## Objetivo
Interceptar la lectura del CSV Principal. Extraer los importes, sanitizarlos contra puntos de mil y comas decimales e inyectarlos en una lógica condicional. Si superan los `$250`, el sistema cruza los datos con el CSV temporal de Clientes (`CLI...csv`), extrae la *Categoría de Iva* y su *Alícuota de Percepción* para traducir ese binomio fiscal a un Payload limpio y prepararlo para la API de Tango.

## Remapeo del Repositorio
Se re-vinculó la terminal hacia el remoto `https://github.com/soportereaxion-prog/rxn-iteracion-tango.git`. Todos los comites a partir de la fecha apuntan hacia este origen que contiene el snapshot estabilizado de la APP.

## Archivos Auditados y Modificados
- `csv/modelo.php`

## Mapeo Confirmado (Ingeniería Inversa sobre CLI)
Se confirmó la discrepancia operativa observada en la UI de Tango VS el CSV:
- **`alic_perc` (Col 24 / Y):** Tango no espera este porcentaje crudo. Emplea la función `$this->busco_alicuota()` contra la tabla `GVA41`. El resultado numérico indexado en `tabla_alicuo['ID_GVA41']` se inyecta en el campo real API `ID_ALI_FIJ_IB`.
- **`cat_iva` (Col 17 / R):** Tango espera el ID. Si bien se mapea a `ID_CATEGORIA_IVA`, cuando su valor es estrictamente `10` (que corresponde a SNC o Sujeto No Categorizado), el endpoint exige mandar un campo secundario hermano: `ID_GVA41_NO_CAT = 1`, o de lo contrario revienta la integridad relacional de la DB.

## Implementación Detallada
Dentro de la fase lectora `procesoPedidos()`, instantes previos a que los flows se difurquen por MODO_PROCESO, se inyectó una iteración sobre todos los importes clave del `enc_pedi`:
`IMPORTE`, `BONIFCOSME`, `PRACTICOSAS`, `GASTADMIN`, `IMPORTE_GRAVADO`, `IMP_IVA`, `BONIF_ADIC`.

Si el parseo `floatval()` del dato sanitizado arroja $>250$, dispara el orquestador: `evaluarYActualizarClienteAPI($cod_cliente)`.

Esta función:
1. Re-usa dinámicamente el Array asociativo `$this->cli_csv`.
2. Busca secuencialmente al `$cod_cliente` involucrado.
3. Extrae Col 17 y 24.
4. Aplica el Mapeo Detectado.
5. Construye el Payload Json para el eventual `PUT`.

### Bandera de Seguridad (Safe Mode)
Toda la rutina se encuentra encapsulada tras una bandera dura `$DEBUG_CLIENTE_UPDATE = true;` imposibilitando alteraciones efectivas a las bases de Tango. La aplicación se limita a renderizar el log exacto en la grilla visual de la tabla CSV de la Interfaz para que la patronal pueda validar los cruces con sus propios ojos.

## Pruebas Mínimas Simuladas
- **Sanitización String:** Ingresos como `"2,500.50"` o strings nulos son casteados a float evitando Excepciones de Type Juggling de PHP 8.
- **Cache Hit de CSV:** La extracción de memoria de `$this->cli_csv` esquivó la necesidad de abrir con `fopen` y trabar los archivos I/O en cada vuelta del loop de Pedidos repetitivamente.

## Riesgos y Pendientes
El `PUT` definitivo se encuentra comentado y a la espera de un SDK Endpoint real en la API `/Api/Update?process=2117` u análogo que Tango provea para el verbo UPDATE. 

## Cierre
Iteración 16 aprobada. Esperando directivas para avanzar a FASE 17.
