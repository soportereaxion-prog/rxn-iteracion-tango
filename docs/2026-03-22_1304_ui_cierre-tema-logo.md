# Cierre de UI Principal — Tema Oscuro y Logo

## Contexto real
Se llega a la iteración final de unificación en la "Tanda 3" buscando dar elegancia visual sin invadir ni reescribir todo el sistema en un framework masivo, dotándolo de persistencia de perfiles (claro/oscuro) y preparando el terreno para la marca del usuario.

## Problema detectado
1. Interfaz nativamente estática sin soporte para perfiles visuales oscuros, lo cual era un requerimiento deseado.
2. Inexistencia de logotipo oficial que refuerce la identidad en la API principal.

## Decisión tomada
Dar soporte mediante CSS nativo configurando variables en `:root`. Setear por defecto el tema oscuro (sin clases asociadas) e inyectar `data-theme="light"` al elemento raíz `<html>` a discreción desde control JavaScript y LocalStorage, siendo la vía moderna más limpia y segura. Se incrusta la lógica pre-parpadeo (FOUC) en el `<head>` y se prepara el `<img>` referenciando `logo.png`.

## Implementación propuesta
- **Variables CSS**: En `rxn-ui.css` se definieron paletas dark en la jerarquía general base `:root` y paletas light en `[data-theme="light"]`. Por lo expuesto, `Oscuro` es el amo y señor original.
- **Logo**: El logotipo `logo.png` se asienta sobre la cabecera `rxn-flex-between`, junto con los textos del título. Está configurado para auto-ocultarse sin romper si la imagen todavía no fue dispuesta en el root.
- **Header dinámico**: El switch bi-polar (Luz/Sombra) convive elegantemente en la banda derecha junto al botón rojo de salida del sistema. Ambos actúan uniformados estéticamente.

## Impacto en producción
- **Riesgo**: Ninguno. Soporte modular puro. Todos los elementos que usen ya `rxn-ui.css` heredarán automáticamente tonalidades oscuras nativamente sin intervención manual extra a menos que se fuerce el switch.
- **Dependencias**: Se utiliza JavaScript Vanilla en línea muy contenido. No hay jQuery, React, ni librerías de estado externas.

## Validación esperada
1. Efectuar limpieza de caché al ingresar.
2. Por defecto el panel debe recibirnos vestidos de gris carbón/negro elegante. Letras legibles y contrastadas.
3. Colocar en la raíz del proyecto web una imagen nombrada `logo.png`. Refrescar y notar cómo subyace limpia junto al título en la esquina superior izquierda.
4. Clickeando el nuevo botón de luna (`bi-moon-stars`) mutará el DOM hacia el tema claro originario reasignando sol de icono (`bi-sun`). Perdurará refrescando por LocalStorage.
