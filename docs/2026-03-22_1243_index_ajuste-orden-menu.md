# Index.php — Ajuste orden menú y logout

## Contexto real
El menú principal mostraba primero la Configuración y dejaba mezclado como una tarjeta más el botón de Cerrar Sesión. Esto rompía con el flujo operativo deseado, donde Procesar CSV y Copia Facturador son las tareas cotidianas más urgentes, y la acción global de salida debe estar siempre separada del menú de módulos.

## Problema detectado
Falta de jerarquía en la disposición de los elementos. La opción de salir estorbaba visualmente dentro del grid asumiendo una falsa posición de "aplicación" y la configuración ocupaba la primera posición que debía ser reservada para las operativas de negocio.

## Decisión tomada
1. Se movió el botón "Cerrar sesión" al encabezado superior derecho utilizando la clase nativa `.rxn-flex-between`, dándole un aspecto real de acción global estandarizada.
2. Se reordenaron las tarjetas operativas en `index.php` asignando el siguiente orden estricto, priorizando flujos de trabajo sobre administración:
   - Procesar CSV
   - Copia Facturador
   - Gestión de Usuarios
   - Configuración
   - Limpieza de Archivos

## Implementación propuesta
Todo el reordenamiento se hizo manipulando el DOM de `index.php` puro, moviendo contenedores y sin tocar en lo absoluto la hoja de estilos (`rxn-ui.css`). Se aprovecharon clases ya existentes en la cabecera, preservando intocablemente los enlaces (`Logout.php`) y toda la base funcional. Se retuvo el estilo rojo específico del botón pero estilizándolo para que aplique padding nativo preservando `rxn-btn` limpiamente.

## Impacto en producción
- **Riesgo**: Ninguno. Operación puramente visual sobre estructura HTML básica.
- **Dependencias**: Se usan las mismas clases nativas de `rxn-ui.css` que proveen `.rxn-flex-between`.
- **Reversibilidad**: Simplemente deshacer el cambio de orden de nodos en HTML.

## Validación esperada
Refrescar el menú de inicio (`/`) y chequear:
1. El encabezado superior ahora muestra título alineado a la izquierda y el botón 'Cerrar sesión' demarcado en rojo a la derecha inmediata.
2. Inicios de grid de tarjetas: El primer módulo en vista superior izquierda es "Procesar CSV", seguido de "Copia Facturador".
3. "Limpieza de Archivos" quedó desplazado elegantemente a la última posición operativa.
