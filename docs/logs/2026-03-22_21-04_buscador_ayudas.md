# Buscador Interno en Sistema de Ayudas

## Contexto
El sistema de ayudas había sido purgado exitosamente de dependencias externas (Bootstrap) y normalizado bajo el esquema `rxn-card` del `rxn-ui.css`. Tras esta simplificación estructural, era ideal proveer a los operadores una herramienta de navegación veloz inter-documento sin incrementar el acoplamiento ni requerir llamadas al motor PHP.

## Problema
Los manuales operativos (Pedidos, Facturas, Rechazos, Purgado) estaban aislados en el frame principal, obligando al usuario a leerlos íntegramente de principio a fin, lo que dificultaba localizar un aspecto técnico puntual en momentos de soporte o dudas operativas críticas.

## Decisión
Desarrollar un motor de búsqueda y filtrado 100% Client-Side empleando Javascript Vanilla:
- Sin frameworks externos o bases de datos.
- Indexación On-The-Fly recuperando silenciosamente el HTML con la API `fetch`.
- Renderizado de coincidencias inmediatas (filtrado in-memory) integradas a la interfaz lateral de `MenuPrincipal.html`.
- Respeto total al framework visual (colores y espaciados importados desde variables nativas CSS `var(--rxn-text-muted)`, etc).

## Archivos afectados
- `/Ayudas/MenuPrincipal.html`
- `/Ayudas/js/buscador.js` (NUEVO)

## Implementación
1. **Frontend en `MenuPrincipal`**: Se inyectó un `input[type=text]` en el encabezado de la sidebar y un contenedor de desbordamiento (overflow `max-height`) para recibir los resultados inyectables.
2. **Motor JS en `buscador.js`**: Este script asincrónico indexa un diccionario estático en la carga de la página (con el listado de archivos HTML existentes en el módulo). Remueve todas las etiquetas HTML transformándolas a DOM, recolecta títulos de encabezados `rxn-card-header` y unifica la masa textual en crudo.
3. **Escucha y Parseo**: Cada `keyup` superior a las tres letras confronta iterando la masa plana (`includes`), e inyecta la cabecera correspondiente en el listado visual lateral, con recortes limpios de texto (snippets).

## Impacto
- Búsqueda instantánea en todo el abanico de ayudas sin demora de red post-carga (solo durante el volcado inicial en memoria).
- Minimalismo arquitectónico preservado (2 KB en script nuevo).
- Mínimo overhead, nula dependencia externa. 

## Riesgos
- Si se agregan nuevos archivos de ayuda al ecosistema en el futuro, deberán anexarse manualmente al Array inicial codificado en duro (hardcoded) dentro del archivo `buscador.js`.
- Es ineficiente si el volumen HTML escala de 6 archivos a más de 100 documentos densos.

## Validación
- Probado sin conflictos de scope. Input de búsqueda estable y reactivo sin paradas de renderizado (janks) visibles durante the event listener injection.
- Redireccionamientos a `ayuda=` funcionales inyectando exitosamente las nuevas requests a través del fetch principal.

## Notas
Iteración 15 completada para RXN Lady API.
