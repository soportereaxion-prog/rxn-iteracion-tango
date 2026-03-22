# Segunda Etapa — Limpieza de Debug y Guardas de Array

## Descripción

Limpieza puntual de salidas de debug visibles en pantalla y guardas seguras de índices de array sin cambiar lógica de negocio.  
**1 archivo modificado:** [csv/modelo.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php)

---

## Hallazgos clasificados

### Echo ACTIVOS detectados

| # | Línea | Texto | Clasificación | Acción |
|---|---|---|---|---|
| A | 572 | `"Actualizo - Tipo Doc : ..."` | Debug de proceso — visible en pantalla | → `error_log()` |
| B | 1062 | `'Pedido: ... Orden: ... Nombre archivo: ...'` | Debug de proceso — por artículo | → `error_log()` |
| C | 1123 | `'Precio: ... Precio art: ...'` | Debug de variables de artículo | → eliminar |
| D | 1218 | `'Estoy en excento?'` | Debug de rama EX | → eliminar |
| E | 1237 | `'¿ Var $precio_art ? ...'` | Debug de variables de artículo | → eliminar |
| F | 1238 | `'¿ Var $precio ? ...'` | Debug de variables de artículo | → eliminar |
| G | 1239 | `'¿ Var $cant_x_precio_neto ? ...'` | Debug de variables de artículo | → eliminar |
| H | 1249 | `'No estoy en excento'` | Debug de rama no-EX | → eliminar |
| I | 1265 | `'¿ Var $art_negativo ? ...'` | Debug de variable de artículo | → eliminar |
| J | 919 | `'El pedido: ... ya existe.'` | **Mensaje funcional** de proceso | → **NO TOCAR** |
| K | 926 | `'No hay archivos de pedidos para procesar'` | **Mensaje funcional** de proceso | → **NO TOCAR** |
| L | 3540 | `echo $data_final;` | Debug — vuelca JSON masivo en pantalla | → eliminar |

### print_r ACTIVOS detectados

| # | Línea | Texto | Acción |
|---|---|---|---|
| M | 1267 | `print_r($articulos)` | Debug — vuelca array de artículos en pantalla | → eliminar |

### Accesos inseguros a arrays (notices/warnings)

| # | Línea | Expresión | Riesgo | Acción |
|---|---|---|---|---|
| N | 835 | `$this->ctrlPediRxnApiCtrl['COD_COMP']` | `Undefined index` si [ctrlPedi()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#405-420) devuelve array vacío `[]` | → `isset()` guarda |

---

## Cambios propuestos

### Archivo: [csv/modelo.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php)

---

**Cambio A — L572: echo de actTipoDocCliente → error_log**

El echo de "Actualizo - Tipo Doc" aparece en el HTML de respuesta del procesador. Tiene valor diagnóstico útil, por lo que se mueve a `error_log` en vez de eliminarse.

```diff
-            echo "Actualizo - Tipo Doc : " . $tipo_doc . " ID_TIPO_DOCUMENTO_GV : " . $id_t_d . " CUIT : " . $cuit_cuil_dni_sin_guiones . " Cód. Cliente Tango: " . $cli_interno . "<br>";
+            error_log('[' . date('Y-m-d H:i:s') . '] actTipoDocCliente | Tipo Doc: ' . $tipo_doc . ' | ID_TIPO_DOC: ' . $id_t_d . ' | CUIT: ' . $cuit_cuil_dni_sin_guiones . ' | Cod.Cliente: ' . $cli_interno);
```

---

**Cambio B — L1062: echo de buscoPedido → error_log**

Muestra por pantalla pedido+orden+archivo en cada artículo. Tiene valor diagnóstico; se mueve a error_log.

```diff
-        echo 'Pedido: ' . $pedido . ' Orden: ' . $orden_x . ' Nombre archivo: ' . $nombre_archivo . '<br>';
+        error_log('[' . date('Y-m-d H:i:s') . '] buscoPedido | Pedido: ' . $pedido . ' | Orden: ' . $orden_x . ' | Archivo: ' . $nombre_archivo);
```

---

**Cambios C-I — L1123, 1218, 1237, 1238, 1239, 1249, 1265: echo de variables de artículo → eliminar**

Son diagnósticos de variables intermedias del loop de artículos. No tienen valor para producción. Se eliminan directamente.

```diff
-                    echo 'Precio: ' . $precio . ' Precio art: ' . $precio_art . '<br>';
```
```diff
-                        echo 'Estoy en excento?';
```
```diff
-                        echo '¿ Var $precio_art ? ' . $this->formatearNumero($precio_art, 2) . '<br>';
-                        echo '¿ Var $precio ? ' . $this->formatearNumero($precio, 2) . '<br>';
-                        echo '¿ Var $cant_x_precio_neto ? ' . $this->formatearNumero($cant_x_precio_neto, 2) . '<br>';
```
```diff
-                        echo 'No estoy en excento';
```
```diff
-                        echo '¿ Var $art_negativo ? ' . $art_negativo . '<br>';
```

---

**Cambio M — L1267: print_r($articulos) → eliminar**

Vuelca el contenido completo del array de artículos en pantalla. Claramente debug. Se elimina.

```diff
-                        print_r($articulos);
```

---

**Cambio L — L3540: echo $data_final → eliminar**

Vuelca el JSON completo del batch en pantalla. Este método ([curlJsonCompleto](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#3491-3558)) está comentado en el flujo principal, pero si se llamara, ensuciaría la salida. Se elimina el echo; el JSON ya se envía a curl en la línea siguiente.

```diff
-        echo $data_final;
```

---

**Cambio N — L835: Guarda isset en acceso a COD_COMP**

Código actual:
```php
if ($this->ctrlPediRxnApiCtrl['COD_COMP'] == '') {
```

[ctrlPedi()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#405-420) puede devolver `[]` (array vacío) cuando no hay resultado. Acceder a `['COD_COMP']` sobre un array vacío genera `Undefined array key`.

Propuesta:
```php
if (($this->ctrlPediRxnApiCtrl['COD_COMP'] ?? '') == '') {
```

Se usa `??` (null coalescing), disponible desde PHP 7. Es la forma más mínima de agregar la guarda sin cambiar la lógica: si `COD_COMP` no existe, se comporta exactamente igual que si viniera vacío, que es la condición original.

> [!IMPORTANT]
> Este cambio **no altera el flujo**: antes el notice era "sin COD_COMP → acceso vacío → condición true". Después, con `?? ''`, el resultado es idéntico: vacío → condición true. Solo desaparece el notice.

---

## Puntos NO tocados

| Punto | Razón |
|---|---|
| `echo 'El pedido: ... ya existe.'` (L919) | Mensaje funcional de proceso — visible intencionalmente |
| `echo 'No hay archivos de pedidos para procesar'` (L926) | Mensaje funcional — visible intencionalmente |
| `echo 'Se procesó el cliente...'` (L1405) | Mensaje de estado de cliente — funcional |
| `echo 'Existe un error...'` (L1392/1709) | Mensajes de error visibles — funcionales |
| `echo 'Se grabó correctamente...'` (L1395/1713) | Mensajes de estado — funcionales |
| `echo 'No hay archivos de clientes...'` (L1414) | Mensaje funcional |
| `echo 'No hay artículos para procesar'` (L1759) | Mensaje funcional |
| Acumuladores `art_total_*` | Explícitamente excluido en esta etapa |

## Verificación

- Procesar un ciclo completo y verificar que la salida HTML no contiene "Precio:", "art_negativo", "Estoy en excento", ni volcados de array.
- Verificar `error_log` del servidor con los mensajes redirigidos.
- Verificar que el procesamiento de pedidos ya existentes sigue mostrando "El pedido X ya existe".
