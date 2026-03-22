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
<title>Re@xion - XML Inyección</title>
<!--<link href="styles.css" rel="stylesheet" type="text/css" /> <meta http-equiv="refresh" content="5"/>-->
<?php
//error_reporting(0);
?>
</head>
	<body link="#000000" alink="#000000" vlink="#000000">
<fieldset><legend>Lectura de CSV</legend>

<?php

//$xml = simplexml_load_file("017-php-simplexml02.xml");
//Busco el archivo para poder leerlo.
//$xml = simplexml_load_file("30475-64.PRA");
//
///*Recorro el XML de acuerdo a sus etiquetas*/
//foreach ($xml->cabecera as $nodo) 
//	{
//	echo $nodo->cliente.'<br>';
//	}
//foreach ($xml->cuerpo as $nodo) 
//	{
//	echo $nodo->descripcion.' Cantidad: '.$nodo->cantidad.' Precio: '.$nodo->precio.' Total: '.$nodo->total.'<br>';
//	}



//Leo los archivos del directorio
//$ruta_de_la_carpeta = "/RXNAPP/3.2/www/rxnAlovero/xml/rutaxml";
//if ($handler = opendir($ruta_de_la_carpeta)) {
//    while (false !== ($file = readdir($handler))) {
//            echo "$file<br>";
//    }
//    closedir($handler);
//}

$modelo->leo_ingreso_directorio_csv();
        
//$modelo->muestroNombreArchivo();

/*Proceso los archivos que sean de clientes pendientes*/
$modelo->procesoCsvClientes();
/*Proceso archivo de artículos*/
//$modelo->procesoCsvArticulos();

/*Proceso pedidos*/
//$modelo->procesoPedidos();

?>
    
    
    
</fieldset>
</body>
</html>