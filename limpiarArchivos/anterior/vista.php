<?php
class vista{

/*Guardo los valores para mostrarlos en un selector*/
public function seleccionoCliente(){

//$matriz_nomenclatura = ;
?>
<select name="selCliente" id="e1">
		<option value=""></option>
<?php  
$this->selecciono_cliente();
foreach ($this->cliente as $cliente){
        /*Si no viene el cliente el genero un valor para que no marque error*/
        if(!isset($_POST['selCliente'])){$_POST['selCliente'] = 1;}
        if($cliente['COD_CLIENT'] == $_POST['selCliente']){
?>
        <option value="<?php echo $_POST['selCliente']; ?>" selected="selected"><?php echo $cliente['RAZON_SOCI']; ?></option>
<?php
        }else{
?>
        <option value="<?php echo $cliente['COD_CLIENT']; ?>"><?php echo $cliente['RAZON_SOCI']; ?></option>
<?php
        }
}
?>
</select>
<?php  
}

public function seleccionoArticulo(){

//$matriz_nomenclatura = ;
?>
<select name="selArt" id="e2">
		<option value=""></option>
<?php  
$this->selec_art();
foreach ($this->art as $art){
        /*Si no viene el art el genero un valor para que no marque error*/
        if(!isset($_POST['selArt'])){$_POST['selArt'] = 1;}
        if($art['COD_ARTICU'] == $_POST['selArt']){
?>
        <option value="<?php echo $_POST['selArt']; ?>" selected="selected"><?php echo $art['COD_ARTICU'].' '.$art['DESCRIPCIO']; ?></option>
<?php
        }else{
?>
        <option value="<?php echo $art['COD_ARTICU']; ?>"><?php echo $art['COD_ARTICU'].' '.$art['DESCRIPCIO']; ?></option>
<?php
        }
}
?>
</select>
<?php  
}

/*Mensajes de error para tiempo*/
public function ctrl_ini_error(){
    if($this->ctrl_ini == 1){
        if($this->hora_ini !=="" || $this->mins_ini !=="" || $this->segs_ini !==""){
        echo "<font color=red><strong>ERROR</strong></font>";
        }
    }
}

public function ctrl_desc_error(){
    if($this->ctrl_ini == 1){
        if($this->hora_ini_desc !=="" || $this->mins_ini_desc !=="" || $this->segs_ini_desc !==""){
        echo "<font color=red><strong>ERROR</strong></font>";
        }
    }
}

public function ctrl_fin_error(){
    if($this->ctrl_ini == 1){
        if($this->hora_fin !=="" || $this->mins_fin !=="" || $this->segs_fin !==""){
        echo "<font color=red><strong>ERROR</strong></font>";
        }
    }
}

public function muestro_boton_remito(){
    //$this->ingreso_tareas_bd();
    if($this->ctrl_remito == 0){
        if(isset($this->boton_remito)){
        $this->ingreso_tareas_bd();
        $this->ingreso_remito();
        }
  if(isset($this->boton_remito)){}else{
  if(isset($_POST['CORREO'])){}else{
?>
<input type="submit" accesskey="R" title="ALT + SHIFT + R" name="REMITO" value="REMITO"></td>
<?php

  }}}
}


/*Mensaje de error para contacto seleccionado*/
public function ctrl_contacto_error(){
    if($this->ctrl_ini == 1){
        echo "<font color=red><strong>NO SE FILTRO EL CLIENTE</strong></font>";
    }
}

public function seleccionoClasificacion(){

//$matriz_nomenclatura = ;
?>
<select name="selClasif" id="e3">
		<option value="SIN_CLASIFICACION">SIN_CLASIFICACION</option>
<?php  
$this->selec_clasif();
foreach ($this->cod_clasif as $clasif){
        /*Si no viene el clasif el genero un valor para que no marque error*/
        if(!isset($_POST['selClasif'])){$_POST['selClasif'] = 1;}
        if($clasif['COD_CLASIF'] == $_POST['selClasif']){
?>
        <option value="<?php echo $_POST['selClasif']; ?>" selected="selected"><?php echo $clasif['COD_CLASIF'].' '.$clasif['DESCRIP']; ?></option>
<?php
        }else{
?>
        <option value="<?php echo $clasif['COD_CLASIF']; ?>"><?php echo $clasif['COD_CLASIF'].' '.$clasif['DESCRIP']; ?></option>
<?php
        }
}
?>
</select>
<?php  
}

public function seleccionoContacto(){

if($this->ctrl_ini == 0){
    
//$matriz_nomenclatura = ;
?>
<select name="selContacto" id="e4" title="ALT + SHIFT + C" accesskey="C">
		<option value="SIN_CONTACTO">SIN_CONTACTO</option>
<?php  
$this->contacto();
if($this->ctrl_devuelve_contacto > 0){
foreach ($this->contacto as $contacto){
        /*Si no viene el contacto el genero un valor para que no marque error*/
        if(!isset($_POST['selContacto'])){$_POST['selContacto'] = 1;}
        if($contacto['NOMBRE'] == $_POST['selContacto']){
?>
        <option value="<?php echo $_POST['selContacto']; ?>" selected="selected"><?php echo $contacto['NOMBRE']; ?></option>
<?php
        }else{
?>
        <option value="<?php echo $contacto['NOMBRE']; ?>"><?php echo $contacto['NOMBRE']; ?></option>
<?php
        }
}

?>
</select>
<?php  
}}
}

 
public function muestro_pendientes(){
    if(isset($_POST['obtener']) || isset($_POST['ENVIAR']) || isset($_POST['LISTO'])){
    $this->obtener_pendientes($_POST['selNomenclatura']);
    //echo $this->ctrl_pen;

    $this->obtener_pendientes_adjunto($_POST['selNomenclatura']);
    $this->obtener_pendientes_adjunto_listo($_POST['selNomenclatura']);
    if(isset($_POST['LISTO'])){$this->act_registros_enviados();}
    
    if($this->ctrl_pen == 0){        
        
?>
<tr>
    <td colspan="2"><strong><font color="red"><?php echo "NO HAY PENDIENTES"; ?></strong></strong></td>
</tr>
<?php
    }
    foreach ($this->nombre_pdf as $nombre_archivo){
?>
<tr>
<td colspan="2"><?php echo $nombre_archivo['NOMBRE_ARCHIVO']; ?></td>
</tr>
<?php
    }
    
    }
}

public function msj_cliente_actualizado(){
    if($this->verif_ing_nom > 0){
        echo "El registro se ha ingresado correctamnte";
    }
}

public function ctrl_existencia(){
    if(isset($_POST['generar'])){
        $this->ctrl_nomenclatura($_POST['selNomenclatura'],$_POST['nomenclatura']);
        if($this->ctrl_nom >= 1){
            echo "La nomenclatura existe, no se ingresa";
        }else{
            $this->ingresoNomenclatura($_POST['selNomenclatura'],$_POST['nomenclatura']);
        }
    }
}

public function ingresar_archivos_base(){
    if(isset($_POST['obtener'])){
        $this->ingresosArchivosEnBase();
    }
    
}

public function enviar_correos_cliente(){
    if(isset($_POST['REMITO']) || $_POST['CORREO']){
    if(isset($_POST['REMITO'])){
    $this->envia_correo();
    }else{
            $this->horas_cliente_mes();
            $this->get_razon_soci();
            $this->remito_usuario_correo();
            if($this->descuento !== ""){
                $this->tarea_para_mail($this->tarea.' Descuento: '.$this->descuento);
            }else{
                $this->tarea_para_mail($this->tarea);
            }
    $this->envia_correo();
    }
    }
}

public function finalizar(){
    if(isset($_POST['ENVIAR'])){
?>
<tr>
    <td colspan="2"><strong><font color="green"><?php echo "Si no hay mensajes de error los correos se enviaron correctamente, click en listo para finalizar"; ?></strong></strong></td>
</tr>
    
<?php
    }
}

public function formato_correo(){
return $var = '
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html";
      charset=windows-1252">
    <meta name="Generator" content="Microsoft Word 15 (filtered medium)">
    <!--[if !mso]><style>v\:* {behavior:url(#default#VML);}
o\:* {behavior:url(#default#VML);}
w\:* {behavior:url(#default#VML);}
.shape {behavior:url(#default#VML);}
</style><![endif]-->
    <style><!--
/* Font Definitions */
@font-face
	{font-family:"Cambria Math";
	panose-1:2 4 5 3 5 4 6 3 2 4;}
@font-face
	{font-family:Calibri;
	panose-1:2 15 5 2 2 2 4 3 2 4;}
@font-face
	{font-family:Tahoma;
	panose-1:2 11 6 4 3 5 4 4 2 4;}
/* Style Definitions */
p.MsoNormal, li.MsoNormal, div.MsoNormal
	{margin:0cm;
	margin-bottom:.0001pt;
	font-size:12.0pt;
	font-family:"Times New Roman","serif";
	color:black;}
a:link, span.MsoHyperlink
	{mso-style-priority:99;
	color:#0563C1;
	text-decoration:underline;}
a:visited, span.MsoHyperlinkFollowed
	{mso-style-priority:99;
	color:#954F72;
	text-decoration:underline;}
span.EmailStyle17
	{mso-style-type:personal-compose;
	font-family:"Calibri","sans-serif";
	color:windowtext;}
.MsoChpDefault
	{mso-style-type:export-only;
	font-family:"Calibri","sans-serif";
	mso-fareast-language:EN-US;}
@page WordSection1
	{size:612.0pt 792.0pt;
	margin:70.85pt 3.0cm 70.85pt 3.0cm;}
div.WordSection1
	{page:WordSection1;}
-->
table { border-collapse: separate; }
td { border: solid 1px #E3E4E6; }
/*PARA LA PRIMERA FILA TIENES DOS OPCIONES*/
/*Así sería la cosa
si has empezado con un tr
*/
tr:first-child td:first-child { border-top-left-radius: 10px; }
tr:first-child td:last-child { border-top-right-radius: 10px; }
tr:first-child td:only-child { border-top-right-radius: 10px;
border-top-left-radius: 10px; }
/*si en lugar de eso has usado la etiquetas thead y th es más
sencillo todavía*/
th:first-child { border-top-left-radius: 10px; }
th:last-child { border-top-right-radius: 10px; }
th:only-child { border-top-right-radius: 10px;
border-top-left-radius: 10px; }

/*Y ASÍ PONEMOS EL PIE*/
tr:last-child td:first-child { border-bottom-left-radius: 10px; }
tr:last-child td:last-child { border-bottom-right-radius: 10px; }
tr:last-child td:only-child { border-bottom-right-radius: 10px;border-bottom-left-radius: 10px; }
</style><!--[if gte mso 9]><xml>
<o:shapedefaults v:ext="edit" spidmax="1026" />
</xml><![endif]--><!--[if gte mso 9]><xml>
<o:shapelayout v:ext="edit">
<o:idmap v:ext="edit" data="1" />
</o:shapelayout></xml><![endif]-->
    <title>[Re@xion] Detalle tareas realizadas</title>
  </head>
  <body vlink="#954F72" text="#000000" link="#0563C1" lang="ES-AR"
    bgcolor="#FFFFFF">
    <div class="WordSection1">
    
      <p class="MsoNormal"><span
style="font-size:11.0pt;font-family:&quot;Calibri&quot;,&quot;sans-serif&quot;"><img
            id="_x0000_i1025"
src="http://reaxion.solutions/imgs_correo/encabezado.jpg"
            class="" width="589" height="75"></span><o:p></o:p></p>
      <p class="MsoNormal"><span
style="font-size:10.0pt;font-family:&quot;Tahoma&quot;,&quot;sans-serif&quot;">&nbsp;</span><o:p></o:p></p>


<div class="grid-block" style="background-image: url(\'http://reaxion.solutions/imgs_correo/RxN_Fondo_Mail.jpg"\');background-repeat: no-repeat; width: 100%; height: 100%; ">  


    
      <p class="MsoNormal"><span
style="font-size:10.0pt;font-family:&quot;Tahoma&quot;,&quot;sans-serif&quot;">Estimado '.utf8_decode($this->get_razon_soci).'</span><o:p></o:p></p>
      <p class="MsoNormal"><span
style="font-size:10.0pt;font-family:&quot;Tahoma&quot;,&quot;sans-serif&quot;">&nbsp;</span><o:p></o:p></p>
      <p class="MsoNormal"><span
style="font-size:10.0pt;font-family:&quot;Tahoma&quot;,&quot;sans-serif&quot;">Hemos atendido a <strong>'.utf8_decode($this->sel_contacto).'</strong> y se ha procesado la siguiente informaci&oacute;n: </span>
    <o:p></o:p>
    <span
style="font-size:10.0pt;font-family:&quot;Tahoma&quot;,&quot;sans-serif&quot;">&nbsp;</span>
    <o:p></o:p>
    </p>
      <table width="750">
        <tr>
          <td colspan="4" align="center">Detalle de tarea realizada</td>
        </tr>
        <tr>
          <td colspan="4" align="left"><span class="MsoNormal"><span
style="font-size:10.0pt;font-family:&quot;Tahoma&quot;,&quot;sans-serif&quot;"><strong>'.utf8_decode($this->tarea_para_mail).'</strong></span>
              <o:p></o:p>
          </span></td>
        </tr>
        <tr>
          <td align="center"><span style="font-size:10.0pt;font-family:&quot;Tahoma&quot;,&quot;sans-serif&quot;"><strong>Fecha</strong></span></td>
          <td align="center"><span style="font-size:10.0pt;font-family:&quot;Tahoma&quot;,&quot;sans-serif&quot;">Inicio</span></td>
          <td align="center"><span style="font-size:10.0pt;font-family:&quot;Tahoma&quot;,&quot;sans-serif&quot;">Descuento</span></td>
          <td align="center"><span style="font-size:10.0pt;font-family:&quot;Tahoma&quot;,&quot;sans-serif&quot;">Fin</span></td>
        </tr>
        <tr>
          <td align="center"><span style="font-size:10.0pt;font-family:&quot;Tahoma&quot;,&quot;sans-serif&quot;"><strong>'.$this->ctrl_fecha.'</strong></span></td>
          <td align="center"><span style="font-size:10.0pt;font-family:&quot;Tahoma&quot;,&quot;sans-serif&quot;">'.$this->hora_ini.':'.$this->mins_ini.':'.$this->segs_ini.'</span></td>
          <td align="center"><span style="font-size:10.0pt;font-family:&quot;Tahoma&quot;,&quot;sans-serif&quot;">'.$this->hora_ini_desc.':'.$this->mins_ini_desc.':'.$this->segs_ini_desc.'</span></td>
          <td align="center"><span style="font-size:10.0pt;font-family:&quot;Tahoma&quot;,&quot;sans-serif&quot;">'.$this->hora_fin.':'.$this->mins_fin.':'.$this->segs_fin.' </span></td>
        </tr>
        <tr>
          <td colspan="2" align="center"><strong>Total de tiempo de esta tarea</strong></td>
          <td colspan="2" align="center"><span style="font-size:10.0pt;font-family:&quot;Tahoma&quot;,&quot;sans-serif&quot;">Remito</span></td>
        </tr>
        <tr>
          <td colspan="2" align="center"><strong>'.$this->string_tiempo.'</strong></td>
          <td colspan="2" align="center"><span style="font-size:10.0pt;font-family:&quot;Tahoma&quot;,&quot;sans-serif&quot;"><font color="green">'.$this->sucu_tal_actual.str_pad($this->ult_remi_ususario+1,8,"0", STR_PAD_LEFT).'</font></span></td>
        </tr>
        <tr>
          <td colspan="4" align="left"><span class="MsoNormal"><span
style="font-size:10.0pt;font-family:&quot;Tahoma&quot;,&quot;sans-serif&quot;"><em>Total de horas realizadas al cliente este mes: '.round($this->horas_cliente_mes,4).'</em></span>
              <em>
              <o:p></o:p>
          </em></span></td>
        </tr>
  </table>
    <p class="MsoNormal"><o:p></o:p>
      <span
style="font-size:10.0pt;font-family:&quot;Tahoma&quot;,&quot;sans-serif&quot;">&nbsp;</span>
      <o:p></o:p>
    </p>
      <p class="MsoNormal"><span
style="font-size:10.0pt;font-family:&quot;Tahoma&quot;,&quot;sans-serif&quot;">Recuerde

          que el <b>Detalle de las Tareas</b> es enviado al finalizar
          los trabajos, Ud. cuenta con 48 hs para efectuar cualquier
          consulta o reclamo. Puede solicitar el informe completo de las
    tareas cuando lo de desee.</span><o:p></o:p></p>
    <p class="MsoNormal"><span
style="font-size:10.0pt;font-family:&quot;Tahoma&quot;,&quot;sans-serif&quot;">&nbsp;</span><o:p></o:p></p>
      <p class="MsoNormal"><span
style="font-size:10.0pt;font-family:&quot;Tahoma&quot;,&quot;sans-serif&quot;">Desde

          ya, muchas gracias.</span><o:p></o:p>
          <o:p></o:p>
      </p>
      <p class="MsoNormal">&nbsp; </p>
    <p class="MsoNormal"><b><i><span
              style="font-family:&quot;Tahoma&quot;,&quot;sans-serif&quot;">Dpto.
        
    Soporte y Capacitaci&oacute;n</span></i></b><o:p></o:p></p>
      <p class="MsoNormal"><span
style="font-size:8.0pt;font-family:&quot;Tahoma&quot;,&quot;sans-serif&quot;"><img
            id="Imagen_x0020_2"
src="http://reaxion.solutions/imgs_correo/web.png"
            alt="web2" class="" width="15" height="15">: </span><a
          href="http://www.reaxion.com.ar/"><span
style="font-size:8.0pt;font-family:&quot;Tahoma&quot;,&quot;sans-serif&quot;">http://www.reaxion.com.ar</span></a><o:p></o:p></p>
      <p class="MsoNormal"><span
style="font-size:8.0pt;font-family:&quot;Tahoma&quot;,&quot;sans-serif&quot;"><img
            id="Imagen_x0020_3"
src="http://reaxion.solutions/imgs_correo/correo.jpg"
            alt="mail2" class="" width="18" height="15" border="0">: </span><a
          href="mailto:soporte@reaxion.com.ar"><span
style="font-size:8.0pt;font-family:&quot;Tahoma&quot;,&quot;sans-serif&quot;">soporte@reaxion.com.ar</span></a><o:p></o:p></p>
      <p class="MsoNormal"><span
style="font-size:8.0pt;font-family:&quot;Tahoma&quot;,&quot;sans-serif&quot;"><img
            id="Imagen_x0020_4"
src="http://reaxion.solutions/imgs_correo/telefono.jpg"
            alt="telefono2" class="" width="18" height="15" border="0">:
          +54 2656 473 149 / +54 11 5263 2464 (L&iacute;neas Rotativas)</span><o:p></o:p></p>
</div>
    </div>
  </body>
</html>
';   
     
}

}
?>