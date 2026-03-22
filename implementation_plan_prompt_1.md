# Estabilización Linux/Docker — rxnLadyApi (Primera etapa conservadora)

## Descripción

El sistema PHP legacy funciona en Windows y presenta incompatibilidades puntuales para ejecutarse en Linux/Docker. El objetivo de esta etapa es **únicamente** corregir esas incompatibilidades operativas sin alterar ninguna lógica de negocio, cálculos impositivos, ni estructura de datos.

Se aplican cambios quirúrgicos: **5 bloques** en 2 archivos.

---

## Cambios propuestos

---

### csv/modelo.php

#### [MODIFY] [modelo.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php)

**Cambio 1 — Líneas 2627–2639: Backslashes en escritura de archivos JSON**

Bloque actual:
```php
$fecha = "archivos_json\\" . date('Y-m-d-H_i_s');
//
if (file_exists("fc_json.txt")) {
    $archivo = fopen("archivos_json\\fc_json.json", "a");
    fwrite($archivo, PHP_EOL . "$data_string $formateado");
    fclose($archivo);
} else {
    $archivo = fopen($fecha . ".json", "w");
    fwrite($archivo, PHP_EOL . "$data_string");
    fclose($archivo);
}
```

Problemas:
- `archivos_json\\` falla en Linux (`\\` se interpreta mal en paths del FS).
- Si el directorio `archivos_json` no existe en el contenedor, `fopen` devuelve `false` silenciosamente y `fwrite(false, ...)` genera un error fatal o advertencia sin trazabilidad.

Propuesta (solo se cambian: separadores + `if (!$fh)` de guarda):
```php
$dir_json = 'archivos_json';
if (!is_dir($dir_json)) {
    mkdir($dir_json, 0755, true);
}
$fecha = $dir_json . '/' . date('Y-m-d-H_i_s');
//
if (file_exists("fc_json.txt")) {
    $fh_json = fopen($dir_json . '/fc_json.json', 'a');
    if ($fh_json !== false) {
        fwrite($fh_json, PHP_EOL . "$data_string $formateado");
        fclose($fh_json);
    }
} else {
    $fh_json = fopen($fecha . '.json', 'w');
    if ($fh_json !== false) {
        fwrite($fh_json, PHP_EOL . "$data_string");
        fclose($fh_json);
    }
}
```

Por qué es seguro: solo cambia el separador y agrega guarda de `false`. El flow de negocio (qué JSON escribe, cuándo, con qué contenido) no se toca.

---

**Cambio 2 — Líneas 2610–2617: Control de error en `curl_exec` de `ingresoFactura`**

Bloque actual:
```php
$data = curl_exec($ch);
// ...
$data2 = json_decode($data, true);
$this->mensaje_api = $data2;
```

Problema: si `curl_exec` devuelve `false` por error de red o timeout, `json_decode(false, true)` devuelve `null` si‌ no se maneja. `$this->mensaje_api` queda en `null`, y todo el bloque de control posterior (`$this->mensaje_api['Succeeded']`, etc.) genera notices/warnings que pueden ocultar el error real.

Propuesta (solo agrega detección y log; no cambia la asignación a `mensaje_api`):
```php
$data = curl_exec($ch);

/* Hardening Linux: si curl falla, loguear y dejar mensaje_api vacío */
if ($data === false) {
    $curl_error = curl_error($ch);
    $log_msg = '[' . date('Y-m-d H:i:s') . '] CURL ERROR en ingresoFactura'
             . ' | Pedido: ' . $nro_pedido
             . ' | Error: ' . $curl_error;
    error_log($log_msg);
    $data2 = null;
} else {
    $data2 = json_decode($data, true);
}

$this->mensaje_api = $data2;
```

Por qué es seguro: el `$data2` solo se asigna de forma diferente si hay error de red. Si no hay error, el flujo es **idéntico al original**. La lógica de `Succeeded`/`grabo` no se toca.

---

**Cambio 3 — Líneas 1633–1640: Control de error en `curl_exec` de `ingresoArticulo`**

Mismo patrón que el Cambio 2, para el método `ingresoArticulo()`.

Bloque actual:
```php
$data = curl_exec($ch);
// ...
$data2 = json_decode($data, true);
$this->mensaje_api = $data2;
```

Propuesta idéntica al cambio 2 pero con contexto de artículo:
```php
$data = curl_exec($ch);

/* Hardening Linux */
if ($data === false) {
    $curl_error = curl_error($ch);
    $log_msg = '[' . date('Y-m-d H:i:s') . '] CURL ERROR en ingresoArticulo'
             . ' | Artículo: ' . $cod_articulo
             . ' | Error: ' . $curl_error;
    error_log($log_msg);
    $data2 = null;
} else {
    $data2 = json_decode($data, true);
}

$this->mensaje_api = $data2;
```

---

**Cambio 4 — Líneas 898–906: Robustez en escritura de `detalle_proceso.txt`**

Bloque actual:
```php
if (file_exists("detalle_proceso.txt")) {
    $archivo = fopen("detalle_proceso.txt", "a");
    fwrite($archivo, PHP_EOL . "$mensaje_txt");
    fclose($archivo);
} else {
    $archivo = fopen("detalle_proceso.txt", "w");
    fwrite($archivo, PHP_EOL . "$mensaje_txt");
    fclose($archivo);
}
```

Problema: si el proceso corre desde un directorio de trabajo distinto al esperado (frecuente en Docker), `fopen` puede devolver `false` silenciosamente.

Los dos bloques `if/else` son **funcionalmente idénticos** (el modo `"a"` en PHP crea el archivo si no existe). Se pueden unificar con guarda mínima:

```php
$fh_log = fopen("detalle_proceso.txt", "a");
if ($fh_log !== false) {
    fwrite($fh_log, PHP_EOL . "$mensaje_txt");
    fclose($fh_log);
} else {
    error_log('[' . date('Y-m-d H:i:s') . '] No se pudo escribir detalle_proceso.txt');
}
```

Por qué es seguro: `fopen("...", "a")` en PHP **crea el archivo si no existe**, por lo que el `file_exists` original era redundante. El contenido escrito es idéntico.

---

### configuraciones/modelo.php

#### [MODIFY] [modelo.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/configuraciones/modelo.php)

**Cambio 5 — Línea 81: Backslash en lectura de directorio XML**

Bloque actual:
```php
$ruta_de_la_carpeta = $this->leoParametroBd('RUTAXML') . '\\';
```

Problema: en Linux, `\\` concatenado como string PHP produce `\` literal en el path, que no es válido como separador de directorio.

Propuesta:
```php
$ruta_de_la_carpeta = rtrim($this->leoParametroBd('RUTAXML'), '/\\') . '/';
```

El `rtrim` limpia cualquier separador que ya venga al final del valor en BD (sea `/` o `\`), y luego agrega `/`. Esto funciona en ambos sistemas operativos.

Por qué es seguro: ninguna lógica de negocio depende del separador elegido aquí. Solo se usa para `opendir()`.

---

## Validación

> No existen tests automatizados en el proyecto.

### Verificación manual (sin necesidad de procesamiento real)

1. **Rutas JSON (Cambio 1):** Desde el contenedor Linux, verificar que el directorio `archivos_json/` se cree automáticamente si no existe, al ejecutar el proceso de facturas.
2. **CURL (Cambios 2 y 3):** Desconectar temporalmente la API de Tango (o apuntar a IP inválida en `RXN_PARAMETROS.RUTA_LOCAL`) y verificar que aparece el mensaje de error en el log de PHP (`error_log`) sin excepción fatal.
3. **Log detalle_proceso.txt (Cambio 4):** Al finalizar un ciclo de procesamiento, verificar que `detalle_proceso.txt` existe y contiene entradas.
4. **Lectura de directorio (Cambio 5):** Acceder a `configuraciones/index.php` y verificar que el listado de archivos del directorio RUTAXML funciona correctamente sin warnings.

---

## Puntos NO tocados (reportados)

| Punto | Razón |
|---|---|
| Acumuladores `art_total_*` en `ingresoFactura` | Interacción compleja entre pedidos. El reset actual al final funciona. **No se toca.** |
| `generoJsonCompleto()` | Código duplicado pero explícitamente excluido del alcance. **No se toca.** |
| SQL Injection en `leoParametroBd()` y otros | Requeriría cambios en la firma de métodos existentes. Fuera de esta etapa. **Reportado.** |
| `closeCursor()` después de `return` | Código muerto. No causa fallo. Fuera de esta etapa. **Reportado.** |
| Credenciales hardcodeadas en `ConectarBase.php` | Problema de seguridad real pero fuera del alcance de estabilización Linux. **Reportado.** |
| `CURLOPT_VERBOSE true` en `ingresoArticulo` (línea 1628) | Activo en producción, genera output de debug. Podría desactivarse, pero es solo cosmético. **Reportado, no se toca.** |

> [!IMPORTANT]
> **Acumuladores**: se decidió **no tocar** el reseteo de acumuladores. El comportamiento actual (reset al final de `ingresoFactura`) está validado en producción. Moverlo a un método separado implica riesgo de cambiar el orden de ejecución si se llama en otros puntos. Queda anotado para una segunda etapa cuando exista cobertura de tests.
