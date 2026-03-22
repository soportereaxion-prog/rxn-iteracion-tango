# RCA y Corrección: Regresión en Ingreso de Pedidos
**Fecha:** 2026-03-22  16:33

## Síntoma Reportado
El "botón de Procesar ya no completa el flujo esperado; aparentemente no se está comunicando correctamente con la API o el proceso se corta antes". Facturas sigue funcionando normalmente, Pedidos está corrupto en su ejecución de capa de transporte.

## Contexto de la Regresión
Luego de la iteración en [2026-03-22_16-15] orientada a visibilizar resultados de Tango en el Iframe, se introdujeron `echo` y comandos de forzado de salida (`@flush()` y `@ob_flush()`) al hilo sincrónico de PHP que procesa todos los comprobantes de manera sucesiva en `csv/modelo.php`.

## Diferencias Estructurales (Facturas vs. Pedidos)
Analizando las ramas funcionales:
- `ingresoFactura` construye su XML/JSON transaccional de manera limpia en buffer.
- `ingresoPedido` posee **salidas nativas a pantalla (ecos) legadas en su código interno** previas al `curl_exec` (ej. `echo 'Llego hasta acá: <br>';` , `echo 'Artículo en encabezado: <br>';`).

## Causa Detectada
La conjunción de los comandos artificiales de inyección UI y los `flush()` que añadimos, provocaron que cualquier texto esparcido por el método legacy de Pedidos rompiera el buffer de volcado de la API cURL o forzara al Custom Error Handler del sistema a interceptar un Notice ("failed to flush buffer", causado por la arquitectura y ciclo de vida de los búferes anidados) enviándolo al visualizador antes de tiempo. Esto interrumpió silenciosamente el socket o impidió la finalización limpia del bucle para ese comprobante, provocando que la UI interpretara el ciclo como cerrado/colgado sin haber transferido la data satisfactoriamente. Facturas continuó operando de forma prístina al no tener ecos residuales en su preparación de payload.

## Corrección Aplicada (Rolback Parcial)
1. Con bisturí, se **removió y desarmó la inyección UI**, eliminando todo rastro del `echo` formateado, `$ui_color`, y especialmente el `@flush(); @ob_flush();` **exclusivamente** de la ramificación *Pedidos* dentro de `procesoPedidos()`.
2. Se reestableció el encadenamiento directo desde `fclose($fh_log)` hacia `$this->ingresoMensajesApi(...)`.

## Decisión sobre la Mejora Visual
Se retiene la mejora en el módulo de *Facturas*, ya que opera de manera aséptica. Para *Pedidos*, se revierte 100% al comportamiento clásico silencioso, acatando la directiva estricta de no corromper la funcionalidad matriz.

## Validación Manual
Reprocesar un lote puntual de formato *Pedido* (CSV "cabe...") cliqueando en Procesar. Comprobar que el proceso toma su tiempo natural, que el botón se des-bloquea al final (control del onload recuperado) indicando éxito en verde, y que la transferencia es efectivamente recibida por Tango. El Iframe nativamente presentará los logs clásicos (texto legacy) emitidos por `ingresoPedido`. Ningún riesgo residual estimado.
