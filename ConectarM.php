<?php

class Conectar
{

	public static function conexion()
	{

		try {

			$dsn = 'mysql:host=localhost; dbname=rxn_resto';
			$user = 'root';
			$password = '';

			$conexion = new PDO($dsn, $user, $password);

			$conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			//$conexion->exec("SET CHARACTER SET UTF8");

		} catch (Exception $e) {

			die("Error " . $e->getMessage());

			echo "Línea del error " . $e->getLine();
		}
		return $conexion;
	}
}
