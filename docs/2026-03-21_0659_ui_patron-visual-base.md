# Gestión de Usuarios — Patrón Visual Base

## Contexto real
La vista de Gestión de Usuarios tiene un diseño limpio y moderno que difiere drásticamente de las pantallas transaccionales históricas (como el procesamiento CSV). Se requiere normalizar el sistema usando esta pantalla como plano base, sin afectar la robusta lógica legada en PHP.

## Problema detectado
Actualmente el sistema posee inconsistencias visuales: mientras el módulo de usuarios usa layouts modernos (`card`, márgenes limpios y grillas formales), módulos como el listado de CSV siguen anclados en HTML clásico con etiquetas `<fieldset>`, `inputs` sin estilos o estilos en línea y CSS obsoleto (`importa.css`).

## Decisión tomada
Oficializar los estilos de la sección "Gestión de Usuarios" como el patrón base estandarizado, replicando de forma no invasiva las clases aplicadas ahí en el resto de la plataforma. Como "Gestión de Usuarios" funciona mediante el framework front-end Bootstrap 5 (vía CDN), la base CSS a reciclar serán estrictamente las clases de este framework sin incorporar ninguna herramienta o librería extra.

## Implementación propuesta
Para aplicar la base en otras pantallas con mínima invasividad:

### Lectura rápida
El diseño "Gestión de Usuarios" se sostiene sobre utilidades clásicas de Bootstrap 5: fondo claro (`bg-light`), tarjetas de elevación suave (`card shadow-sm`) y un control ordenado de márgenes nativos (`mt-4`, `mb-3`). El pasaje a este estilo no interfiere con el modelo de formularios POST.

### Análisis visual
- **Estructura general:** Fondo gris claro, contenedor contenedor centrado (`container`).
- **Acciones y Títulos:** Títulos grandes (`h2`) acompañados de botoneras alineadas horizontalmente usando `d-flex` y `justify-content-between`.
- **Presentación:** Las pantallas se quitan de un fondo "pelado" y pasan al interior de una caja enmarcada blanca que agrupa semánticamente la información.
- **Formularios:** Alineación clara con separación superior-inferior.

### Componentes detectados
- **Títulos:** `h2` o `h5` dentro de headers de tarjeta (`card-header bg-dark text-white`).
- **Botones:** 
  - Principal (Submit/Guardar): `btn btn-success` o `btn btn-primary`.
  - Secundario (Volver/Cancelar): `btn btn-secondary` o `btn btn-outline-secondary`.
  - De grilla: `btn btn-sm btn-info text-white`.
- **Contenedores:** `card shadow-sm` compuesto de `card-body`.
- **Tablas:** `table table-hover table-striped align-middle` acompañadas de `thead class="table-dark"`.
- **Estados / Badges:** `badge bg-success` (verde) y `badge bg-danger` (rojo).

### Propuesta CSS base
No inventaremos CSS desde cero. Se debe replicar en el `<head>` de las pantallas viejas esta misma dependencia que "Usuarios" ya carga para poder reutilizar sus clases:
```html
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
```

### Impacto / Riesgos
- **Lógica e Integraciones:** Cero riesgo. Los `form` siguen utilizando POST sincrónico o iFrames. Ningún atributo funcional (`name`, `value`, `action`, `method`) es modificado.
- **Mínima Invasividad:** Es una readaptación estética mediante clases asignadas al DOM. 
- **Reversibilidad:** Inmediata. Si una vista se rompe, retroceder implica solamente deshacer el reemplazo de etiquetas (ej: volver a la etiqueta `<fieldset>`).

### Siguiente paso recomendado
Elegir la pantalla **`/csv/index.php` como primer "pantalla piloto"**. Cambiaríamos el `<fieldset>` y `<legend>` por un moderno conjunto de `<div class="card">` conservando íntegro el iFrame actual de la consola y sus flujos POST con Tango.
