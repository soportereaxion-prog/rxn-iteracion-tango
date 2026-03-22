<?php



require("vista.php");


class modelo extends vista
{

    private $db;
    private $db_sql;
    private $db_sql_origen;
    private $db_sql_din;
    public $base_seleccionada;
    public function __construct()
    {
        /* String de conexion con la base de datos */
        //require_once("../ConectarM.php");
        //$this->db = Conectar::conexion();

        require_once("../Conectar.php");

        $this->db_sql = Conectar_SQL::conexion();

        require_once("../ConectarBase.php");

        $this->base_seleccionada = 'LADY_WAY_SRL';
    }

    /* Selecciono el talonario para filtrar */



    public function traerBase()
    {

        $consulta = $this->db_sql->query("SELECT BASE_DE_DATOS FROM LADY_WAY_SRL.dbo.RXN_PARAMETROS");

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        $consulta->closeCursor();
        return $fila['BASE_DE_DATOS'];
    }


    public $empresa;
    public function selec_empresa()
    {
        //echo '<br>soy el metodo<br>';
        $consulta = $this->db_sql->query("SELECT NombreBD FROM EMPRESA");
        while ($filas = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $this->empresa[] = $filas;
        }
        return $this->empresa;
        $consulta->closeCursor();
    }

    public $factura;
    public function selec_Factura($nombreEmpresa, $fechaDesde, $fechaHasta)
    {

        $this->db_sql_din = Conectar_SQL::conexion($_POST['selectEmpresa']);
        $fechaDesde = DateTime::createFromFormat('d/m/Y', $_POST['fech1'])->format('Ymd');
        $fechaHasta = DateTime::createFromFormat('d/m/Y', $_POST['fech2'])->format('Ymd');


        //$consulta = $this->db_sql->query("SELECT TOP(10) N_COMP FROM".$nombreEmpresa."[RXN_GVA12]");';
        $consulta = $this->db_sql_din->query("SELECT  N_COMP FROM " . $nombreEmpresa . ".dbo.[GVA12] WHERE T_COMP = 'FAC' AND TALONARIO IN (5,15) AND FECHA_EMIS BETWEEN '$fechaDesde' AND '$fechaHasta'");

        while ($filas = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $this->factura[] = $filas;
        }
        return $this->factura;
        $consulta->closeCursor();
    }


    //FUNCION EN DESUSO.
    public function selec_factur()
    {

        $consulta = $this->db_sql_origen->query("SELECT N_COMP FROM GVA12 WHERE T_COMP = 'FAC'");

        while ($filas = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $this->factura[] = $filas;
        }
        return $this->factura;
        $consulta->closeCursor();
    }




    public function existeFactura($factura)
    {
        /*Se deberia traer la base original*/
        $consulta = $this->db_sql->query("SELECT N_COMP FROM " . $this->base_seleccionada . ".dbo.[GVA12] WHERE N_COMP = '$factura'");

        // Verificamos si la consulta devuelve alguna fila
        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        // Si $filas tiene un valor, la factura existe
        return $filas ? true : false;
        //$filas = $consulta->fetch(PDO::FETCH_ASSOC);

        //return $this->talon_ped = $filas['NombreBD'];
        $consulta->closeCursor();
    }



    public function actualizoRutaXml($ruta_xml, $api_tiendas, $api_local, $id_empresa, $ruta_local, $facB, $facEcommerce, $facE)
    {
        //Conecto con la base de datos
        $query = $this->db_sql;

        /* Creo la query que va a generar el ingreso de los nuevos archivos para ser leídos */
        $ingreso_valor_bd = "UPDATE RXN_PARAMETROS SET RUTAXML = '$ruta_xml', API_TIENDAS = '$api_tiendas', API_LOCAL = '$api_local', ID_EMPRESA = '$id_empresa', RUTA_LOCAL = '$ruta_local', FAC_B = '$facB', FAC_ECOMMERCE = '$facEcommerce', FAC_E_EXPO = '$facE'";
        $ingreso_valor = $query->prepare($ingreso_valor_bd);
        $ingreso_valor->execute();
        $ingreso_valor->closeCursor();
    }

    /* Actualizo el XML ingresado */




    public function ingreso_factura($empresa, $n_comp)
    {
        //Conecto con la base de datos

        $query = $this->db_sql;

        /* Creo la query que va a generar el ingreso de los nuevos archivos para ser leídos */
        $ingreso_valor_bd = "EXEC RXN_COPIO_FACTURA '$empresa', '$n_comp',$this->base_seleccionada";
        /*         echo '<br>Valor'.$ingreso_valor_bd; */
        $ingreso_valor = $query->prepare($ingreso_valor_bd);
        $ingreso_valor->execute();
        $ingreso_valor->closeCursor();
    }
}
