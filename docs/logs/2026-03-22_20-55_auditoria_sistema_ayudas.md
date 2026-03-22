# Auditoría y Refactorización del Sistema de Ayudas

## Contexto
El subsistema de ayudas del sistema (almacenado en `/Ayudas`) presentaba dependencias severas de Bootstrap 5, incluía un diseño visual sobrecargado con imágenes comerciales pesadas, footers irrelevantes para la operación de la API y textos ambiguos de corte marquetinero. Esto rompía la homogeneidad visual establecida con `rxn-ui.css` y engrosaba el peso y mantenimiento de la capa de frontend.

## Problema
- **Dependencia Externa**: Fuerte acoplamiento con el CDN de Bootstrap.
- **Inconsistencia Visual**: Mezcla de estilos del framework externo con la identidad nativa de la aplicación.
- **Contenido Inflado**: Documentación extensa, con lenguaje comercial en un entorno técnico/operativo.
- **Rendimiento**: Carga dinámica mediante JS de archivos con estructuras HTML completas (incluido `<html>`, `<head>`, `<body>`) destinadas a ser inyectadas en un `div`, generando HTML inválido o redundante, así como peticiones de imágenes pesadas.

## Decisión
- **Extracción de Frameworks:** Eliminar toda referencia a Bootstrap de los archivos en `/Ayudas`. 
- **Refactor Estructural:** Reescribir `MenuPrincipal.html` para usar Flexbox nativo, emulando la sidebar requerida sin CSS de terceros.
- **Normalización de Estilos:** Aplicar rigurosamente los componentes base de `rxn-ui.css` (`rxn-card`, `rxn-card-header`, `rxn-card-body`, etc.) en todos los documentos de ayuda.
- **Síntesis Funcional:** Redactar de cero los contenidos de todos los módulos (`ProcesarDatoss.html`, `Reprocesar rechazados.html`, `LimpiarArchivos.html`, `RechazarPendientes.html`, `CopiarFacturas.html`, `ConfiguracionDeDirectorio.html`), privilegiando lenguaje preciso, alertas operativas y advertencias técnicas de impacto directo en el flujo de caja/Tango.

## Archivos afectados
- `/Ayudas/MenuPrincipal.html`
- `/Ayudas/ProcesarDatoss.html`
- `/Ayudas/Reprocesar rechazados.html`
- `/Ayudas/LimpiarArchivos.html`
- `/Ayudas/RechazarPendientes.html`
- `/Ayudas/CopiarFacturas.html`
- `/Ayudas/ConfiguracionDeDirectorio.html`

## Implementación
1. Se reestructuró `MenuPrincipal.html` como contenedor maestro, inyectando estilos locales para sostener una sidebar responsiva.
2. Cada archivo de ayuda individual fue vaciado de su estructura monolítica y se condensó en un contenedor `rxn-card` purificado.
3. Se reemplazaron todas las alertas Bootstrap (`alert-success`, `alert-warning`, `alert-danger`) por variaciones cromáticas de clase nativas sobre cajas delimitadas con bordes de flexión.
4. Se retiró de forma permanente el "Footer" comercial.

## Impacto
- Interfaz del visor general de Ayuda unida cromáticamente a toda la suite RXN.
- Tiempos de renderizado reducidos al eliminar llamadas al CDN de Bootstrap y de imágenes ilustrativas superfluas.
- Eliminación de HTML mal formado dentro de la inyección por `fetch()` del MenuPrincipal.
- Comprensión del proceso mejorada en el equipo operativo.

## Riesgos
- Al remover screenshots descriptivos de la operatoria, se exige al usuario estar familiarizado visualmente con las verdaderas interfaces.

## Validación
- Se verificó que la función `fetch()` embebida en `MenuPrincipal` continúe resolviendo apropiadamente cada uno de los archivos internos, y que su contenido `<body>` sea renderizado dentro del DOM sin sobreescribir configuraciones globales de CSS.

## Notas
Iteración 14, auditoría completada satisfactoriamente. Todo registro obedece a la política estricta de "cero externalidades".
