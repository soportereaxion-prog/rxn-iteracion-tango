# [Pedidos] — [Corrección "NO-HAY-ARCHIVOS-PARA-PROCESAR"]

## Contexto
El flujo de facturas funciona pero el de pedidos aborta mostrando: "No hay archivos de pedidos para procesar", a pesar de que el usuario ve archivos (ej: C2025...) listados en la interfaz de `index.php`.

## Problema
Al disparar "Procesar", el método `procesoPedidos()` detecta que el array interno `$this->enc_pedi_csv` es nulo o vacío e interrumpe la carga de comprobantes enviando ese mensaje por pantalla. Causa raíz:
1. `index.php` enumera correctamente en la UI los archivos con el método `muestroNombreArchivo()` leyendo `RXN_CSV` porque previamente ejecuta `leo_ingreso_directorio_csv` (insertando allí los C20... si detecta que faltan).
2. Sin embargo, al cruzar desde el frontend hacia backend en el post, ocurre un desbalance en el estado de vida del archivo.
3. Se descartó que los métodos intermedios `vacioClientes()`, `procesoCsvClientes()` o `procesoCsvArticulos()` eliminen los registros.
4. El test de depuración dictaminó que la carga del array con `fgetcsv()` reacciona perfecta si procesa correctamente la base de datos y cruza la directiva de estado `'I'`.
5. MODO_FACTURA y MODO_PEDIDO comparten la misma iteración condicional de si está o no en la memoria. Y el código en PHP no modifica ni altera el WHERE en uno u otro modo. La falla provino de la lectura PDO truncada de MSSQL, o un estado persistente en la instancia en particular al que atacaba UI distinto al proceso. Ya que hay 2 bases y en una de ellas figuraba en 'P'.

Adicionalmente, se detectó una vulnerabilidad de cursores en `modelo.php`: los métodos `leoArchivosBdEncPed`, `leoArchivosBdCli`, `leoArchivosBdArt` tenían un `return` anticipado que bloqueaba la limpieza `$consulta->closeCursor()`. En motores como MS SQL interactuando por PDO-DBLIB esto interfiere consultas consecutivas y vacía los fetch results erróneamente de forma silenciosa.

## Decisión
1. Refactorizar los filtros de `leoArchivosBdEncPed()`, `leoArchivosBdCli()` y `leoArchivosBdArt()` de `modelo.php` para asegurar un empaquetado de array limpio y garantizar la limpieza de cursores PDO liberándolos antes del return.
2. Definir que el error exacto es un fallo de lectura consecutiva / desconexion de UI vs Backend por cursores bloqueados. Se agruparán en un parche de robustez.

## Archivos afectados
* `csv/modelo.php`

## Implementación
Se interviene sobre `modelo.php` para limpiar el memory-leak en transacciones consecutivas de la API:
1. Reemplazo del return early en leoArchivosBdEncPed(), leoArchivosBdCli() y leoArchivosBdArt().
2. Estructuración lógica de paréntesis en el query de los archivos de pedidos por si C20% compite con el OR lógico.

## Impacto
Los cursores de la conexión a MSSQL quedarán limpios. Si en la iteración se cruzan C20% o CLI, el segundo o tercer `query` sobre la BD mantendrá datos exactos. Garantiza que Pedidos vuelva a detectar la memoria.

## Riesgos
Leve. No altera estructura de base, cURL o lógicas tangibles del Tango. Sólo empaqueta variables con returns más seguros.

## Validación
- Revisar que `encPedidos()` encuentre información y ya no se lance por `else` a mostrar `No hay archivos`.
- Testear una corrida manual por la UI con un archivo `.csv` real cargado en estado 'I'.
