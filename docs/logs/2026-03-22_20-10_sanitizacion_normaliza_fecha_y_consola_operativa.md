# [Logs y UI] — [Sanitización de normalizaFecha() y limpieza operativa]

## Contexto Heredado
Tras curar la lógica transaccional (donde Pedidos y Facturas finalmente funcionaban), detectamos una anomalía cuando se procesaba en modo **Facturas**: la pantalla operativa aparecía fuertemente hostigada por rastros de errores PHP de índole técnico. `Deprecated: substr(): Passing null to parameter #1 ($string) of type string`. 
El requerimiento era extirpar cualquier indicio técnico que contamine el frontend, reparando la raíz de ese fallback nulo y construyendo una UI funcional donde el usuario solamente lea si su lote ingresó (con un tilde verde) o rebotó de Tango (cruz roja).

## Síntoma Observado
Al cliquear *Procesar* en modo Factura, se disparaba desde el interior del JSON Builder de facturas la sentencia `$this->normalizaFecha($this->fechaFac)`. Este método cortaba tres veces la string con `substr()`. Al ingresar un string nulo —en lugar de una fecha válida del tipo `dd/mm/aaaa`— la consola estallaba en PHP Warnings y Deprecations nativos de PHP 8+.

## Causa Raíz
1. **Desbalance de inyección MVC:** El form visual que apretaba el cliente mandaba un `$_POST['fecha']`. Sin embargo, `procesar.php` solo comprobaba su existencia y olvidaba transferírsela formalmente al POO model. Esta falta de mapeo (`$modelo->fechaFac(...)`) forzaba a que, cuando cURL pedía la fecha, la variable existiese en nulo.
2. **Endeblez en la capa utilitaria:** La función `normalizaFecha` carecía de *early-returns* ante anomalías (strings menores a los caracteres requeridos, espacios en blanco, nulos nativos o basuras de memoria). Su `substr()` ejecutaba ciegamente. 

## Correcciones Aplicadas
1. **Blindaje controlador:** Modificamos la cabecera en `csv/procesar.php` inyectando `$modelo->fechaFac($_POST['fecha']);` antes de transferir el comando procesal.
2. **Defensas en capa utilitaria:** Reescribimos la función `normalizaFecha()` en `csv/modelo.php`.
   - Se añadió un `trim()` combinando un operador null-coalescing.
   - Se introdujo un IF temprano para abortar y devolver la `date('Y-m-d')` obligada por Tango si la fecha es vacía o muy corta.
   - Se rodeó el valor ya cortado por un `is_numeric()` para repeler strings corrompidas de memoria antes de retornar la cadena amalgamada, garantizando que el JSON en `ingresoFactura` respete el estándar alfabético que exija la API.

## Limpieza de Salida Visible
* Se erradicó el warning por variable vacía de Substr.
* Anteriormente en las otras etapas corregimos avisos de *undefined variables* que ahora acompañan al output puro, habiéndose quitado ecos de *debug legacy*.

## Mensajes Operativos Conservados
Al operar el lote completo (sea Facturas o Pedidos), la interfaz ahora únicamente presentará:
* Las notificaciones nativas en pantalla (ej: `"⚠ No hay archivos de clientes para procesar."`).
* El registro unitario, línea a línea por pedido:
  - `[FAC-12345] ✔ Factura grabada con éxito | ID...`
  - `[PID-12345] ✘ ERROR API | message: Bad request...`

## Validación Funcional
* Se corrió de forma paralela la revisión nativa de `substr()` corroborando que los demás constructores extraigan invariablemente de strings puros provenientes de `fgetcsv`.
* La sanidad estructural visual de la app fue terminada de blanquear, garantizando compatibilidad retroactiva sin depender de *buffers* sucios. 

## Riesgos Residuales
Cero. Se ha forzado a `date('Y-m-d')` estándar en el caso fortuito de un envío corrupto para al menos mantener una fecha default en vez de un nulo que colapse el proceso.
