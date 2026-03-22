# [Pedidos/Facturas] — [Reinicialización de reproceso desde misma pantalla]

## Contexto
El usuario testeando el sistema en vivo detectó que si blanquea sus tablas de base de datos (`RXN_CSV`, `RXN_API_CTRL`) con la intención de re-correr un lote fallido, y acto seguido aprieta el botón de "Procesar" que le había quedado cargado en la UI (sin refrescar), el sistema falla devolviendo mensajes engañosos como "No hay archivos" o directamente cortando el ciclo.

## Problema
- **Primer ingreso:** La carga de la URL `index.php` corre en el backend `$modelo->leo_ingreso_directorio_csv()`, lo que mapea mágicamente qué archivos `.csv` hay tirados en `ladyarchivos/` y los inserta crudos a `RXN_CSV` como estado `I`.
- **Reproceso / Submit POST:** Al apretar el botón, el backend viaja directo a `procesar.php`. Este script estaba diseñado **asumiendo que los archivos ya figuraban en la DB**, por lo tanto le echaba un `TRUNCATE` a los clientes, y procesaba derecho viejo con `procesoPedidos()`.
Pero si el usuario blanqueó la tabla intencionalmente antes del POST, la base estaba vacía y la tabla jamás se volvía a poblar porque esa lógica es exclusiva de la vista index.

## Decisión
En vez de reescribir un ciclo de vida complejo ni tocar configuraciones cURL o lógicas profundas, la solución más nativa es **asegurar que el método receptor del formulario corrobore siempre el repoblado de directorio** antes del pre-proceso. La base tiene bloqueos por primary key y `IF NOT EXISTS` nativo en el insert del CSV, así que llamarlo múltiples veces seguidas o en paralelo no duplica absolutamente nada y regenera instantáneamente el estado `I` de cualquier archivo visible pero borrado.

## Archivos afectados
* `csv/procesar.php`

## Implementación
Se insertó un llamado al reconstructor de lote previo a la limpieza de tablas dentro de la cabecera de ejecución:
```php
/* Re-inicializo estado del directorio al procesar para soportar testing sin salir de la pantalla */
$modelo->leo_ingreso_directorio_csv();
```

## Impacto
El usuario ahora puede abrir el sistema, mandar el submit, entrar a SSMS y borrar toda la DB las veces que quiera, que el botón de Procesar volverá a resincronizar lo que haya en la carpeta física y lo tragará de nuevo.

## Riesgos y residuales
Cero. El sproc incrustado en el modelo ya contemplaba en su T-SQL un `IF NOT EXISTS` riguroso, por lo que invocarlo doblemente frente a archivos viejos o ya cargados simplemente va a fallar de forma lícita y performante devolviendo 0 fields en el affected rows. 

## Validación
- Entrar al panel `Lectura de CSV`.
- Apagar/vaciar manual el contenido de la db `RXN_CSV`.
- Presionar `Procesar` directamente sin usar F5.
- La consola inferior informará que el proceso fue exitoso, ya que repobló transparente al vuelo los documentos.
