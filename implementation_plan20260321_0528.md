# Auditoría Técnica y Propuesta UX: Procesamiento CSV

## 1. Auditoría Técnica Actual

Se analizaron los documentos [csv/index.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/index.php), [csv/index_reprocesos.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/index_reprocesos.php) y [csv/vista.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/vista.php). A continuación, el detalle de cada punto solicitado:

1. **Ubicación real del formulario (`form`)**:
   - En **[index.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/index.php)**: El formulario se abre en la línea 78 (`<form action="procesar.php" method="post" target="visor_consola">`) y cierra en la línea 91.
   - En **[index_reprocesos.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/index_reprocesos.php)**: Inicia en la línea 76 (`<form action="procesar_reprocesos.php" method="post" target="visor_consola_reprocesos">`) y cierra en la línea 87.
   - En ambos casos, todo el form (calendario, botón y listado PHP) está encapsulado para enviar una misma petición `POST` hacia un iFrame de destino.

2. **Botón de submit actual**:
   - Elemento: `<input type="submit" value="Procesar" name="Procesar" accesskey="P" />`.
   - Se encuentra idéntico en ambos archivos. No tiene un `ID`, por lo que su manipulación a futuro se debe realizar apuntando a su nombre o agregándole un `ID` explícito.

3. **Dónde se ejecuta [muestroNombreArchivo()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/vista.php#6-16)**:
   - En **[index.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/index.php)**: Diferido en la línea 86 mediante invocación directa al modelo: `$modelo->muestroNombreArchivo();`.
   - En **[index_reprocesos.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/index_reprocesos.php)**: Invocado en la línea 83 con el método alternativo: `$modelo->muestroNombreArchivoReproceso();`.

4. **Cómo se está renderizando actualmente ese output**:
   - El código en [vista.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/vista.php) hace un `echo` directo con el nombre de cada archivo concatenado con un retorno de carro HTML: `echo $archivo['NOMBRE_ARCHIVO'].'<br>';`.
   - Como resultado, cada línea de texto **se imprime desnuda y sin envolver** (sin un contenedor como `<div>` ni etiqueta formata) flotando directamente debajo del botón Submit, interrumpiéndo el layout formal del formulario.

5. **Múltiples puntos donde se imprime ese listado**:
   - No hay múltiples puntos. En ambos archivos la variable del modelo con la visualización del listado se llama exactamente _una_ única vez, justo en el bloque del flujo donde se configuran los envíos (previa etiqueta que muestra el link para salir).

6. **Ubicación actual del iframe**:
   - Está **fuera y por debajo** del bloque del `<form>`.
   - En **[index.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/index.php)**: Ubicado en la línea 94, envuelto en un `div` con `margin-top: 15px;`. Su nombre y ID son `visor_consola`.
   - En **[index_reprocesos.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/index_reprocesos.php)**: Ubicado en la línea 90, con id/name `visor_consola_reprocesos`.
   - En ambos sistemas funciona de forma independiente sin recargar el `form` gracias al atributo html `target`.

---

## 2. Propuesta de Mejoras UX

Esta propuesta garantiza mantener la lógica intacta del `modelo.php` y [vista.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/vista.php), respetando el web form _POST_ tradicional y asegurando que las integraciones productivas a Tango no se alteren (ni usando AJAX o frameworks que modifiquen el core).

### Mejora 1: Estado de procesamiento visible

**Modificaciones a nivel Frontend (HTML + JavaScript Vanilla puro en ambos index):**

1. **Añadir un ID al formulario** (ej: `id="form-procesar"`) y un **ID al botón submit** (`id="btn-procesar"`).
2. **Implementar un evento `onsubmit`** con Vanilla JS al final del `body` que haga lo siguiente de forma sincrónica:
   - Cambiar el valor/texto del botón Submit de "Procesar" a "Procesando comprobantes...".
   - Deshabilitar el botón Submit visualmente (`pointer-events: none` y `opacity: 0.6`). **NOTA:** Preferible no usar `disabled = true` en un input `submit` debido a que en PHP clásico eso a veces impide que la variable viajé por POST (`$_POST['Procesar']`), lo cual podría romper validaciones del core si se dependen de ello; en su lugar, podemos controlar el múltiple envío con una bandera JS.
   - Mostrar un bloque de texto que diga de forma dinámica: *"Procesando, por favor no cierre ni recargue esta pantalla..."*. Se puede lograr revelando un bloque oculto `<p style="display:none;" id="msg-procesando" class="msg-espera">...</p>`.

### Mejora 2: Encapsulado del listado de archivos

**Modificaciones a nivel Frontend (HTML + CSS):**

Para evitar el efecto visual desordenado que deja el listado libre, simplemente envolveremos su llamada actual.

1. **Añadir un contenedor HTML (`div`) con scrolling** envolviendo directamente la invocación:
   
   ```html
   <div style="max-height: 150px; overflow-y: auto; background: #fdfdfd; border: 1px solid #ccc; padding: 10px; margin: 10px 0; border-radius: 4px; font-family: monospace;">
       <?php $modelo->muestroNombreArchivo(); ?>
   </div>
   ```
   *(Aplica de modo igual para `$modelo->muestroNombreArchivoReproceso()`)*.
   
2. **Beneficios logrados**:
   - Al tener su propio `max-height` (ejemplo 150px) y usar un `overflow-y: auto`, si existen 500 comprobantes CSV para procesar, esto no generará una página larguísima obligando al usuario a scrollear la web infinita; quedará contenido en una pequeña consola.
   - Todo esto alinea mejor con los diseños visuales (incluso usando CSS Vanilla) y conserva la escritura del método clásico que tiene el PHP ([vista.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/vista.php)).

---

## Próximos Pasos

Si estás de acuerdo con la auditoría y esta propuesta de abordamiento:
Confirmame la aprobación de este plan para, en el próximo paso, sugerirte la modificación exacta únicamente de [index.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/index.php) e [index_reprocesos.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/index_reprocesos.php) para integrar estas visualizaciones UX.
