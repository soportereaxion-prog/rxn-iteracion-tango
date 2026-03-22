# Módulo General — Tanda 3: Análisis y Estrategia Visual

## Lectura rápida
La "Tanda 3" compromete el núcleo vital estético del sistema. A diferencia del Módulo CSV que usaba HTML "crudo" (fácil de revestir), las vistas de Menú Raíz, Copia de Facturas y Usuarios **nacieron acopladas** a Bootstrap 5 (vía CDN o empaquetado local). Extirpar el framework abruptamente fracturará la grilla (grid), los formularios y las alertas del sistema. Se requiere primero blindar `rxn-ui.css` con equivalentes minimalistas antes de inyectarlo.

---

## Análisis por pantalla

### 1. `index.php` (Menú Raíz)
- **Dependencias:** CDN de Bootstrap 5 y Bootstrap Icons.
- **Acoplamiento:** Total. Funciona al 100% sostenido por el sistema de grillas (`row`, `col-md-3`, `g-4`) y clases utilitarias de flexbox de Bootstrap.
- **Componentes críticos:** Tarjetas de navegación que se apilan dinámicamente según la pantalla.

### 2. `usuarios/index.php` y `usuarios/form.php`
- **Dependencias:** CDN de Bootstrap 5.
- **Acoplamiento:** Muy Alto. Usan clases de alineación como `d-flex justify-content-between`, tablas `table-striped`, badges de colores nativas, e inputs con `form-control` y `form-switch` (interruptores).
- **Componentes críticos:** El formulario responsivo ABM, los switches de activación y la tabla de listado.

### 3. `copiaFacturas/index.php`
- **Dependencias:** Bootstrap 5 (local en `/assets`), jQuery 3.6, Select2 y Flatpickr.
- **Acoplamiento:** Crítico, caótico e inconsistente. Mezcla tablas estructurales `<table border="1">` tipo legacy con clases de alertas modernas (`<div class="alert alert-warning">`) inyectadas dinámicamente desde el código PHP puro.
- **Componentes críticos:** Las alertas condicionales y los `<select>` enriquecidos por el plugin Select2 (que a veces heredan el CSS de Bootstrap).

---

## Riesgos identificados

1. **Ruptura de Cajas (Grid Collapse):** Si quitamos Bootstrap sin tener en `rxn-ui.css` un equivalente a `col-md-3` o manejo de Flexbox para filas/columnas, el Menú Raíz pasará a mostrar tarjetas gigantes o amontonadas sin sentido estético.
2. **Interruptores Visuales:** Formatos elaborados como el `form-switch` (botón deslizable de activar/desactivar usuario) perderán su forma volviendo a ser un mero *checkbox* cuadrado antiguo.
3. **Alertas Fantasma:** Al desconectar Bootstrap en `copiaFacturas`, los mensajes de éxito/error que hoy devuelve en el HTML el código backend (`$modelo`) perderán fondo, padding y colores, pareciendo texto roto suelto en la página.

---

## Estrategia propuesta por fases

Para proceder con la erradicación global manteniendo "mínima invasividad estructural", la regla es: **Preparar la vacuna antes de remover el virus.**

- **Fase 1: Enriquecer `rxn-ui.css` (Capa de utilería básica).**
  No tocaremos ningún PHP. Ampliaremos nuestro CSS para incluir un sistema de Grid simple (ej: `.rxn-row`, `.rxn-col`), clases para Alertas (`.rxn-alert`, `.rxn-alert-danger`) y una envoltura flex de alineación.

- **Fase 2: Asalto al Módulo Usuarios.**
  Sustituiremos la CDN de Bootstrap vinculando `rxn-ui.css`. Migraremos la tabla a `.rxn-table` y los forms a `.rxn-card` e `.rxn-input`. Es un ABM aislado ideal para poner a prueba nuestro CSS enriquecido.

- **Fase 3: Asalto al Menú Raíz.**
  Al tener validado nuestro Grid propio, desconectaremos Bootstrap de la portada `index.php` y orquestaremos el menú armando la disposición de tarjetas con flex.

- **Fase 4: Cirugía de Alta Tensión en `copiaFacturas`.**
  Reescribiremos los bloque de "echo" adentro de PHP para que pasen de escupir `<div class="alert...` a escupir `<div class="rxn-alert...`. Se encapsulará la tabla híbrida legacy en `.rxn-table` del mismo modo que se hizo en la Tanda 2 (*Configuraciones*) conviviendo pacíficamente con el plugin jQuery Select2.

---

## Recomendación de siguiente paso concreto

⛔ **NO ENTRAR a modificar el HTML/PHP** hoy.
✅ **Siguiente paso lógico:** Iniciar la Fase 1 autorizándome para extender nuestro actual archivo `rxn-ui.css` sumándole las utilidades exclusivas faltantes (Alertas, layout Grid rudimentario y wrappers). Una vez fondeadas las herramientas en la librería, el reemplazo en el código base será coser y cantar.
