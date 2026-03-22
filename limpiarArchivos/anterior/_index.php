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
session_start();

if(isset($_COOKIE["nombre_usuario"])){
    
}else if (!isset($_COOKIE["nombre_usuario"])){

if(isset($_SESSION["usuario"])){
    
}else{

header("Location:login.php");
    
}}
?>
<!doctype html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="estilo.css" rel="stylesheet" type="text/css" />
<title>Enviar Correos</title>
<?php
?>
<script src="custom.js"></script>
    <SCRIPT LANGUAGE="JavaScript">
function justNumbers(e)
        {
        var keynum = window.event ? window.event.keyCode : e.which;
        if ((keynum == 8) || (keynum == 46))
        return true;
         
        return /\d/.test(String.fromCharCode(keynum));
        }
</SCRIPT>
<style>
input:-moz-read-only { /* For Firefox */
    background-color: #E6E6E6;
}

input:read-only {
    background-color: #E6E6E6;
}  

textarea:-moz-read-only { /* For Firefox */
    background-color: #E6E6E6;
}

textarea:read-only {
    background-color: #E6E6E6;
} 

#e1, #e2 {
    width: 250px;
    height: 20;
}
</style>
<link href="select2/dist/css/select2.css" rel="stylesheet"/>
<script src="select2/dist/js/select2.js"></script>
    <script>
        $(document).ready(function() { $("#e1").select2(); });
        $(document).ready(function() { $("#e2").select2(); });
        $(document).ready(function() { $("#e3").select2(); });
        $(document).ready(function() { $("#e4").select2(); });
    </script>
</head>
<!--  onload="nobackbutton();" -->
<body link="#000000" alink="#000000" vlink="#000000" onload="nobackbutton();" >
<?php
require_once ("controlador.php");
//$modelo->ingresar_archivos_base();
//if(isset($_POST['ENVIAR'])){
//$modelo->get_nomen_sel($_POST['selNomenclatura']);
//}
error_reporting(0);
?>
<fieldset><legend>Generar Correo con adjunto al cliente</legend>
<div align="center">
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<?php
if(isset($_POST['validar']) || isset($_POST['REMITO']) || isset($_POST['CORREO'])){
$modelo->boton_remito($_POST['REMITO']);
$modelo->contacto_cliente($_POST['selCliente']);
$modelo->ctrl_fecha($_POST['fecha']);
$modelo->cod_articu($_POST['selArt']);
$modelo->clasif($_POST['selClasif']);
$modelo->sel_contacto($_POST['selContacto']);
$modelo->tarea($_POST['tareas']);
$modelo->descuento($_POST['descuento']);
/*Paso los valores a las variables para que sean controladas*/
$modelo->hora_ini($_POST['hora_ini']);
$modelo->mins_ini($_POST['mins_ini']);
$modelo->segs_ini($_POST['segs_ini']);
$modelo->hora_ini_desc($_POST['hora_ini_desc']);
$modelo->mins_ini_desc($_POST['mins_ini_desc']);
$modelo->segs_ini_desc($_POST['segs_ini_desc']);
$modelo->hora_fin($_POST['hora_fin']);
$modelo->mins_fin($_POST['mins_fin']);
$modelo->segs_fin($_POST['segs_fin']);
$modelo->ctrl_client($_POST['selCliente']);
$modelo->u_s_u($_POST['u_s_u']);

/*Meto la variable de sesión en un post hidden para que no se pierda*/
if(isset($_SESSION['usuario']) || isset($_COOKIE["nombre_usuario"])){
?>
    <input type="hidden" name="u_s_u" value="<?php if(isset($_SESSION['usuario'])){echo $_SESSION['usuario'];}else{echo $_COOKIE["nombre_usuario"];}; ?>">
<?php
}else if(isset($_POST['u_s_u'])){
?>   
    <input type="hidden" name="u_s_u" value="<?php echo $_POST['u_s_u']; ?>">
<?php
}
}
?>
<table width="850" border="0">
  <tr>
      <td colspan="2">Hola! <strong><?php if(isset($_SESSION["usuario"])){echo $_SESSION['usuario'];}else{echo $_COOKIE["nombre_usuario"];}; ?></strong></td>
      <td><a href="../cerrar_sesion.php">Cerrar sesi&oacute;n</a></td>
  </tr>
  <tr>
      <td colspan="3">Verific&aacute; que todos los campos sean validados correctamente</td>
  </tr>
  <tr>
    <!--Letra asignada U-->
    <!--Letra asignada i-->
    <td>Cliente<?php 
    //$modelo->selecciono_nomenclatura();
	/*Si se presionó en nuevo que no llegue ningún cliente, así se completa el circuito*/
	if(isset($_POST['nuevo'])){}else{
    $modelo->seleccionoCliente();}
    ?></td>
    
    <!--Selección de la fecha para el pedido -->
    <!-- Primer Calenadrio -->
    <td>Fecha:
        <input class="texto" type="text" accesskey="F" title="ALT + SHIFT + F" name="fecha" id="sel1" size="30" value="<?php if(isset($_POST['validar']) || isset($_POST['REMITO']) || isset($_POST['CORREO'])){ echo $_POST['fecha'];  }?>"/><input class="numerico" type="reset" value=" ... " onclick="return showCalendar('sel1', '%d/%m/%Y');" /></td>
    <!-- Fin Primer Calenadrio -->
    <td>Articulo<?php 
    //$modelo->selecciono_nomenclatura();
    $modelo->seleccionoArticulo();
    ?></td>
  </tr>
  <tr><td colspan="3"></td></tr>
  <tr>
      <td><?php $modelo->seleccionoClasificacion(); ?></td>
      <td><?php if($_POST['selContacto'] === "SIN_CONTACTO"){ echo "<font color='red'><strong>Seleccionar contacto-></strong></font>";} if($_POST['selContacto'] == "1" || $_POST['selCliente'] == ""){echo  "<font size='1' color='green'><strong>Completar campos y Validar</strong></font>";} ?></td>
      <td>Se atendi&oacute;: <?php if(isset($_POST['validar']) || isset($_POST['REMITO']) || isset($_POST['CORREO'])){$modelo->controlo_contacto_seleccionado();$modelo->seleccionoContacto(); $modelo->ctrl_contacto_error();} ?></td>
  </tr>
  <tr><td colspan="1">Inicio</td><td>Descuento</td><td>Fin</td></tr>
  <tr>
      <td>HS<input type="text" maxlength="2" onkeypress="return justNumbers(event);" <?php if($_POST['selContacto'] == "" || $_POST['selContacto'] == "1" || $_POST['selContacto'] === "SIN_CONTACTO" || isset($_POST['CORREO']) || isset($_POST['REMITO'])){ echo "READONLY";} ?> name="hora_ini" class="tiempo" value="<?php if(isset($_POST['validar']) || isset($_POST['REMITO']) || isset($_POST['CORREO'])){echo $_POST['hora_ini'];} ?>" > MS<input type="text" <?php if($_POST['selContacto'] == "" || $_POST['selContacto'] == "1" || $_POST['selContacto'] === "SIN_CONTACTO" || isset($_POST['CORREO']) || isset($_POST['REMITO'])){ echo "READONLY";} ?> name="mins_ini" onkeypress="return justNumbers(event);"  maxlength="2" class="tiempo" value="<?php if(isset($_POST['validar']) || isset($_POST['REMITO']) || isset($_POST['CORREO'])){echo $_POST['mins_ini'];} ?>" >SEG<input type="text" <?php if($_POST['selContacto'] == "" || $_POST['selContacto'] == "1" || $_POST['selContacto'] === "SIN_CONTACTO" || isset($_POST['CORREO']) || isset($_POST['REMITO'])){ echo "READONLY";} ?> name="segs_ini" onkeypress="return justNumbers(event);" maxlength="2" class="tiempo" value="<?php if(isset($_POST['validar']) || isset($_POST['REMITO']) || isset($_POST['CORREO'])){echo $_POST['segs_ini'];} ?>" ></td>
      <td>HS<input type="text" maxlength="2" onkeypress="return justNumbers(event);" <?php if($_POST['selContacto'] == "" || $_POST['selContacto'] == "1" || $_POST['selContacto'] === "SIN_CONTACTO" || isset($_POST['CORREO']) || isset($_POST['REMITO'])){ echo "READONLY";} ?> name="hora_ini_desc" class="tiempo" value="<?php if(isset($_POST['validar']) || isset($_POST['REMITO']) || isset($_POST['CORREO'])){echo $_POST['hora_ini_desc'];} ?>" > MS<input type="text"  <?php if($_POST['selContacto'] == "" || $_POST['selContacto'] == "1" || $_POST['selContacto'] === "SIN_CONTACTO" || isset($_POST['CORREO']) || isset($_POST['REMITO'])){ echo "READONLY";} ?> maxlength="2" name="mins_ini_desc" onkeypress="return justNumbers(event);" class="tiempo" value="<?php if(isset($_POST['validar']) || isset($_POST['REMITO']) || isset($_POST['CORREO'])){echo $_POST['mins_ini_desc'];} ?>" >SEG<input type="text" <?php if($_POST['selContacto'] == "" || $_POST['selContacto'] == "1" || $_POST['selContacto'] === "SIN_CONTACTO" || isset($_POST['CORREO']) || isset($_POST['REMITO'])){ echo "READONLY";} ?> maxlength="2" onkeypress="return justNumbers(event);" name="segs_ini_desc" class="tiempo" value="<?php if(isset($_POST['validar']) || isset($_POST['REMITO']) || isset($_POST['CORREO'])){echo $_POST['segs_ini_desc'];} ?>" ></td>
      <td>HS<input type="text" maxlength="2" onkeypress="return justNumbers(event);" <?php if($_POST['selContacto'] == "" || $_POST['selContacto'] == "1" || $_POST['selContacto'] === "SIN_CONTACTO" || isset($_POST['CORREO']) || isset($_POST['REMITO'])){ echo "READONLY";} ?> name="hora_fin" class="tiempo" value="<?php if(isset($_POST['validar']) || isset($_POST['REMITO']) || isset($_POST['CORREO'])){echo $_POST['hora_fin'];} ?>" > MS<input type="text" <?php if($_POST['selContacto'] == "" || $_POST['selContacto'] == "1" || $_POST['selContacto'] === "SIN_CONTACTO" || isset($_POST['CORREO']) || isset($_POST['REMITO'])){ echo "READONLY";} ?> name="mins_fin"  maxlength="2" onkeypress="return justNumbers(event);" class="tiempo" value="<?php if(isset($_POST['validar']) || isset($_POST['REMITO']) || isset($_POST['CORREO'])){echo $_POST['mins_fin'];} ?>" >SEG<input type="text" name="segs_fin" <?php if($_POST['selContacto'] == "" || $_POST['selContacto'] == "1" || $_POST['selContacto'] === "SIN_CONTACTO" || isset($_POST['CORREO']) || isset($_POST['REMITO'])){ echo "READONLY";} ?>  maxlength="2" onkeypress="return justNumbers(event);" class="tiempo" value="<?php if(isset($_POST['validar']) || isset($_POST['REMITO']) || isset($_POST['CORREO'])){echo $_POST['segs_fin'];} ?>" ></td>
  </tr>
  <tr>
      <td><?php if(isset($_POST['validar']) || isset($_POST['REMITO']) || isset($_POST['CORREO'])){if($_POST['selContacto'] === "" || $_POST['selContacto'] == "1" || $_POST['selContacto'] == "SIN_CONTACTO" || $_POST['selCliente'] == ""){}else{$modelo->controlo_ini(); $modelo->ctrl_ini_error(); }}?></td>
      <td><?php if(isset($_POST['validar']) || isset($_POST['REMITO']) || isset($_POST['CORREO'])){if($_POST['selContacto'] === "" || $_POST['selContacto'] == "1" || $_POST['selContacto'] == "SIN_CONTACTO" || $_POST['selCliente'] == ""){}else{$modelo->controlo_descuento(); $modelo->ctrl_desc_error(); }}?></td>
      <td><?php if(isset($_POST['validar']) || isset($_POST['REMITO']) || isset($_POST['CORREO'])){if($_POST['selContacto'] === "" || $_POST['selContacto'] == "1" || $_POST['selContacto'] == "SIN_CONTACTO" || $_POST['selCliente'] == ""){$modelo->controlo_fin();}else{$modelo->controlo_fin(); $modelo->ctrl_fin_error(); }}?></td>
  </tr>
  <tr>
      <?php if(isset($_POST['validar']) || isset($_POST['REMITO']) || isset($_POST['CORREO']))
          //"10:00","11:00"
          { $modelo->calculo_tiempo($_POST['hora_ini'].":".$_POST['mins_ini'].":".$_POST['segs_ini'],$_POST['hora_ini_desc'].":".$_POST['mins_ini_desc'].":".$_POST['segs_ini_desc'],$_POST['hora_fin'].":".$_POST['mins_fin'].":".$_POST['segs_fin']); }else{$modelo->calculo_tiempo("00:00","00:00","00:00");} // 
          //, 
          
          ?>
      <td>Total Tiempo: <?php echo $modelo->string_tiempo ?></td><td></td><td> Decimal: <?php echo $modelo->total_tiempo; ?></td>
  </tr>
  <tr>
      <td colspan="3"><textarea class="tareas" name="tareas" <?php if($_POST['selContacto'] == "" || $_POST['selContacto'] == "1" || $_POST['selContacto'] === "SIN_CONTACTO" || isset($_POST['CORREO']) || isset($_POST['REMITO'])){ echo "READONLY";} ?> accesskey="A" title="ALT + SHIFT + A" cols="200" rows="4"><?php if(isset($_POST['validar']) || isset($_POST['REMITO']) || isset($_POST['CORREO'])){echo $_POST['tareas'];} ?></textarea></td>
  </tr>
  <tr>
      <td colspan="3" class="motivo">Motivo del descuento</td>
  </tr>
  <tr>
      <td colspan="3"><textarea class="tareas" <?php if($_POST['selContacto'] == "" || $_POST['selContacto'] == "1" || $_POST['selContacto'] === "SIN_CONTACTO" || isset($_POST['CORREO']) || isset($_POST['REMITO'])){ echo "READONLY";} ?> name="descuento" cols="200" rows="1"><?php if(isset($_POST['validar']) || isset($_POST['REMITO']) || isset($_POST['CORREO'])){echo $_POST['descuento'];} ?></textarea></td>
  </tr>
      <tr>
          <?php
          if(isset($_POST['REMITO']) || isset($_POST['CORREO'])){
          if(isset($_POST['CORREO'])){
          ?>
          <!--<input type="submit" name="CORREO" value="CORREO">-->
          <?php
          $modelo->enviar_correos_cliente();
          }
          ?>
          <td colspan="2"><font color="red"><strong>Controlar copia de correo a soporte@reaxion.com.ar<br>EL REMITO YA FUE INGRESADO AL SISTEMA</strong></font></td><td colspan="1"><input type="submit" name="NUEVO" value="NUEVO"><input type="submit" name="CORREO" value="CORREO"></td>
          <?php
          }else{
          ?>
          <td colspan="3"><input type="submit" name="validar" value="VALIDAR" title="PRESIONA ALT + SHIFT + V" accesskey="V"><?php if(isset($_POST['obtener'])){ echo '<input type="submit" name="ENVIAR" value="ENVIAR"/>'; }else if(isset($_POST['ENVIAR'])){ echo '<input type="submit" name="LISTO" value="LISTO"/>'; } ?>
          <?php
          }
          $modelo->ctrl_ingreso();
          $modelo->muestro_boton_remito();
          //echo "->".$_POST['selContacto']."<-";
          ?>
            
      </tr>
  <tr>
      <td><?php if($_POST['selCliente'] !== ""){ $modelo->horas_cliente_mes(); echo "Horas este mes: ".round($modelo->horas_cliente_mes,4); } ?></td>
      <td><?php if($_POST['selCliente'] !== ""){ $modelo->cliente_clasificado(); echo $modelo->cliente_clasificado; } ?></td>
      <td><?php if(isset($_POST['REMITO'])){ echo "Rto - Nro: ".$modelo->sucu_tal_actual.str_pad($modelo->ult_remi_ususario+1,8,"0", STR_PAD_LEFT); } ?></td>
  </tr>
</table>
</form>
</div>
<a href="../index.php" title="Click para volver" align="left"><img src="../imagenes/paginaAtras.png" align="left" width="80" height="70" border="0" alt="Volver" title="Volver"/></a>
</fieldset>
<div align="right">
<a href="mailto:soporte@reaxion.com.ar">Re@xion ---> desarrollo de soluciones para Tango <---</a> 
</div>
</body>
</html>