# Módulo General — Unificación Visual (Limpieza de Archivos)

## Contexto real
Iniciando la "Tanda 2" de nuestro plan de migración visual post-CSV, se atacó en primer lugar la pantalla de `limpiarArchivos/index.php`. Esta vista particular presentaba el desafío crudo de utilizar estructuralmente una tabla de `1000px` de ancho exclusivo para ubicar (centradamente) dos botones de acción vitales. Adicionalmente, el flujo implementaba un *Modal (Pop-Up)* nativo de seguridad, fabricado manualmente con Javascript básico y CSS rígido ("hardcodeado") en cabecera.

## Problema resuelto
El layout proveía una usabilidad deficiente donde los botones de borrado masivo jamás colapsaban ni se adaptaban al achicamiento físico de la ventana de navegador (rompiendo layouts o provocando barra de desliable horizontal). Más allá de la torpeza técnica, la estética general chocaba contundentemente con el estándar inmaculado adoptado ya en subsecciones contiguas. El cartel negro del modal se veía sumamente amateur en comparación al resto del sistema *Reaxion*.

## Decisión tomada y ejecutada
1. Exterminar la etiqueta de tabla `<table width="1000">` usada inapropiadamente como matriz organizativa.
2. Subsumir todo el interior funcional de lógica bajo la jerarquía estricta `.rxn-container` y `.rxn-card` del sistema.
3. Organizar dinámicamente los dos botones principales del form bajo nuestro Flexbox con clase predefinida `.rxn-action-bar`, revistiendo y coloreando los mismos de `.rxn-btn-primary` y `.rxn-btn-danger` (dado el riesgo del proceso).
4. Emprolijar discretamente en la porción de `<style>` CSS nativo la sintaxis correspondiente a `#modalVentana`, imbuyéndola de un radio de borde pulido y sombra sutil idéntica a `.rxn-card` para engañar fluidamente al ojo (el modal nativo ahora parece pariente de nuestro framework). Sus botones de confirmación interiores fueron atados a `.rxn-btn`.

## Impacto en producción
- **Riesgo Operacional:** Inexistente por completo. Las vitales funciones interactivas `mostrarModal()` de origen natural y `enviarFormulario()` sobrevivieron intocables, respetando la estructura de variables POST. La sintaxis del modal JS oculto jamás se corrompió.
- **Estandarización:** La vista de limpieza reacciona fluidamente sin "hacer aguijada" a los costados de la caja base. Al achicar la pantalla, los botones de eliminación colapsan uno por debajo del otro de excelente manera.

## Validación de éxito comprobable
Visitando la ruta "Limpiar archivos pendientes" desde el menú raíz, la pesada caja anacrónica extinguió su linaje siendo sustituida por nuestra tarjeta blanca limpia moderna. Al pulsar los triggers, el oscurecimiento del entorno revela un modal JS elegante, provisto con un imponente botón que implora "Sí, borrar" resplandeciendo en rojo carmesí de peligro, anulando ambigüedades respecto a la envergadura del acto de cliquear.
