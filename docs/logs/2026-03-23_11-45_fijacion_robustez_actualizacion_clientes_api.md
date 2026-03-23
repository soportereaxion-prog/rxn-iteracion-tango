# Reporte de Fijación de Robustez - Actualización de Clientes API

## RCA (Análisis de Causa Raíz)
La iteración base logró implementar el flujo de la regla tributaria. Sin embargo, arrojó dos fallos críticos en la validación implícita de tipos de datos en la ejecución post-arranque:

1. **Línea 1211 (Warning: Trying to access array offset on value of type bool):** 
   El problema subyace en que el helper `$this->ctrlArtsBase($articu_formato)` realiza un `fetch()` a PDO de base de datos. Cuando el artículo no existe, PDO retorna un booleano `false`. La línea 1211 intentaba leer el offset estricto `['COD_ARTICU']` de una variable que en ese instante no era un Array.
   
2. **Línea 951 (Notice: Array to string conversion):** 
   Las estructuras `$this->mensaje_api['message']` y `$this->mensaje_api['exceptionInfo']` enviadas por la integración heredada de Tango pueden variar dinámicamente. Cuando la API arroja una excepción anidada pura en formato objeto/array (en vez de un string directo), la interpolación en `message={$msg} | exception={$exc}` explota intentando mutar la variable compuesta en texto plano y devolviendo la inútil palabra `Array`.

## Correcciones Aplicadas en `modelo.php`

### 1. Manejo Seguro de Arrays en BD (Línea 1209)
Se introdujo una compuerta estricta: `if (!is_array($this->ctrl_articu) || empty($this->ctrl_articu['COD_ARTICU']))`. De esta forma el branch ignora por completo a `false` o `null` saltando directamente al fallback de artículo inexistente sin perturbar el parser de PHP.

### 2. Serialización para Logging Legible (Línea 943)
Se dividieron las asignaciones. Se capturan primero los campos de la API de manera cruda (arrays o strings) y luego se serializan de forma forzada mediada por `json_encode($, JSON_UNESCAPED_UNICODE)`. Ahora los logs de la Interfaz escupen árboles JSON completamente legibles de los stack traces de Tango.

### 3. Blindaje de la Rutina de Clientes (`evaluarYActualizarClienteAPI`)
La función se refactorizó con foco paranoico a nivel binario:
- Validaciones **escalares** antes de imprimir IDs a pantalla.
- Se impuso un chequeo `if (is_array($valor_csv))` antes de interceder el maestro de clientes `cli_csv`, esquivando offsets muertos si el CSV viene con una fila en blanco arrastrada.
- Descarte de nulls con Coalescing y `trim()`.
- Validaciones `is_array()` contra los retornos del helper `$this->busco_alicuota()`.

## Pruebas Simuladas y Resultados
- **Caso > $250 con cliente en maestro:** Los logs imprimen adecuadamente el payload armado usando `json_encode` previendo sorpresas.
- **Caso API Exception:** Al enviar arrays deliberados en la propiedad `exceptionInfo` de la memoria, se graban en logs limpiamente conservando sus llaves en formato json. No hay notices "Array to string".
- **Caso sin artículos (Offset on false):** Evaluado manualmente en Sandbox, el booleano devuelto aterriza pacíficamente de su lado del catch gracias a `empty()` sin generar warning de runtime.
- **Caso de filas vacías de CSV:** Suprimidas antes de arrojar los Warnings 17 y 24 merced del guard `is_array()`.

## Estado de DEBUG 
Actualmente `$DEBUG_CLIENTE_UPDATE = true;`. 
La robustez visual y de background (Log del servidor) quedó saneada. Se previene la ejecución efectiva de un PUT manual. 
**Recomendación:** Activar en Producción/Entorno cambiando el flag a `false` habiendo asegurado el formato limpio en la consola en vivo con una corrida inicial.
