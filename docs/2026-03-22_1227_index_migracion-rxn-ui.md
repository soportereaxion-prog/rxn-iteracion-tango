# Index.php — Migración final a rxn-ui.css

## Contexto real
El menú raíz (`index.php`) contenía la estructura básica para las tarjetas de opciones, pero abusaba de estilos incrustados (`inline styles`) que simulaban el comportamiento de las clases de Bootstrap, ensuciando el código y dificultando la unificación con el resto de los módulos migrados en la Tanda 3.

## Problema detectado
Falta de clases de utilidad y componentes en `rxn-ui.css` que permitieran estructurar correctamente los textos, márgenes, botones bloque y composición de tarjetas del menú principal sin depender de largas sentencias en el atributo `style`.

## Decisión tomada
Se extendió `rxn-ui.css` de manera mínima y justificada, configurando el tag `body` base y agregando modificadores estandarizados del componente tarjeta (`.rxn-card-title`, `.rxn-card-text`, `.rxn-card-icon`, `.rxn-card-footer`), una variante de botón en bloque y utilidades menores de texto/espaciado (`.rxn-text-center`, `.rxn-mb-4`, etc). Luego, se limpió todo el código de `index.php`.

## Implementación propuesta
1. **rxn-ui.css**: Se introdujeron propiedades limpias orientadas a elementos y utilidades para no tener que simular layout grid y flex a mano en el HTML.
2. **index.php**: Se aplicó una limpieza a fondo de atributos `style`, sustituyéndolos por las nuevas clases de CSS puro, y manteniendo íntegros los enlaces, la semántica y los íconos Bootstrap y el botón particular rojo de Logout.

## Impacto en producción
- **Riesgo**: Muy bajo. La reestructuración de `rxn-ui.css` define `body` lo cual mejora el sistema en todos los módulos. Las tarjetas se definen como `flex flex-col` por defecto sin afectar las tablas empaquetadas en las vistas de ABM (ya que su child es `card-body`).
- **Dependencias**: Ya no existen atributos dependientes ni pseudo-clases manuales. `bootstrap-icons.css` permanece sin alteraciones. `copiaFacturas` no fue modificado.
- **Reversibilidad**: Alta y simple revirtiendo el tag HTML completo y el CSS.

## Validación esperada
Ingresar a `/index.php` y observar:
1. Las tarjetas de acceso directo deben visualizarse estiradas uniformemente en la grilla.
2. Los botones de acceso de cada módulo deben verse anchos a full (`100%`) acoplados al fondo de las tarjetas.
3. Se verificarán correctos márgenes, colores de las fuentes grises (`text-muted`) y centrado.

## Notas
Se cumple el objetivo de unificar visualmente el panel de control. El código ahora es conciso, fácil de mantener y se basa enteramente en utilidades de la librería propia del sistema `rxn-ui.css`.
