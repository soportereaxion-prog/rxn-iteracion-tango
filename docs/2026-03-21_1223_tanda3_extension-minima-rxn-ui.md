# Módulo General — Tanda 3: Extensión Mínima de `rxn-ui`

## Lectura rápida
Con el dictamen autoritativo y firme del gran Patrón Orquestador, procedimos a sentar las pre-condiciones productivas para la Fase 1 de la Tanda 3. El objetivo fundamental consistió en vacunar y fortalecer nuestra modesta biblioteca `rxn-ui.css` con los escudos necesarios previendo que el sistema no colapse visualmente al extirpar Bootstrap de las pantallas de alta dependencia. Se inyectaron selectivamente 4 abstracciones tácticas en la hoja de estilos: Alertas, un *Grid* moderno de despliegue fluido, envoltorios *Flex* espaciadores y una recreación ligera del popular Switch interactivo. 

## Componentes nuevos propuestos para `rxn-ui.css`

1. **.rxn-alert** (y sus variantes `success`, `warning`, `danger`, `info`).
2. **.rxn-grid**
3. **.rxn-flex-between** y **.rxn-flex-center**
4. **.rxn-switch** (Compuesto por un input base tipo checkbox fuertemente alterado visualmente con el truco `appearance: none`).

## Justificación de cada agregado

- **Alertas (`.rxn-alert`):** Absolutamente imperativas y críticas porque en la actual pantalla `copiaFacturas`, la lógica del core back-end (`modelo.php`) lanza ecos directos en pantalla devolviendo errores en crudo que estaban enlazados directamente a un string `<div class="alert alert-warning">` propio de Bootstrap. Sin inyectar el simulador, el feedback visual al usuario en la operación de datos pasará a ser una triste línea de texto rota sin fondo contenedor.
- **Grillas Autónomas (`.rxn-grid`):** Indispensable para el menú raíz (`index.php`), que apila sus masivos botones visuales apoyándose a los bastonazos en la cuadrícula robusta de columnas `col-md-3` de Bootstrap. En vez de rearmar una arquitectura de 12 columnas pesada, se implementó una regla genérica inteligente con CSS puro `grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));` que garantiza flujo ordenado que corta automáticamente al achicar la pantalla.
- **Micro-alineamiento (`.rxn-flex-between`):** Obligatorio. Es la piedra filosofal para evitar llenar de `<br>` todas las vistas. Pantallas como *Gestión de usuarios* utilizan este espaciador de Flexbox de Bootstrap (`d-flex justify-content-between`) para empujar el título a un lado de la cabecera y el botón de crear a la derecha. Recrearlo condensa el código a unas hermosas líneas reutilizables.
- **Switches de ABM (`.rxn-switch`):** Vital exclusivamente para el esqueleto de `usuarios/form.php` que maneja el estado ACTIVO/INACTIVO del registro como un vistoso y pulcro deslizador ovalado (`form-switch`). Volver al rudimentario `<input type="checkbox">` setentoso impactaría trágicamente la UX moderna ya asimilada en el programa.

## Riesgos de sobre-extensión a evitar
Se evitó soberanamente y de forma tajante importar grillas numéricas de tamaño variable o 12 columnas exactas (ejemplo típicos de librerias: `.col-4`, `.w-50`, `.mt-2`), lo cual contaminaría un CSS que debía subsistir diminuto desnaturalizando su propósito único. Igualmente se bloquearon las utilidades triviales de espaciado o tipografía que deben ser resueltas ocasionalmente en HTML directo `style="margin..."`, para no convertir nuestro limpio `rxn-ui` en otro abrumador mini-Bootstrap inmanejable.

## Código CSS inyectado provisoriamente
Se agregó entre las propiedades estándar y las variables responsivas del archivo `.css`:
```css
/* Rxn-ui.css / Subsección Fase 3 Utilidades  */
.rxn-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
.rxn-flex-between { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }

.rxn-switch-checkbox { appearance: none; width: 40px; height: 20px; background: #ccc; border-radius: 20px; position: relative; cursor: pointer; transition: background 0.3s; margin: 0; }
.rxn-switch-checkbox:checked { background: #0d6efd; }
.rxn-switch-checkbox::after { content: ''; position: absolute; top: 2px; left: 3px; width: 16px; height: 16px; background: #fff; border-radius: 50%; transition: transform 0.3s; }
.rxn-switch-checkbox:checked::after { transform: translateX(18px); }
/* ... (Además de rxn-alerts informativos y exitosos) ... */
```
