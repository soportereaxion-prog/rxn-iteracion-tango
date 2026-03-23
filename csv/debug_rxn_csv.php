<?php
require 'controlador.php';

// Simulate POST
$_POST['fecha'] = '22/03/2026';

echo "1. Insertando forzosamente...\n";
$modelo->leo_ingreso_directorio_csv();

echo "2. Llamando procesoPedidos('PROCESAR')...\n";
ob_start();
$modelo->procesoPedidos('PROCESAR');
$output = ob_get_clean();

echo "OUTPUT DE PROCESOPEDIDOS:\n" . $output . "\n";
echo "- enc_pedi_csv count: " . (isset($modelo->enc_pedi_csv) ? count($modelo->enc_pedi_csv) : "NULL") . "\n";
