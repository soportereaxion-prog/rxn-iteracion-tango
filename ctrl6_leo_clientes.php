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

$token = '
{
"ApiAuthorization": "c894f9cf-9078-4cce-b296-888b7439e390";
"Company: 283"
}
';
//setup the request, you can also use CURLOPT_URL
$ch = curl_init('http://svrrxn:17001/Api/Get?process=2117&pageSize=10&pageIndex=0&view=RXN_Clientes');
//Filtro por cliente
//$ch = curl_init('http://svrrxn:17001/Api/GetById?process=2117&id=009999');

// Returns the data/output as a string instead of raw data

$data_string = '{
"ApiAuthorization": "c894f9cf-9078-4cce-b296-888b7439e390";
"Company: 283"
}
';

curl_setopt($ch, CURLOPT_POST, true);

curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

//Set your auth headers
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'ApiAuthorization: c894f9cf-9078-4cce-b296-888b7439e390',
    'Company: 283',
   ));
     //'Content-Type: application/json',
    //'Authorization: Bearer ' . $token,
   //'accesstoken: 1363f43b-6e58-455e-975a-f09a7baf28d2_11031'

curl_setopt($ch, CURLOPT_VERBOSE, true);

$verbose = fopen('log_curl.txt', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

// get stringified data/output. See CURLOPT_RETURNTRANSFER
$data = curl_exec($ch);

// get info about the request
$info = curl_getinfo($ch, CURLINFO_HTTP_CODE);

$data2 = json_decode($data, true);

print_r($data2);

echo '<br>';
foreach ($data2['resultData']['list'] as $cliente){
    
        //        echo $cliente['RAZON_SOCI'].' - '.$cliente['CUIT'];
        echo $cliente['CUIT'].'<br>';
        //$data['resultData']['list'][0]['RAZON_SOCI']
    
}

// close curl resource to free up system resources
curl_close($ch);

?>