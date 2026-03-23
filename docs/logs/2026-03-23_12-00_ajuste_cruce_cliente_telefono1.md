# Ajuste de Cruce Condicional de Cliente (CSV -> Telefono_1)

## Contexto y Error Detectado
La funcionalidad condicional por la cual verificábamos importes > $250 estaba mapeando la identidad del cliente (enviada al Payload de PUT) basándose unívocamente en la llave primaria detectada en el propio flujo (el valor `$cod_cliente` que venía de la matriz y se cruzaba con el `codcliente` del CSV Maestro).

El error yacía en un detalle de la capa de arquitectura local: **Tango no guarda a sus entidades en GVA14 bajo la clave bruta del CSV.** Históricamente, el sistema de integración heredado fue configurado para tomar esa llave original, y guardarla contra el campo físico compensatorio **`TELEFONO_1`** en la tabla SQL `GVA14`.

## Corrección Aplicada
Se alteró la llave de cruce de actualización dentro de la función interceptora principal (`evaluarYActualizarClienteAPI`) para que se comporte como un enrutador entre los dos mundos:

1. El script localiza la fila en el padrón `cli_csv` matcheando contra el código que viene impreso en el pedido diario original.
2. Captura el valor crudo del padrón `valor_csv[0]` (ej. "84323") de ese match.
3. Se dispara una consulta sucia hacia el backend invocando la query `SELECT ... FROM GVA14 ... WHERE TELEFONO_1 = '84323'`.
4. Si y sólo si el motor devuelve respuesta positiva, el payload se construye abstrayendo ahora el *verdadero* `COD_CLIENT` oficial emitido y resguardado por Tango, al igual que su `ID_GVA14` de metadatos.

## Archivos Auditados y Modificados
- `csv/modelo.php`

## Pruebas de Flujo Simuladas (Resultados)
- **Caso > $250 con codcliente válido:** El motor localiza la columna 0, ejecuta un `$this->busco_cliente()`, reesculpe el JSON incluyendo el "COD_CLIENT" genuino re-hidratado, y frena por bandera `DEBUG_CLIENTE_UPDATE`, mostrando exactamente todas las etapas en el UI.
- **Caso de Inconsistencia de Datos en BD:** Se simuló interceptar un cliente que no estuviera volcado sobre GVA14. Validando `is_array()` contra el statement estricto el proceso aborta dócilmente en pantalla indicando: *"Error: No existe ningún cliente en Tango asociado al TELEFONO_1..."* sin paralizar el procesamiento del ticket de cabera.

## Estado Final de la Propiedad DEBUG
Debido a que el Payload es ahora lo suficientemente seguro y maduro para ser emitido contra Tango, `$DEBUG_CLIENTE_UPDATE` se retiene intencionalmente en `true` sólo hasta que un simulacro real por parte de jefatura testifice un Output en verde que justifique accionar el interrutor general y liberar el curl request `PUT`.
