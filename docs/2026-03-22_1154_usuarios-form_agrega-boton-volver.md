# Módulo General — Tanda 3: Ajuste de Usabilidad en ABM (`usuarios/form.php`)

## Lectura rápida
Se dictaminó aplicar un ajuste de *Quality of Life* (QoL) sobre el formulario de alta/edición recientemente asimilado a RXN. La directiva exigía asegurar un retorno explícito y amigable hacia el listado de usuarios de manera visible. Si bien el botón secundario previo etiquetado bajo el genérico vocablo de "Cancelar" ya cumplía fielmente el rol de regresar al listado (enrutado a `href="index.php"`), la jefa decretó que este requería una modificación en pro de clarificar intuitivamente el salto y relajar la interfaz web.

## Ajuste realizado
1. **Renombramiento Semántico y Ergonómico:** El ancla inferior asimilada en forma de `<a class="rxn-btn rxn-btn-secondary">` mutó su brusca narrativa inicial hacia un amigable y descriptivo "Volver al listado", respetando escrupulosamente los selectores visuales asignados.
2. **Respiro Visual Divisorio:** Se inyectó un espartano `margin-top: 15px;` in-line al contenedor `.rxn-flex-between` de la botonera en la base de la tarjeta (`.rxn-card`). Esto genera un aura de respiración vital respecto al *control deslizador (Switch)* superior del estado activo/inactivo, despegando intencionalmente la zona de ejecución (acciones de retorno seguro o guardado masivo) de aquella área reservada únicamente a tabulación de datos dinámicos.

## Impacto / Riesgo
- **Ausencia Tota de Riesgo Operativo:** La reestructuración estética recae 100% en las anclas de front, sin afectar las propiedades `name`, `POST` ni alterar triggers del `<form action="save.php">`. El botón para consolidar información de guardado a base (`Guardar DB`) persiste estoico sin interrupciones formales.
- **Sin Inflación CSS:** El cambio transcurrió acatando el mandato de mínima invasividad; evitamos inmensamente recurrir a inventar atajos inermes en el *rxn-ui.css* (*margin-bottoms, helpers u orientaciones huérfanas*).

## Validación esperada
Al ingresar libremente hacia "Nuevo Usuario" desde Menú Principal, el bloque control no se percibe apelotonado. Una clara franja divisoria invisible separa ahora visual y cognitivamente el llenado del perfil contra el accionar de su subida. La lectura fluye desde los inputs hacia la base, donde un afable botón gris grafito propone explícitamente "Volver al listado", aclarando las dudas del visitante asiduo al permitirle abandonar un registro confuso u ocio con plena garantía de enrutamiento de regreso, sin presionar por error el gatillo general azúl de base de datos.
