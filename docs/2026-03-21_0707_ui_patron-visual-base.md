# Gestión de Usuarios — Patrón Visual Base (Iteración Custom CSS)

## Lectura rápida
La pantalla "Gestión de Usuarios" funciona visualmente gracias a los componentes de Bootstrap 5. Sin embargo, para cumplir estricamente la restricción de **cero dependencias externas**, NO se incluirá dicho framework. En su lugar, se extraerá su ADN visual (colores, sombras, espaciados) para construir una micro-librería CSS propia (prefijo `rxn-`) que imite este look and feel de forma encapsulada y no invasiva.

## Corrección de enfoque
Se descarta totalmente la adopción de frameworks globales y librerías de terceros (Bootstrap / Tailwind, etc.). En su lugar, se procederá a **diseñar una capa visual CSS propia, liviana y controlada**. Para aislar firmemente estos nuevos estilos del entorno frágil legacy, cada componente utilizará una nomenclatura específica (ej: `rxn-`) y **se evitará rotundamente aplicar selectores globales directos** a elementos nativos HTML (como `table`, `input` o `button`), previniendo cascadas accidentales en partes del sistema no migradas.

## Nueva propuesta CSS base (propia)
Se redactará un archivo minificado estructural (ej. `rxn-ui.css`) que contendrá únicamente las clases necesarias abstraídas del diseño de usuarios. 

La lista de componentes clave:
- **Estructura:**
  - `.rxn-container`: Contenedor responsivo centrado con márgenes.
- **Tarjetas (Cards):**
  - `.rxn-card`: Blanco absoluto, bordes redondeados y sombra suave para simular elevación profunda.
  - `.rxn-card-header`: Etiqueta oscura divisoria.
  - `.rxn-card-body`: Contenedor general de absorción de relleno (padding natural).
- **Tablas:**
  - `.rxn-table`: Aislamiento controlado para listas rayadas (`striped`) y color alterando el foco del mouse (`hover`).
- **Botones y Acciones:**
  - `.rxn-btn`: Estructura general de padding y transiciones planas.
  - `.rxn-btn-primary`: Acción transaccional/principal.
  - `.rxn-btn-secondary`: Flujos de cancelación.
- **Formularios:**
  - `.rxn-form-control`: Reempaqueta un típico input de texto/dropdown limpio.

## Estrategia de adopción
1. **Definición Aislada:** Crear el archivo único `rxn-ui.css`.
2. **Aplicación Progresiva Reversible:** Ligar y referenciar este CSS en las pantallas elegidas (por ej. `csv/index.php`).
3. **Reemplazo Encapsulado:** Intercambiar el código absoleto (`<fieldset>`, `<legend>`) aplicándole el `<div>` contenedor de la clase propia elegida.
4. **Respeta su entorno:** Cualquier elemento interno que _no posea_ el prefijo no es agredido estilísticamente por este archivo. 

## Impacto / riesgos reales
- **Riesgo visual (Minimizado a 0):** Al trabajar rigurosamente con clases prefijadas e ignorar element labels genericos como selectores CSS, el riesgo a desarmar flujos no involucrados desaparece.
- **Riesgo funcional (Ninguno):** Tocar atributos `class` no irrumpe validaciones POST o atributos `id` o `name` requeridos por la lógica PHP de la app.
- **Ventaja en Perfomance:** Cargar solo las 12-15 clases requeridas en lugar de 200kb de un CSS Framework mejora la respuesta veloz del sistema.
