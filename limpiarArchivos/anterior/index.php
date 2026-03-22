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
<link href="styles.css" rel="stylesheet" type="text/css" />
<?php 
        // Definir la función de manejo de errores personalizada
        function miManejadorDeErrores($nivel, $mensaje, $archivo, $linea) {
            // Puedes personalizar la forma en que manejas los errores aquí
            echo "<br>Ha ocurrido un error: <font color=red>$mensaje</font> en <font color=red>$archivo</font> en la línea <font color=red>$linea</font><br>";
        }
        

        // Registrar el manejador de errores personalizado
        set_error_handler('miManejadorDeErrores');
?>
</head>
	<body link="#000000" alink="#000000" vlink="#000000">
<fieldset><legend>Lectura de archivo CSV</legend>

                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <table border="1">
                    <tr>
                    <td align="center" colspan="5">Parámetros para la lecutra de archivo CSV</td>
                    </tr>
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
                if(isset($_POST['rutaxml'])){
                /*Envío las actualizaciones generadas por los botones*/
                $modelo->actualizoRutaXml($_POST['rutaxml'],$_POST['api_tiendas'],$_POST['api_local'], $_POST['id_empresa'], $_POST['ruta_local'], $_POST['fac_b'], $_POST['fac_ecommerce'], $_POST['fac_expo']);
                } 
                ?>
                    <td align="center"><input name="rutaxml" type="text" value="<?php echo $modelo->rutaXmlConfigurada(); ?>"/></td>
                    <td align="center"><?php $modelo->seleccionoTalonario();?></td>
                    <td align="center"><input name="ruta_local" type="text" value="<?php echo $modelo->selectRxnParametrosRutaLocal(); ?>"/></td>
                    <td align="center"><input name="api_tiendas" type="text" value="<?php echo $modelo->selectRxnParametrosApiTiendas(); ?>"/></td>
                    <td align="center"><input name="api_local" type="text" value="<?php echo $modelo->selectRxnParametrosApiLocal(); ?>"/></td>
                    <td align="center"><input name="id_empresa" type="text" value="<?php echo $modelo->selectRxnParametrosIdEmpresa(); ?>"/></td>
                  </tr>
                    <tr>
                        <th>Número de Factura B</th>
                        <th>Número de Factura Ecommerce</th>
                        <th>Número de Factura E Exportación</th>
                        <th></th>
                        <th></th>
                    </tr>
                    <tr>
                        <td><input name="fac_b" type="text" value="<?php echo $modelo->facB(); ?>"/></td>
                        <td><input name="fac_ecommerce" type="text" value="<?php echo $modelo->facEcommerce(); ?>"/></td>
                        <td><input name="fac_expo" type="text" value="<?php echo $modelo->facExpo(); ?>"/></td>
                        <td></td>
                        <td></td>
                        <td></td>                        
                    </tr>
                  <tr>
                      <td colspan="6" align="center">
                <?php
                if(isset($_POST['Editar']))        {
                    echo "Editado";
                }
                ?>
                      </td>    
                  </tr>
                 </table>
                <input type="submit" value="Editar" name="Editar">
            </form>
<a href="../index.php" title="Click para volver" align="left"><img src="../imagenes/paginaAtras.png" align="left" width="80" height="70" border="0" alt="Volver" title="Volver"/></a>
</fieldset>
</body>
</html>