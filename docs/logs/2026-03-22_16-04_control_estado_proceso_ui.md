# Módulo CSV — Control de Estado en UI

## Contexto
Durante la ejecución del ingreso de comprobantes (tanto en lectura normal como en reprocesamiento), el botón principal cambiaba a "Procesando..." y un mensaje inferior indicaba que la tarea estaba en curso. Sin embargo, al culminar el script backend en el iframe `visor_consola`, la interfaz no era notificada ni recuperaba su estado original, dejando al usuario con la duda de si el proceso había terminado o se había "colgado".

## Problema Detectado
El flujo `<body> -> <form target="visor_consola"> -> <iframe>` es unidireccional por HTML estándar. Ante la falta de una captura del evento de finalización del request dentro del iframe, la pantalla padre quedaba con el botón deshabilitado indefinidamente impidiendo corridas consecutivas o cierres claros.

## Análisis
Ambas pantallas de procesamiento (`csv/index.php` y `csv/index_reprocesos.php`) envían su POST hacia un iframe sin un callback asíncrono explícito (no se usa AJAX). La manera más limpia, robusta y menos invasiva de detectar el fin del proceso sin tener que reescribir ni tocar el backend (PHP) para forzar un postMessage, es colocar un evento nativo `onload` sobre el iframe receptor.

Para evitar que este evento dispare errores o se ejecute en la carga inicial de la página cuando el iframe carga por primera vez en blanco, es indispensable utilizar una bandera de control JS (`procesoIniciado`). Adicionalmente, aprovechando que el iframe y la ventana padre comparten origen (same-origin policy), podemos auditar sutilmente el cuerpo de la respuesta en la capa de interfaz (`iframe.contentWindow.document.body.innerText`) dentro de un bloque seguro `try/catch` para buscar palabras clave de falla que imprime el PHP de Tango ("error", "fatal", "exception") y comunicar al usuario el grado del éxito general.

## Solución Aplicada
1. Se añadió el listener `onload="finalizarProcesamiento()"` directamente al tag `<iframe>` existente que hace de visor de consola.
2. Se inyectó la variable global JavaScript `procesoIniciado` que arranca en `false`. Al clickear sobre el formulario (función `mostrarProcesando()`), se activa en `true`.
3. Se desarrolló la función JavaScript vanilla `finalizarProcesamiento()` que:
   - Restablece el valor o texto del botón al original ("Procesar").
   - Reactiva sus eventos con `pointer-events: auto` y opacidad 100%.
   - Analiza pasivamente el output del texto en el iframe inyectando un bloque informativo verde (éxito) o rojo (advertencias/errores) informando la culminación y recordando al usuario revisar los registros de la consola para más detalle.

## Alcance
Intervención circunscrita 100% sobre la capa de interfaz usuaria (HTML y Vanilla Javascript) en el directorio `/csv`.

## Validación Realizada
- [x] El botón queda deshabilitado temporalmente y cambia de color su bloque inferior a naranja previniendo el multiclick.
- [x] Al finalizar el proceso en el backend y culminar todo el renderizado en el iframe, el script localiza el fin y restaura inmediatamente los botones.
- [x] Se permite volver a clickear para reprocesar o ingresar otra fecha sin necesidad de dar F5 a la pantalla entera.
- [x] Un mensaje advierte que terminó en tonos verdes confirmando estabilidad operativa.

## Riesgos e Impacto
- **Impacto Backend/Lógica:** NULO. Las páginas de modelo y controladores que conectan con Tango (`procesar.php` y `procesar_reprocesos.php`) no fueron alteradas en un solo bit.

## Notas
Este simple pero crítico control de flujo sella la mayor causante de ambigüedad funcional en el módulo más usado del sistema, transformando un escenario incierto en un ciclo operativo predecible y estanco.
