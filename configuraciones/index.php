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
    <title>Re@xion - Lectura CSV</title>
    <link rel="icon" type="image/x-icon" href="../icon1.ico" />
    <link href="../rxn-ui.css" rel="stylesheet" type="text/css" />
    <link href="styles.css" rel="stylesheet" type="text/css" />
    <?php
    // Definir la función de manejo de errores personalizada
    function miManejadorDeErrores($nivel, $mensaje, $archivo, $linea)
    {
        // Puedes personalizar la forma en que manejas los errores aquí
        echo "<br>Ha ocurrido un error: <font color=red>$mensaje</font> en <font color=red>$archivo</font> en la línea <font color=red>$linea</font><br>";
    }


    // Registrar el manejador de errores personalizado
    set_error_handler('miManejadorDeErrores');
    ?>
    <style>
        .cursor {
            color: black !important;
            text-decoration: none;
            font-size: 16px !important;
        }

        .cursor:hover {
            color: cadetblue !important;
        }
        
        /* Vestir inputs y selects dentro de la tabla sin tocar modelo.php */
        .rxn-table input[type="text"], .rxn-table select {
            width: 100%;
            padding: 6px 10px;
            font-size: 14px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .rxn-table input[type="text"]:focus, .rxn-table select:focus {
            border-color: #86b7fe;
            outline: 0;
        }
        .rxn-table th { font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
    </style>
</head>

<body link="#000000" alink="#000000" vlink="#000000" style="background-color: #f8f9fa;">
    <div class="rxn-container">
        <div class="rxn-card">
            <div class="rxn-card-header">
                Configuración de Parámetros
            </div>
            <div class="rxn-card-body">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

                    <div style="overflow-x: auto;">
                        <table class="rxn-table">
                            <!-- Se omite la fila de titulo legacy porque el card-header ya cumple el rol principal -->
                <tr>
                    <th>Ruta para la lectura de CSV</th>
                    <th>Talonario del pedido</th>
                    <th>Ruta local</th>
                    <th>Token API Tiendas</th>
                    <th>Token API local</th>
                    <th>ID Empresa</th>
                    <!-- <th>Color de fondo para comandas Pedidos desde Delivery</th>
                        <th>Color de fondo para comandas Pedidos desde Sal&oacute;n</th>-->
                </tr>
                <tr>
                    <?php
                    ?>
                    <?php
                    if (isset($_POST['rutaxml'])) {
                        /*Envío las actualizaciones generadas por los botones*/
                        $modelo->actualizoRutaXml($_POST['rutaxml'], $_POST['api_tiendas'], $_POST['api_local'], $_POST['id_empresa'], $_POST['ruta_local'], $_POST['fac_b'], $_POST['fac_ecommerce'], $_POST['fac_expo']);
                        
                        // Guardado independiente para la nueva semántica de MODO_PROCESO
                        if(isset($_POST['modo_proceso'])) {
                            $modelo->actualizarModoProceso($_POST['modo_proceso']);
                        }
                    }
                    ?>
                    <td align="center"><input name="rutaxml" type="text" value="<?php echo $modelo->rutaXmlConfigurada(); ?>" /></td>
                    <td align="center"><?php $modelo->seleccionoTalonario(); ?></td>
                    <td align="center"><input name="ruta_local" type="text" value="<?php echo $modelo->selectRxnParametrosRutaLocal(); ?>" /></td>
                    <td align="center"><input name="api_tiendas" type="text" value="<?php echo $modelo->selectRxnParametrosApiTiendas(); ?>" /></td>
                    <td align="center"><input name="api_local" type="text" value="<?php echo $modelo->selectRxnParametrosApiLocal(); ?>" /></td>
                    <td align="center"><input name="id_empresa" type="text" value="<?php echo $modelo->selectRxnParametrosIdEmpresa(); ?>" /></td>
                </tr>
                <tr>
                    <th>Número de Factura B</th>
                    <th>Número de Factura Ecommerce</th>
                    <th>Número de Factura E Exportación</th>
                    <th> Base de datos</th>
                    <th> Modo de Proceso</th>
                    <th></th>
                </tr>
                <tr>
                    <td><input name="fac_b" type="text" value="<?php echo $modelo->facB(); ?>" /></td>
                    <td><input name="fac_ecommerce" type="text" value="<?php echo $modelo->facEcommerce(); ?>" /></td>
                    <td><input name="fac_expo" type="text" value="<?php echo $modelo->facExpo(); ?>" /></td>
                    <td><input name="Nombre_base" type="text" value="<?php echo $modelo->traerBase(); ?>" /></td>
                    <td align="center">
                        <?php $modo_actual = $modelo->leoParametroBd('MODO_PROCESO') ?? 'FACTURA'; ?>
                        <select name="modo_proceso" id="modo_proceso">
                            <option value="FACTURA" <?php if($modo_actual === 'FACTURA') echo 'selected="selected"'; ?>>FACTURA</option>
                            <option value="PEDIDO" <?php if($modo_actual === 'PEDIDO') echo 'selected="selected"'; ?>>PEDIDO</option>
                        </select>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="6" align="left">
                        <?php
                        if (isset($_POST['Editar'])) {
                            echo "<span style='color: #198754; font-weight: 600;'>Configuraciones guardadas exitosamente.</span>";
                        }
                        ?>
                    </td>
                </tr>
            </table>
            </div> <!-- End overflow -->

            <div class="rxn-action-bar" style="margin-top: 15px;">
                <input type="submit" class="rxn-btn rxn-btn-primary" value="Guardar Cambios" name="Editar">
            </div>
        </form>

        <a href="../index.php" title="Click para volver" align="left"><img src="../imagenes/paginaAtras.png" align="left" width="80" height="70" border="0" alt="Volver" title="Volver" /></a>
        <p align="right" style="margin-bottom:0;"><a class="cursor" href="../Ayudas/MenuPrincipal.html?ayuda=ConfiguracionDeDirectorio.html" target="_blank">Ayuda</a></p>

        </div>
    </div>
  </div>
</body>

</html>