# Módulo CSV — Ajuste Responsive Pantalla Piloto

## Contexto real
La vista `csv/index.php` había sido portada recientemente a la nueva estructura visual propia y minimalista del sistema (`rxn-ui.css`), pero conservaba ciertos atributos `inline` en los formularios y comportamientos rígidos (ancho duro de 200px en inputs) que dificultaban su operabilidad o legibilidad en resoluciones móviles y pantallas reducidas. 

## Problema detectado
- Inputs superpuestos o cortados en ventanas muy chicas.
- El listado de archivos post-proceso y algunos botones carecían de adaptabilidad fluida (no colapsaban de forma amigable a pantallas pequeñas).
- No había una caja envolvente que permitiera aprovechar reglas de Flexbox para apilar interacciones automáticamente de izquierda a derecha.

## Decisión tomada
Refinar la base `rxn-ui.css` con extrema precisión, inyectando un componente visual simple (`.rxn-action-bar`) y un bloque minimalista de `@media queries`, enfocados en apilar horizontalmente (`flex-direction: column;`) los botones y controles cuando la ventana cae por debajo del umbral estándar móvil (480px). Adicionalmente, se otorgó resiliencia lateral a las tablas futuras mediante `overflow-x: auto`. Todo regido bajo la regla de contención dentro del prefijo `rxn-`.

## Implementación propuesta y ejecutada
1. Rediseño estructural en el CSS agregando el contenedor utilitario `.rxn-action-bar` que emplea `display: flex; gap: 10px;`.
2. Supresión del container estático previo `div style="margin-bottom:15px;"` por el nuevo `.rxn-action-bar` dentro de `index.php`.
3. Inserción de `@media (max-width: 768px)` y `@media (max-width: 480px)` en el CSS oficial que colapsan fluidamente la tarjeta y obligan a que inputs y botones asuman `width: 100%` en dispositivos chicos.

## Impacto en producción
- **Riesgo:** Inexistente. Son aditamentos de cascada visual reaccionando exclusivamente a las proporciones de pantalla del cliente (Media Queries). El modelo y las llamadas de red permanecen idénticos.
- **Dependencias:** Continuamos 100% aislados a recursos de terceros y frameworks macroscópicos.
- **Reversibilidad:** Alta e instantánea; simplemente eliminando el bloque genérico de `@media` desde el final del archivo CSS todo vuelve al tamaño desktop perpetuo.

## Validación esperada
Poniendo a prueba la interfaz: achicar asimétricamente la venta del navegador web. El campo de fecha y los dos botones de interacción (el del calendario y procesar) deben encogerse progresivamente, y al golpear la resolución pequeña, deberán apilarse uno por el otro logrando un aspecto vertical 100% cómodo sin romper la caja blanca contenedora.
