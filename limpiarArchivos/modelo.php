<?php

require("vista.php");

class modelo extends vista
{

    private $db;
    private $db_sql;
    private $db_sql_origen;

    public function __construct()
    {
        /* String de conexion con la base de datos */
        //require_once("../ConectarM.php");
        //$this->db = Conectar::conexion();

        require_once("../Conectar.php");

        $this->db_sql = Conectar_SQL::conexion();

        require_once("../ConectarBase.php");

        $this->db_sql_origen = Conectar_SQL_static::conexion_origen();
    }

  

   
    //Funciones de limpieza de archivos
    public function limpiarPendientesDeReproceso($base)
    {
       
        
        $query = $this->db_sql;
        /* Creo la query que va a generar el ingreso de los nuevos archivos para ser leídos */
        $ingreso_valor_bd = "EXEC RXN_LIMPIO_ARCHIVOS '$base'";
        $ingreso_valor = $query->prepare($ingreso_valor_bd);
        $ingreso_valor->execute();
        $ingreso_valor->closeCursor();

        //echo "<script>alert('funcionó el botón');</script>";
    }

    public function limpiarTodo($base)
    {
        $query = $this->db_sql;
        /* Creo la query que va a generar el ingreso de los nuevos archivos para ser leídos */
        $ingreso_valor_bd = "EXEC RXN_BORRAR_PENDIENTES '$base'";
        $ingreso_valor = $query->prepare($ingreso_valor_bd);
        $ingreso_valor->execute();
        $ingreso_valor->closeCursor();
       
        //echo "<script>alert('funcionó el botón');</script>";
    }


 public function traerBase()
    {

      $consulta = $this->db_sql->query("SELECT BASE_DE_DATOS FROM LADY_WAY_SRL.dbo.RXN_PARAMETROS");

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        $consulta->closeCursor();
        return $fila['BASE_DE_DATOS'];
    }



   
}