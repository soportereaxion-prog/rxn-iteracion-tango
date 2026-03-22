


<?php



class Conectar_SQL
{
	/*Para el ingreso a BD*/
	public static $RUTA_ARCHIVOS_BD = "E:\plataformasWeb\wamp2.5\www\PDF\.";
	/*Para correo*/

	public static $RUTA_ARCHIVOS_MAIL = "E:\plataformasWeb\wamp2.5\www\PDF\\";

	/**/

	public static function conexion()
	{

		try {

			$dsn = 'sqlsrv:server=192.168.10.10\SQLEXPRESS2019;database=DiccionarioCharly;TrustServerCertificate=yes';
			$user = 'Axoft';
			$password = 'Axoft';

			$conexion = new PDO($dsn, $user, $password);

			$conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);



			$GLOBALS['nombre_server_usado'] = 'SVRRXN';
		} catch (Exception $e) {

			die("Error " . $e->getMessage());

			echo "Línea del error " . $e->getLine();
		}
		return $conexion;
	}
}

?>