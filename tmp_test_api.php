<?php
require __DIR__ . '/csv/modelo.php';

$csv = new modelo();
$csv->devuelvoTokens();

$cod_cliente_test = '1001'; // Default test customer
$consulta = $csv->db_sql->query("SELECT TOP 1 COD_CLIENT, TELEFONO_1, ID_GVA14 FROM GVA14");
$fila = $consulta->fetch(PDO::FETCH_ASSOC);

if (!$fila) {
    die("No test clients found.\n");
}

$cod_tango = $fila['COD_CLIENT'];
$telefono = $fila['TELEFONO_1'];
$id_gva14 = $fila['ID_GVA14'];

echo "Testing client COD_CLIENT: $cod_tango (ID_GVA14: $id_gva14, TELEFONO_1: $telefono)\n";

$payload = [
    "COD_CLIENT" => $cod_tango,
    "ID_CATEGORIA_IVA" => 10,
    "ID_GVA41_NO_CAT" => 1,
    "ID_ALI_FIJ_IB" => 31 // Testing specific valid values
];
$data_string = json_encode($payload, JSON_UNESCAPED_UNICODE);

echo "Payload: $data_string\n";

$url = $csv->token['RUTA_LOCAL'] . '/Api/Update?process=2117';
echo "Test 1: PUT to $url\n";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'ApiAuthorization: ' . $csv->token_api_local,
    'Company: ' . $csv->id_empresa,
    'Content-Type: application/json'
]);
$data = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpcode\nResponse 1:\n";
print_r(json_decode($data, true) ?: $data);
echo "\n\n";

$url2 = $csv->token['RUTA_LOCAL'] . '/Api/Create?process=2117';
echo "Test 2: POST to $url2\n";
$ch = curl_init($url2);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'ApiAuthorization: ' . $csv->token_api_local,
    'Company: ' . $csv->id_empresa,
    'Content-Type: application/json'
]);
$data2 = curl_exec($ch);
$httpcode2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpcode2\nResponse 2:\n";
print_r(json_decode($data2, true) ?: $data2);
echo "\n\n";
