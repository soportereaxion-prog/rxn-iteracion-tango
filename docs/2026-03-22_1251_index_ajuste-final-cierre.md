# Index.php — Ajustes finales de cierre

## Contexto real
A nivel visual, las pantallas y los cards estaban definidos en la versión anterior. Sin embargo aparecieron 3 problemas específicos en producción:
- Los botones de las tarjetas podían "flotar" o no alinear a la misma altura horizontal inferior si el texto de la tarjeta difería.
- El icono `bi-copy` presentaba problemas de compatibilidad o asimetría con la librería de Bootstrap Icons.
- El botón de cierre de sesión apuntaba a `Logout.php` (inexistente) en lugar de la verdadera ruta `auth/logout.php`.

## Problema detectado
1. En Chrome y otros navegadores o resoluciones específicas, `.rxn-card` carecía de `height: 100%` lo cual es necesario para permitir la propagación elástica total hasta `.rxn-card-footer` empujándolo a la base mediante la justificación `margin-top: auto`.
2. Error de ruta en atributo href de Cerrar Sesión.
3. El ícono `bi-copy` provocó problemas de visualización/simetría respecto al pack iconográfico del menú.

## Decisión tomada
Dar resolución quirúrgica:
1. Reemplazamos `bi-copy` por su versión universal y perfectamente soportada `bi-files`.
2. Cambiamos la ruta destino a `auth/logout.php` preservando el botón del encabezado sin tocar su lógica PHP.
3. Se adicionaron exclusivamente las 2 sentencias mínimas necesarias al componente `.rxn-card` en `rxn-ui.css`: `height: 100%` en la tarjeta padre y `margin-top: auto` en el footer hijo, forzando matemáticamente a que todos los botones de acción se apoyen contra la parte inferior. Ninguna utilidad global nueva fue creada.

## Implementación propuesta
Todo se hizo reutilizando los componentes sin alterar la composición general de Bootstrap ni la de las pantallas de ABM en otros lados del sistema (las flex properties aseguran compatibilidad hacia atrás).

## Impacto en producción
- **Riesgo**: Mínimo. Funcionalidad restablecida para cerrar sesión de manera segura.
- **Dependencias**: Aumentó la consistencia y la robustez contra distintos tamaños de pantalla minimizando el comportamiento float y arreglando alineaciones.
- **Reversibilidad**: Simplemente restaurar los archivos CSS e index alterados en el commit actual.

## Validación esperada
Refrescar `/` y verificar:
1. Todos los botones azules y grises quedan pegados y perfectamente emparejados en el fondo al mismo nivel de toda la fila.
2. Hacer click en 'Cerrar sesión' destruye la sesión y finaliza la operatoria.
3. El ícono de 'Copia Facturador' muestra dos hojas correctamente trazadas.
