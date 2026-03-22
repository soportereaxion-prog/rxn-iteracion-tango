# Módulo General — Tanda 3: Migración de Menú Raíz (`index.php`)

## Lectura rápida
Avocados firmemente en la recta final de la extirpación (Fase 3), nos hemos adentrado en la gran arteria del sistema portuario: el `index.php` (Menú Raíz). Previo a esta iteración, el menú index era dependiente absoluto de Bootstrap 5; su despliegue en tarjetas visuales recaía puramente en su orquestación de la grilla foránea clásica de 12-columnas (`row col-md-3 c-4`). Valiéndonos de las robustas pautas inyectadas previamente en nuestro `rxn-ui.css` (puntualmente la herramienta autogestionable `.rxn-grid`), aniquilamos exitosamente la inyección CDNs externas, resguardando la belleza y la simetría original de las "Cards". Y, como marcaba el protocolo de contención, la frágil ruta colindante `copiaFacturas` permaneció indemne y protegida.

## Ajustes realizados
1. **Erradicación del Framework:** Eliminados categóricamente los anclajes a `<link href="...bootstrap.min.css">` de la cabecera y el ya mortuorio `<script src="...bootstrap.bundle.min.js">` marginado al pie del documento.
2. **Re-arquitectura del Grid:** Los pesados y redundantes andamiajes anidados (`<div class="row row-cols-1 row-cols-md-3 g-4">` y las iteraciones solitarias subsecuentes `<div class="col">`) fueron demolidos transversalmente. En su reemplazo directo se alzó el prodigioso contenedor maestro `<div class="rxn-grid">`, que merced de la nueva inteligencia CSS dictada en la Fase 1 acomoda y retrae el desborde en las cards dinámicamente según la resolución óptica del usuario.
3. **Mapeo de Tarjetas:** Las clases atómicas nativas de Bootstrap (`card shadow-sm h-100`) saltaron semánticamente al control oficial `.rxn-card`. Para emparentar la vital uniformidad en altura entre los bloques (originalmente provisto por `h-100`), recubrimos internamente a las rxn-cards con el esqueleto flex `display: flex; flex-direction: column; justify-content: space-between;`.
4. **Respeto Icónico y Botoneras:** Cuidando con celo el acervo estético de la iconografía abstracta principal, la dependencia superficial orientada a `bootstrap-icons.css` permanece viva. Los botones rectangulares anchos de base mutaron desde `.btn-outline` hacia nuestro formato corporativo RXN propulsados a ocupar el margen íntegro (`width: 100%`) mediante el truco de uso común `display:block; padding: 10px;`.

## Dependencias que permanecen (Controladas)
*   **Bootstrap-Icons:** Vital para el aspecto minimalista y enteramente inofensIVO (dado que se constriñe estrictamente a volcar glifos o librar fuentes tipográficas).
*   **Módulo `copiaFacturas/index.php`:** Totalmente intocable. A la espera de autorizarse la Cirugía de Alta Tensión dispuesta para la eventual Fase 4.

## Impacto / Riesgo
- **Falta Total de Riesgo Operativo:** Al encontrarnos transitando ante un Index o Hub maestro libre enteramente de lógica backend (Carencia de `GET/POST` para guardado de variables); las mutaciones estéticas están incapacitadas para mermar o anular datos reales. Los anclajes `href` y el enrutamiento a `Logout` se mantienen blindados.
- **Micro-optimización:** Al expurgar severo *overhead* HTML y evitar interpelar al DNS con múltiples requests hacia Bootstrap-CDN se reduce radicalmente el umbral de carga del hub principal en los dispositivos de campo instalados.

## Validación esperada
Un refresh ordinario a la URL raíz de la plataforma desplegará a las puertas de la vista un panal de 6 tarjetas finas y elegantes, espaciadas en perfecta mimetización respecto a su extinto homólogo anterior, y con la innegable cualidad de no sufrir desnivelaciones groseras en las alturas relativas de sus contenedores si los párrafos del menú descriptivo alterasen sus lineas de texto futuro. Emitiendo un testeo por consola sobre perfiles de tamaño Smartphone (IPhone 12 / Samsung) la cuadrilla RXN se quiebra impecablemente para generar columnas apiladas sin que exista directiva explícita de un `@media` controlándola. Formato fluido garantizado.
