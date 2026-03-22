# Módulo CSV — Unificación Visual (Rechazar Pendientes)

## Contexto real
Continuando con la adopción estricta de la capa visual propia y encapsulada (`rxn-ui.css`), se procedió a refactorizar la tercera y última pantalla núcleo de la carga CSV (`index_rechazar_pendientes.php`). A diferencia de las anteriores, esta no emite logs hacia un elemento de `iFrame`, sino que ejecuta un flujo sincrónico directo enviando la solicitud POST contra sí misma (`PHP_SELF`).

## Problema resuelto
La vista resultaba inconexa y discordante, ya que era la única en el subdirectorio que conservaba el cascarón antiguo HTML (compuesto por el esqueleto `<fieldset>` y un layout en cascada rústico para su botonera). Mantenerla implicaba inconsistencias flagrantes en la consistencia de estilos del módulo.

## Decisión tomada y ejecutada
- Desterrar el elemento `<fieldset>` obsoleto.
- Implementar la arquitectura `rxn` anidada verificada en iteraciones anteriores (`.rxn-container`, `.rxn-card`, `.rxn-card-header` y `.rxn-card-body`).
- Anclar y estabilizar el botón principal encajándolo en una agrupación `.rxn-action-bar` combinada con su propio diseño oficial `.rxn-btn-primary`.
- El aviso de retroalimentación positivo o "echo" de finalización PHP fue retocado estéticamente in-line manteniendo colorimetría verde suave acorde al lenguaje de marca de `.rxn-badge-success`, sin agregar divs intrusivos ni complejidades extras.

## Impacto en producción
- **Riesgo Funcional:** Completamente Nulo. La directiva original que exige omitir rediseños sistémicos de flujo fue acatada puramente de frente. Las funciones nativas de `$modelo` así como también el re-envío en formato legacy continuaron sin un solo cambio en sus componentes de memoria.
- **Estandarización del Bloque Subyacente:** Toda la funcionalidad de ruteo `/csv/*.php` se encuentra ya divorciada del layout heredado. 

## Validación esperada
Al transitar a "Rechazar Pendientes" navegando el menú, la vista actual ostenta una tarjeta de bordes redondeados y una sombra de sutil elevación. El evento fundamental "Rechazar" adopta estado dinámico para hover de mouse en lugar de comportarse como un botón chato de S.O. Los reescalados bajo 480px se apilan obedientemente según las proporciones de ventana móvil asimilando por completo nuestra librería de CSS interno.
