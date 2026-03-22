<?php
require_once "ConectarBase.php";
$bd = new ConectarBd();
$conexion = $bd->Conexion();

try {
    $sql = "ALTER TABLE RXN_PARAMETROS ADD MODO_PROCESO VARCHAR(20) DEFAULT 'FACTURA'";
    $stmt = $conexion->query($sql);
    echo "Columna MODO_PROCESO añadida a RXN_PARAMETROS exitosamente.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
