# Módulo Visual — Relevamiento de Pantallas Candidatas a `rxn-ui`

## Lectura rápida
Tras auditar el código fuente del sistema, se identificaron 6 pantallas adicionales (excluyendo las ya unificadas del listado CSV). Existen vistas 100% dependientes de Bootstrap 5 (como el Menú Principal y `usuarios`), y vistas transaccionales ancladas en HTML estricto (`<fieldset>` y tablas como layout). La transición a `rxn-ui.css` es viable progresivamente sin alterar la lógica backend, priorizando las pantallas con menos componentes estructurales anidados.

## Inventario de pantallas
Se agrupan en las siguientes ubicaciones:
1. `csv/index_rechazar_pendientes.php` (Tipo: Proceso simple).
2. `limpiarArchivos/index.php` (Tipo: Proceso mixto y modal).
3. `configuraciones/index.php` (Tipo: Formulario en grilla).
4. `copiaFacturas/index.php` (Tipo: Formulario condicional dinámico).
5. `index.php` [Raíz] (Tipo: Menú de navegación).
6. `usuarios/index.php` y `usuarios/form.php` (Tipo: Formulario ABM / Tabla).

## Clasificación por complejidad
- **BAJA Complejidad:**
  - `csv/index_rechazar_pendientes.php`: Estructura elemental con un botón y `<fieldset>`. Refactorización casi idéntica a la pantalla piloto (envoltorios limpios).
- **MEDIA Complejidad:**
  - `limpiarArchivos/index.php`: Reemplazo de tablas que actúan de columnas de layout y limpieza de un Modal Javascript 100% nativo y rígido ("hardcodeado").
  - `configuraciones/index.php`: Contiene tablas HTML robustas (`border="1"`) que ordenan inputs. Hay que llevarlas cuidadosamente a contenedores responsive o reajustar como `.rxn-table`.
- **ALTA Complejidad:**
  - `copiaFacturas/index.php`: Embebe dependencias de Bootstrap 5 de manera focalizada y presenta inyecciones de código PHP en medio de etiquetas `<td>`. Requerirá aislar y limpiar prolijamente.
  - `index.php` (Home) y `usuarios/`: Creadas inherentemente sobre la grilla Flexbox de Bootstrap 5 (`row`, `col`, `col-md-3`). Su migración hacia `rxn-ui` obligaría a extender nuestra librería CSS propia con una lógica estructural (grid system basico) antes de intervenir.

## Riesgos puntuales
- **Tablas HTML usadas como Layout:** Pantallas como *Configuraciones* y *LimpiarArchivos* posicionan botones e inputs usando `<tr>` y `<td>`. Sustituir la tabla arbitrariamente desordenaría por completo la posición visual del formulario. Será necesario repensar con Flexbox usando un nuevo `.rxn-grid` controlado o mantener una tabla puramente estructural sin bordes (`.rxn-table-layout`).
- **Scripts Nativos Atados a CSS Hardcodeados:** En la carpeta de limpieza de archivos, la lógica del modal pop-up invoca identificadores y estilos que coexisten violentamente en la vista.
- **Inyección de Bootstrap Inconsistente:** Múltiples pantallas importan Bootstrap 5 puntualmente saltándose las directivas globales. Removerlo para obligarlas a usar `rxn-ui.css` puede revelar selectores rotos si no se reemplazan etiquetas con atención.

## Orden recomendado de intervención
La táctica ideal será por "desgaste de los márgenes hacia el core": intervenir inicialmente donde hay menos fricción y construir los componentes faltantes de la micro-librería rxn gradualmente.

1. **TANDA 1 - Victorias Rápidas:**
   - `csv/index_rechazar_pendientes.php` (Termina de unificar la carpeta CSV por completo).
2. **TANDA 2 - Limpieza de Layouts Rígidos:**
   - `limpiarArchivos/index.php` (Oportunidad para abstraer un modal estilo `.rxn-modal`).
   - `configuraciones/index.php` (Oportunidad para incorporar una grilla de inputs sin apelar a tablas obsoletas).
3. **TANDA 3 - Cirugía Profunda (Erradicación Final de Frameworks):**
   - `copiaFacturas/index.php`.
   - `index.php` (Pantalla de bienvenida y tarjetas de menú).
   - `usuarios/index.php` (y subcarpetas, migrándolos lejos de Bootstrap foráneo).
