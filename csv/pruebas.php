<?php

/*
--------------------------------------------------------------------------------------------
|                          Ch4rl1X Desarrollo de aplicaciones web y móviles                |
|                                                                                          |
|                                  correo: charly@charlesweb.com.ar                        |
|                                     web: www.charlesweb.com.ar                           |
|                                                                                          |
| Este material es apto para ser difundido y compartido. Utilizalo bajo tu responsabilidad.|
--------------------------------------------------------------------------------------------
*/
function redondearNumeroEspecial($numero) {
    // Extraer la parte entera y decimal del número
    $parteEntera = floor($numero);
    $parteDecimal = $numero - $parteEntera;

    // Redondear la parte decimal según las reglas específicas
    if ($parteDecimal <= 0.82) {
        $redondeadoDecimal = 0.80;
    } elseif ($parteDecimal <= 0.83) {
        $redondeadoDecimal = 0.85;
    } else {
        $redondeadoDecimal = 0.90;
    }

    // Devolver el número redondeado
    return $parteEntera + $redondeadoDecimal;
}

// Ejemplo de uso
$numeros = array(1039.82, 1039.83, 1039.86);

foreach ($numeros as $numero) {
    $resultado = redondearNumeroEspecial($numero);
    echo "El número $numero se redondea a: $resultado\n";
}

?>