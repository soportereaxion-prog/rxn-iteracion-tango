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

function redondearHaciaAbajoDosDecimales($numero) {
    return floor($numero * 100) / 100;
}

// Ejemplo de uso
$numero = 50277.3232;
$resultado = redondearHaciaAbajoDosDecimales($numero);
echo "El resultado de redondear hacia abajo $numero con 2 decimales es: $resultado"; // Salida: El resultado de redondear hacia abajo 10.736 con 2 decimales es: 10.73


?>