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

$token = "330b3a3a-aa60-42d7-acbb-ff9676e523c1_14396";

$ch = curl_init('https://tiendas.axoft.com/api/Aperture/Product?pageSize=3000&pageNumber=1');


/*
IMPORTANTES LÍNEAS PARA DESACTIVAR EL USO DE CERTIFICADOS DEL SERVER
Desactivar el uso de certificados online en el server local*/
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
/*Desactivar el uso de certificados online en el server local*/


curl_setopt($ch, CURLOPT_POST, true);

curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch, CURLOPT_HTTPHEADER, array(
   'Content-Type: application/json',
   'Expect:',
   'Content-Length: 0',
   'Authorization: Bearer ' . $token,
   'accesstoken: 330b3a3a-aa60-42d7-acbb-ff9676e523c1_14396"'
   ));

curl_setopt($ch, CURLOPT_VERBOSE, true);

$verbose = fopen('log_curl.txt', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

$data = curl_exec($ch);

$info = curl_getinfo($ch, CURLINFO_HTTP_CODE);

$data2 = json_decode($data, true);

//El siguiente script da los valors de los Json definidos
//$data2 = var_dump(json_decode($data, true));
//var_dump(json_decode($data,false,512,JSON_BIGINT_AS_STRING));

print_r($data2);

curl_close($ch);




?>