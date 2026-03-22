<?php
require_once __DIR__ . '/../auth/guard.php';
/*
  --------------------------------------------------------------------------------------------
  |                          Ch4rl1X Desarrollo de aplicaciones web y móviles                |
  |                                                                                          |
  |                                  correo: charly@charlesweb.com.ar                        |
  |                                     web: www.charlesweb.com.ar                           |
  |                                                                                          |
  | Este material es apto para ser difundido y compartido. Utilizalo bajo tu responsabilidad.|
  --------------------------------------------------------------------------------------------
 * 
 */
?>
<?php
include('controlador.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Re@xion - Proceso CSV API</title>
    <link rel="icon" type="image/x-icon" href="../icon1.ico" />

    <!--<link href="styles.css" rel="stylesheet" type="text/css" /> <meta http-equiv="refresh" content="5"/>-->
    <?php
    //error_reporting(0);
    $modelo->leo_ingreso_directorio_csv();
    include("comboBoxs.php");
    if (isset($_POST['fecha'])) {
        $modelo->fechaFac($_POST['fecha']);
    }
    // Definir la función de manejo de errores personalizada
    function miManejadorDeErrores($nivel, $mensaje, $archivo, $linea)
    {
        // Puedes personalizar la forma en que manejas los errores aquí
        echo "<br>Ha ocurrido un error: <font color=red>$mensaje</font> en <font color=red>$archivo</font> en la línea <font color=red>$linea</font><br>";
    }


    // Registrar el manejador de errores personalizado
    set_error_handler('miManejadorDeErrores');
    ?>
    <link href="importa.css" rel="stylesheet" type="text/css" />
    <link href="../rxn-ui.css" rel="stylesheet" type="text/css" />

    <style>
        .cursor {
            color: black !important;
            text-decoration: none;
            font-size: 16px !important;
        }

        .cursor:hover {
            color: cadetblue !important;
        }
    </style>
</head>

<body link="#000000" alink="#000000" vlink="#000000" style="background-color: #f8f9fa;">
    <div class="rxn-container">
        <div class="rxn-card">
            <div class="rxn-card-header">
                Rechazar Pendientes CSV
            </div>
            <div class="rxn-card-body">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <p style="margin-top: 0;">Con este proceso se marcarán como pendientes de eliminar todos los comprobantes que quedan en cola para obtener CAE, luego de ejecutar este proceso, dar click en "Actualizar" en el administrador de comprobantes electrónicos para que se habilite el botón "Eliminar rechazados"</p>
                    
                    <div class="rxn-action-bar">
                        <input type="submit" class="rxn-btn rxn-btn-primary" value="Rechazar" name="Rechazar" title="Rechazar pendientes ALT + R" accesskey="R" />
                    </div>
                    <br />
                    <?php
                    //$modelo->muestroNombreArchivoReproceso();
                    ?>
                    
                    <?php
                    if (isset($_POST['Rechazar'])) {
                        /* Llamo al método que rechaza los pendientes */
                        $modelo->cambioEstadoRechazados();
                        /*Dejo el comprobante pendiente para reprocesar*/
                        $modelo->actualizo_para_reproceso();
                        echo "<p style='color: #198754; font-weight: 600;'>Se han rechazado todos los comprobantes pendientes</p>";
                    }
                    ?>
                    
                    <a href="../index.php" title="Click para volver" align="left"><img src="../imagenes/paginaAtras.png" align="left" width="80" height="70" border="0" alt="Volver" title="Volver" /></a>
                </form>

                <p align="right" style="margin-bottom:0;"><a class="cursor" href="../Ayudas/MenuPrincipal.html?ayuda=RechazarPendientes.html" target="_blank">Ayuda</a></p>
            </div>
        </div>
    </div>
</body>

</html>