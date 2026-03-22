<?php
require_once __DIR__ . '/../auth/guard.php';
include('controlador.php');

if (!isset($_POST['Procesar'])) {
    header("Location: index.php");
    exit;
}

// Configurar forzado de reporte para log por precaucion
set_error_handler(function($nivel, $mensaje, $archivo, $linea) {
    echo "<br><span style='color:red;'>Error: $mensaje en $archivo (línea $linea)</span><br>";
});
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Procesando API...</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            background-color: #1e1e1e;
            color: #00ff00;
            padding: 15px;
            font-size: 14px;
            margin: 0;
            line-height: 1.4;
        }
        .fin { margin-top: 20px; font-weight: bold; color: #fff; border-top: 1px dashed #555; padding-top: 10px; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #1e1e1e; }
        ::-webkit-scrollbar-thumb { background: #555; border-radius: 4px; }
        h1, h2, h3 { color: #fff; margin-top: 0; }
    </style>
</head>
<body>
<?php
/* Re-inicializo estado del directorio al procesar para soportar testing sin salir de la pantalla */
$modelo->leo_ingreso_directorio_csv();

/* Vacío los clientes */
$modelo->vacioClientes();
/* Proceso los archivos que sean de clientes pendientes */
$modelo->procesoCsvClientes();
/* Proceso archivo de artículos */
$modelo->procesoCsvArticulos();

if (isset($_POST['fecha']) && $_POST['fecha'] !== '') {
    /* Proceso pedidos */
    $modelo->procesoPedidos('PROCESAR');
} else {
    echo '<span style="color:red;">Para procesar Facturas o Pedidos debe estar la fecha configurada</span><br>';
}

echo '<div class="fin">--- PROCESAMIENTO FINALIZADO ---</div>';
?>
<script>
    // Hacer scroll down automático al finalizar la impresión síncrona de PHP
    window.scrollTo(0, document.body.scrollHeight);
</script>
</body>
</html>
