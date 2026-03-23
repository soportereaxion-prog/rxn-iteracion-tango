<?php
require_once __DIR__ . '/controlador.php';

$csv = new modelo();
$csv->devuelvoTokens();

$reflector = new ReflectionClass('modelo');
$property = $reflector->getProperty('db_sql');
$property->setAccessible(true);
$db_sql = $property->getValue($csv);

echo "\n--- Test 1: Excede 250, cliente correcto ---\n";
// Mock cli_csv
$mock_row = array_fill(0, 30, '');
$mock_row[0] = '3656586'; // TELEFONO_1 matching cod_cliente
$mock_row[17] = '10';     // cat_iva
$mock_row[24] = '3.00';   // alic_perc
$csv->cli_csv = [$mock_row];

try {
    $csv->evaluarYActualizarClienteAPI('3656586', 300);

    echo "\n--- Test 2: Faltante / Cliente No Hallado ---\n";
    $csv->evaluarYActualizarClienteAPI('INEXISTENTE999', 300);

    echo "\n--- Test 3: No supera umbral ---\n";
} catch (\Throwable $e) {
    echo "\n====== FATAL ERROR CAUGHT ======\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    echo "================================\n";
}
