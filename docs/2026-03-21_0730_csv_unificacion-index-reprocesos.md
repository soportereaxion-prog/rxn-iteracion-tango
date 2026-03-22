# Módulo CSV — Unificación Visual Reprocesos

## Contexto real
A partir de la exitosa iteración de la pantalla piloto (`csv/index.php`), donde se reemplazó el obsoleto diseño tabular y de fieldsets con el nuevo CSS mínimo y limpio (`rxn-ui.css`), se procedió a unificar visualmente la interfaz de *Reprocesos* del mismo módulo. Todo debía mantener identidad visual sin introducir variaciones de comportamiento de fondo.

## Problema resuelto
El módulo CSV principal había quedado estéticamente desfasado en su interfaz secundaria de reprocesos. La misma conservaba la etiqueta `<fieldset>`, inputs sin adaptabilidad, botones web tradicionales y nulo soporte para responsividad, chocando con el nuevo estándar recién aplicado a la pantalla principal.

## Decisión tomada
Replicar de manera incondicional el patrón visual validado.
- Vincular la misma hoja propia de utilidades `rxn-ui.css`.
- Descartar el layout veterano e implementar `.rxn-container` y `.rxn-card` como envoltorios de presentación.
- Acomodar elementos interactivos del `form` bajo el control `.rxn-action-bar` aplicando a los elementos nativos (inputs de tipo texto y botones submit) sus clases prefijadas `.rxn-input`, `.rxn-btn`, y `.rxn-btn-primary`.

## Impacto en producción
- **Riesgo:** Completamente NULO. La lógica de PHP contenida en `procesar_reprocesos.php`, el mecanismo inyector para logs hacia el iFrame y todos los nombres de variables procesadas han permanecido inalterables y opacos. La alteración transcurrió puramente a nivel de nodos HTML y atributos de clase requeridos por el CSS. 
- **Estandarización:** Se generó uniformidad con el módulo padre logrando consolidar el primer patrón visual formal `rxn` en todo el flujo de ingreso de facturación masiva.

## Validación de éxito
Al navegar hasta "Reprocesar Archivos CSV", se percibirá una experiencia idéntica visualmente y en flexibilidad (respuestas fluidas ante achicamientos de marco y layout estilo "card blanca" posada sobre fondo claro) que la provista en la carga de archivos inicial del día. El botón Submit se desvanecerá estéticamente de la misma manera mostrando de forma persistente la alerta preventiva de "Procesando..." cuando el usuario accione los envíos, eliminando inconsistencias lógicas en uso de escritorio y móvil.
