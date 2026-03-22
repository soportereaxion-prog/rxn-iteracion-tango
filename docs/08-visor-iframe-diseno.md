# AUDITORÍA Y DISEÑO: VISOR DE CONSOLA EMBEBIDO (IFRAME)

## 1. Auditoría del comportamiento actual

**Archivos analizados:**
- `csv/index.php`
- `csv/index_reprocesos.php`

**Diagnóstico funcional:**
Ambos archivos poseen un formulario HTML clásico que envía un `POST` contra la variable global `$_SERVER['PHP_SELF']` (`action="index.php"`). En la cabecera final del script (aproximadamente línea 94 a 104), se evalúa `if (isset($_POST['Procesar']))`.

Dentro de este bloque `IF`, el script instancia el procesamiento sincronizado masivo llamando a los métodos del modelo secuencialmente:
1. `$modelo->vacioClientes()`
2. `$modelo->procesoCsvClientes()`
3. `$modelo->procesoCsvArticulos()`
4. `$modelo->procesoPedidos(...)`

**Origen de los `echo`:**
Efectivamente, el origen del volcado directo a pantalla viene de los métodos internos ubicados en `csv/modelo.php`, notablemente desde `$this->procesoPedidos()` e `$this->ingresoFactura() / $this->ingresoPedido()`. Como Apache está enviando el buffer HTTP en tiempo real al navegador sin `ob_start()`, cada `echo` cae en crudo debajo del layout de todo el sitio, destrozando la experiencia visual y forzando el scroll infinito.

## 2. Objetivo y Restricciones
- **No se tocará** `csv/modelo.php`, garantizando la inviolabilidad del flujo de pedidos y de Tango.
- **Se retendrá** el método `POST` original y la lógica secuencial actual.
- Todo vestigio impreso (*echo*) por el modelo quedará canalizado hacia un Iframe con scroll interno propio y fondo oscuro, sin alargar verticalmente la pantalla padre bajo ningún concepto.

## 3. Propuesta de Implementación Quirúrgica

### A. Adaptar el Formulario Padre (`csv/index.php` y `csv/index_reprocesos.php`)
1. **Targeting del Iframe**: Se agregará el atributo `target="visor_consola"` a la etiqueta `<form>`. Esto fuerza a que la respuesta del POST no detone la pestaña principal, sino que se direccione exclusivamente dentro de un marco.
2. **Flag de Reconocimiento**: Se añadirá un input oculto `<input type="hidden" name="is_iframe" value="1">` opcionalmente si hiciera falta diferenciar el render (por lo general el framework con purgar el layout post-ejecución tiene garantizada la mitigación).
3. **Inyectar Receptáculo Dinámico**: Finalizado el formulario, se colocará la caja contendora:
   ```html
   <div class="mt-4">
       <iframe name="visor_consola" id="visor_consola" style="width: 100%; height: 500px; border-radius: 8px; background-color: #1e1e1e; border: 2px solid #ccc;"></iframe>
   </div>
   ```

### B. Cortafuegos de Rendereo (`backend`)
En el mismo `csv/index.php` donde el código realiza el `if (isset($_POST['Procesar']))`:

1. **Inicialización Visual de Consola**: Se inyecta como prólogo un blindaje HTML customizado que tiña el recuadro dinámicamente de estética shell (`#00FF00` en fondo `#1e1e1e`), con fuente especial para los logs, preservando su aislamiento del CSS del padre.
2. **Ejecución Intacta**: Acto seguido, se deja fluir la core-logic legacy.
   ```php
   $modelo->vacioClientes();
   $modelo->procesoCsvClientes();
   // Continúa la avalancha natural de echo provistos por modelo.php de manera síncrona
   $modelo->procesoPedidos('**');
   ```
3. **Muerte Prematura (Aborto de Cascada de Headers)**: Se debe colocar al final de la ejecución la directiva **`exit;`** por obligación del intérprete.
   *Razón:* Porque el request POST lo absorbió internamente el Iframe. Si se omite `exit;`, PHP seguirá devolviendo el output remanente de `index.php` (el header visual superior, librerías, links, botoneras secundarias), incrustando literalmente un sitio duplicado adentro de nuestra pantallita de logs. Forzando una detención intencional, aseguramos la transmisión hermética de los `echo` en estado puro.

## 4. Archivos a modificar
Solo las vistas nativas visuales por donde se entra, sin tocar ni una línea del motor API.
- `csv/index.php`
- `csv/index_reprocesos.php`

## 5. Riesgos mitigados
- **Riesgo:** Pérdida de actualización del listado de archivos encolados remanentes (los que preinforma la lista en la página padre), dado a que la pestaña principal ignorará que un iframe resolvió tareas críticas tras cerrarse por `exit`.
- **Mitigación:** Proveer a la cúpula del Output del Iframe la directiva de despachar un pequeño script Javascript post-mortem, donde se inserte un comando o botón `<button onclick="window.parent.location.reload()">Refrescar Panel Padre</button>`, permitiendo al individuo actualizar y liquidar el cache local de la vista general una vez auditado el log del recuadro.

## 6. Procedimiento
1. Insertar el Atributo `target` e `<iframe>` visual al HTML de `index.php`.
2. Envolver el contenido lógico del post insertándole el output cabecera estilo CSS-Console y frenando todo render colateral inferior con `exit`.
3. Repetir espejo contra la visual de `index_reprocesos.php`.
