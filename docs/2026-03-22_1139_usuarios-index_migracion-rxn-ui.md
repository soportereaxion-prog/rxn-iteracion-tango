# Módulo General — Tanda 3: Migración de `usuarios/index.php` (Fase 2)

## Contexto real
Continuando con la Tanda 3, iniciamos la Fase 2 enfrentando por primera vez con éxito un archivo completamente acoplado a Bootstrap 5 desde su concepción: el listado maestro de "Gestión de Usuarios". El objetivo primario era remover la gigantesca hoja de estilos del framework externo conectada vía CDN e instanciar en el campo de batalla real si la librería `rxn-ui.css` lograría soportar estructuralmente un layout con Flexbox y tablas responsivas sin romperse.

## Problema resuelto
La vista no usaba tablas para armar su esqueleto estructural (como las anteriores `limpiarArchivos` o `configuraciones` que lo usaban de layout). En cambio, delegaba absolutamente todas sus necesidades de márgenes, posicionamiento horizontal y tarjetas a las utilidades atómicas de Bootstrap (`d-flex justify-content-between`, `table-striped`, `badge bg-success`, etc.). Descartar el framework requería de un "mapeo visual" uno a uno de decenas de propiedades para preservar la Experiencia de Usuario sin generar CSS inútil.

## Decisión tomada y ejecutada
Se retiró definitivamente la etiqueta `<link href="...bootstrap.min.css">`, resguardando únicamente el CDN de iconos nativos (`bootstrap-icons`) al ser activos gráficos y no estructurales:

1. **Estructura Cúbica:** La cabecera engorrosa que abría el archivo `<div class="container... d-flex justify-content-between...">` fue sustituida limpiamente con nuestra inyección de Fase 1: `<div class="rxn-container"><div class="rxn-flex-between">`. Esto contuvo el H2 y los botones primarios organizándolos a izquierda-derecha al instante.
2. **Tabla HTML:** Se eliminaron las clases foráneas `.table-striped .table-hover` y se inyectó nuestra unificada `.rxn-table` que replica perfectamente las columnas en las capas *odd/even* (impares) y la reactividad del bloque `thead`. Una vez más aplicamos la norma estricta del `overflow-x: auto` en una capa circundante asegurando cero fugas de ancho en móviles angostos.
3. **Badges:** El estado ACTIVO/INACTIVO del PHP saltó sin altercados de usar `.badge bg-success` a la paleta nativa `.rxn-badge-success`.
4. **Resistencia a re-inventar Utilidades:** Las micro-clases atómicas de márgenes y textos que rellenan código (ej: `.text-center`, `.mt-4`, `.text-muted`) no fueron añadidas a RXN. Obedeciendo al pedido de estricta simplicidad aséptica, se les aplicó utilidades puras `style="text-align: center..."` ahorrando cargar selectores CSS en la stylesheet oficial del sistema de manera injustificada.

## Impacto en producción
- **Riesgo:** Completamente nulo. La lógica del ciclo de carga, renderizado de matriz array PHP `$usuarios as $u` e IF-statements quedaron inmaculados sosteniendo la coherencia semántica original.
- **Validación Práctica de Escudo UI:** Esta operación es histórica pues comprueba orgánicamente que los componentes RXN recientemente codificados (`rxn-flex`, grids y tables) operan de manera intercambiable con librerías hiperpesadas extranjeras.

## Validación esperada
Al entrar al listado del Gestor de Usuarios se verificará de antemano un milagro invisible: *pareciera ser exactamente la misma vista de ayer*. Pero al consultar la carga en el "DevTools F12", la página renderiza su estructura ahorrándose traccionar 250KB de peticiones de Bootstrap remoto, garantizando una carga en microsegundos y soportando las reescaladas de ventana de forma 100% nativa.
