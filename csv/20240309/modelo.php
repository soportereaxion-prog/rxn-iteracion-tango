<?php

require("vista.php");

class modelo extends vista {

    private $db;
    private $db_sql;
    public $token_api;
    public $token_api_local;
    public $id_empresa;

    public function __construct() {
        /* String de conexion con la base de datos */
        //require_once("../ConectarM.php");
        //$this->db = Conectar::conexion();

        require_once("../Conectar.php");

        $this->db_sql = Conectar_SQL::conexion();

        /* Llamo al método que devuelve el arrays con los tokens */
        $this->devuelvoTokens();

        $this->token_api = $this->token['API_TIENDAS'];
        //$this->token_api = 'cf7d4feb-3fce-424d-9a7a-b2a6cf1b0208_14355';

        $this->token_api_local = $this->token['API_LOCAL'];
        //$this->token_api_local = 'c894f9cf-9078-4cce-b296-888b7439e390';

        $this->id_empresa = $this->token['ID_EMPRESA'];
        //$this->id_empresa = '282';
    }

    /* Selecciono el talonario para filtrar */

    public $talonario;

    public function selec_talonario() {
        $consulta = $this->db_sql->query("SELECT GVA43.TALONARIO, GVA43.DESCRIP FROM GVA43 INNER JOIN RXN_PARAMETROS ON GVA43.TALONARIO = RXN_PARAMETROS.TALON_PED WHERE GVA43.COMPROB = 'PED'");
        while ($filas = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $this->talonario[] = $filas;
        }
        return $this->talonario;
        $consulta->closeCursor();
    }

    public function leo_ingreso_directorio_csv() {

        //Conecto con la base de datos
        $query = $this->db_sql;

        //Leo los archivos del directorio
        $ruta_de_la_carpeta = $this->leoParametroBd('RUTAXML') . '\\';
        //echo 'Ruta:'.$this->leoParametroBd('RUTAXML').'\\';
        if ($handler = opendir($ruta_de_la_carpeta)) {
            //echo "Archivos procesados: <br>";
            while (false !== ($file = readdir($handler))) {
                if (substr($file, -4) === ".csv") {
                    /* Creo la query que va a generar el ingreso de los nuevos archivos para ser leídos */
                    $ingreso_nombre_archivo = "IF NOT EXISTS (SELECT NOMBRE_ARCHIVO FROM [RXN_CSV] WHERE NOMBRE_ARCHIVO = '$file')
                                BEGIN INSERT INTO [dbo].[RXN_CSV]
                                          ([NOMBRE_ARCHIVO]
                                          ,[ESTADO]
                                          ,[FECHA])
                                    VALUES
                                          (:NOMBRE_ARCHIVO
                                          ,'I'
                                          ,getdate()) END";
                    $ingreso_nombre_bd = $query->prepare($ingreso_nombre_archivo);
                    $ingreso_nombre_bd->execute(array(":NOMBRE_ARCHIVO" => $file));
                    $ingreso_nombre_bd->closeCursor();
                }
                //echo "$file.<br>";
            }
            closedir($handler);
        }
    }

    /* Leo los archivos para mostrarlo en la pantalla principal */

    public function leoArchivosBd() {

        $consulta = $this->db_sql->query("SELECT NOMBRE_ARCHIVO FROM RXN_CSV WHERE ESTADO = 'I'");

        while ($filas = $consulta->fetch(PDO::FETCH_ASSOC)) {

            $this->nombre_archivo_ini[] = $filas;
        }

        if (isset($this->nombre_archivo_ini)) {
            return $this->nombre_archivo_ini;
        }

        $consulta->closeCursor();
    }

    /* Busco todos los archivos leídos en la base de datos para luego recorrerlos */

    public function leoArchivosBdCli() {

        $consulta = $this->db_sql->query("SELECT NOMBRE_ARCHIVO FROM RXN_CSV WHERE NOMBRE_ARCHIVO NOT IN ('.','..') AND NOMBRE_ARCHIVO LIKE '%.csv%' AND ESTADO = 'I' AND NOMBRE_ARCHIVO LIKE 'CLI%'");

        while ($filas = $consulta->fetch(PDO::FETCH_ASSOC)) {

            $this->nombre_archivo[] = $filas;
        }

        if (isset($this->nombre_archivo)) {
            return $this->nombre_archivo;
        }

        $consulta->closeCursor();
    }

    /* Leo el archivo de artículos */

    public function leoArchivosBdArt() {

        $consulta = $this->db_sql->query("SELECT NOMBRE_ARCHIVO FROM RXN_CSV WHERE NOMBRE_ARCHIVO NOT IN ('.','..') AND NOMBRE_ARCHIVO LIKE '%.csv%' AND ESTADO = 'I' AND NOMBRE_ARCHIVO LIKE 'ARTS%'");

        while ($filas = $consulta->fetch(PDO::FETCH_ASSOC)) {

            $this->nombre_archivo_art[] = $filas;
        }
        /* Controlo que haya un nombre de archivo que sea devuelto para pasar el array */
        if (isset($this->nombre_archivo_art)) {
            return $this->nombre_archivo_art;
        }

        $consulta->closeCursor();
    }

    /* Leo el archivo de encabezados de pedidos */

    public function leoArchivosBdEncPed() {

        $consulta = $this->db_sql->query("SELECT NOMBRE_ARCHIVO FROM RXN_CSV WHERE NOMBRE_ARCHIVO NOT IN ('.','..') AND NOMBRE_ARCHIVO LIKE '%.csv%' AND ESTADO = 'I' AND NOMBRE_ARCHIVO LIKE 'C20%'");

        while ($filas = $consulta->fetch(PDO::FETCH_ASSOC)) {

            $this->nombre_archivo_enc_pedidos[] = $filas;
        }

        if (isset($this->nombre_archivo_enc_pedidos)) {
            return $this->nombre_archivo_enc_pedidos;
        }

        $consulta->closeCursor();
    }

    /* Leo el archivo del cuerpo del pedido */

    public function leoArchivosBdCuePed($nombre_archivo) {
        unset($this->nombre_archivo_cue_pedidos);
        $consulta = $this->db_sql->query("SELECT NOMBRE_ARCHIVO FROM RXN_CSV WHERE NOMBRE_ARCHIVO LIKE 'D20%' AND NOMBRE_ARCHIVO = 'D' + SUBSTRING('$nombre_archivo',2,200)");
        //$consulta = $this->db_sql->query("SELECT NOMBRE_ARCHIVO FROM RXN_CSV WHERE NOMBRE_ARCHIVO NOT IN ('.','..') AND NOMBRE_ARCHIVO LIKE '%.csv%' AND ESTADO = 'I' AND NOMBRE_ARCHIVO LIKE 'D20%' AND NOMBRE_ARCHIVO LIKE (SELECT TOP(1)'%' + SUBSTRING(NOMBRE_ARCHIVO, 12, 3) + '%' AS NOMBRE_RECORTADO FROM RXN_CSV WHERE NOMBRE_ARCHIVO NOT IN ('.','..') AND NOMBRE_ARCHIVO LIKE '%.csv%' AND ESTADO = 'I' AND NOMBRE_ARCHIVO LIKE 'C20%')");
        while ($filas = $consulta->fetch(PDO::FETCH_ASSOC)) {

            $this->nombre_archivo_cue_pedidos[] = $filas;
        }

        return $this->nombre_archivo_cue_pedidos;


        $consulta->closeCursor();
    }

    /* Devuelvo el valor del talonario seleccionado */

    public function devuelvoValorTalonSelec() {
        $consulta = $this->db_sql->query("SELECT TALON_PED FROM RXN_PARAMETROS");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        return $this->talon_ped = $filas['TALON_PED'];


        $consulta->closeCursor();
    }

    /* Busco los datos de Token */

    public function devuelvoTokens() {
        $consulta = $this->db_sql->query("SELECT * FROM RXN_PARAMETROS");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        return $this->token = $filas;


        $consulta->closeCursor();
    }

    /* Devuelvo el último ID de GVA14 para llevarlo a los clientes */

    public function maxIdGva14() {
        $consulta = $this->db_sql->query("SELECT TOP(1) CASE WHEN COD_CLIENT IS NULL THEN 1 ELSE COD_CLIENT END AS COD_CLIENT FROM GVA14 ORDER BY ID_GVA14 DESC");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        return $this->maxIdGva14 = $filas;


        $consulta->closeCursor();
    }

    /* Devuelvo todos los valores del GVA14 en princpio para controlar los CUIT de los clientes */

    public function selectGva14($cuit, $tipo_doc) {
        $consulta = $this->db_sql->query("SELECT TOP(1)* FROM GVA14 WHERE CUIT = '$cuit' AND TIPO_DOC = $tipo_doc");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        return $this->selectGva14 = $filas;


        $consulta->closeCursor();
    }

    /* Vacío tabla de clientes para el momento del ingreso y en su facturación poder cargar la configuración de los artículos */

    public function vacioClientes() {
        $consulta = $this->db_sql->query("TRUNCATE TABLE RXN_IMP_CLI");

        $consulta->closeCursor();
    }

    /* Controlo el número de pedido en la BD para no re-procesar en caso de existir por tema re-ingreso de pedidos por error en cliente de CSV */

    public function ctrlPedi($nro_pedido) {
        $consulta = $this->db_sql->query("SELECT TOP(1)* FROM RXN_API_CTRL WHERE COD_COMP = '$nro_pedido' AND GRABO = 1 ORDER BY ID DESC");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        return $this->ctrlPediRxnApiCtrl = $filas;


        $consulta->closeCursor();
    }

    /* Método que solo devuelve el pedido con error */

    public function ctrlPediError($nro_pedido) {
        $consulta = $this->db_sql->query("SELECT TOP(1)* FROM RXN_API_CTRL WHERE COD_COMP = '$nro_pedido' AND GRABO = 0 AND PROCESO NOT IN ('ARTICULOS','CLIENTES') ORDER BY ID DESC");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        return $this->ctrlPediRxnApiCtrl = $filas;


        $consulta->closeCursor();
    }

    /* Recorro los pedidos no procesados para re-intentar */

    public function ctrlNoProcesados() {
        $consulta = $this->db_sql->query("SELECT * FROM RXN_API_CTRL WHERE GRABO = 0 AND PROCESO NOT IN ('ARTICULOS','CLIENTES') ORDER BY ID DESC");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        return $this->ctrlNoProcesados = $filas;


        $consulta->closeCursor();
    }

    /* Llamo a la ruta XML configurada */

    public function rutaXmlConfigurada() {
        $consulta = $this->db_sql->query("SELECT RUTAXML FROM RXN_PARAMETROS");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        return $this->ruta_xml = $filas['RUTAXML'];


        $consulta->closeCursor();
    }

    /* Ingreso el pedido a la tabla de control */

    public function ingresoPedidoControl($pedido, $cliente) {
        $query = $this->db_sql;
        $ingreso = "INSERT INTO [dbo].[RXN_PEDIDOS_INGRESADOS]
           ([NRO_PEDIDO]
           ,[CLIENTE]
           ,[FECHA])
            VALUES(
            :PEDIDO,
            :CLIENTE,
            getdate()
            )
            ";

        $consulta = $query->prepare($ingreso);
        $consulta->execute(array(":PEDIDO" => $pedido, ":CLIENTE" => $cliente));
        //$filas = $consulta->fetch(PDO::FETCH_ASSOC);
        $consulta->closeCursor();
    }

    /* Lleno la tabla de clientes */

    public function ingresoClieAlic($cliente, $id_alicuota, $alic_perc) {
        $query = $this->db_sql;
        $ingreso = "
            INSERT INTO [dbo].[RXN_IMP_CLI]
           ([COD_CLIENT]
           ,[ID_CAT]
           ,[ALIC_PERC]
           ,[FECHA])
            VALUES(
            :COD_CLIENT,
            :ID_CAT,
            :ALIC_PERC,
            getdate()
            )
            ";

        $consulta = $query->prepare($ingreso);
        $consulta->execute(array(":COD_CLIENT" => $cliente, ":ID_CAT" => $id_alicuota, ":ALIC_PERC" => $alic_perc));
        //$filas = $consulta->fetch(PDO::FETCH_ASSOC);
        $consulta->closeCursor();
    }

    /* Ingreso el pedido a la tabla de control */

    public function ingresoMensajesApi($cod_comp, $proceso, $mensaje, $grabo, $cod_client, $nombre_archivo, $reintento) {
        $query = $this->db_sql;
        $ingreso = "INSERT INTO [dbo].[RXN_API_CTRL]
           ([COD_COMP]
           ,[PROCESO]
           ,[MENSAJE_API]
           ,[FECHA],
           [GRABO],
           [COD_CLIENT],
           [NOMBRE_ARCHIVO],
           [REINTENTOS])
           VALUES(
           :COD_COMP,
           :PROCESO,
           :MENSAJE,
           getdate(),
           :GRABO,
           :COD_CLIENT,
           :NOMBRE_ARCHIVO,
           :REINTENTOS
           )";

        $consulta = $query->prepare($ingreso);
        $consulta->execute(array(":COD_COMP" => $cod_comp, ":PROCESO" => $proceso, ":MENSAJE" => $mensaje, ":GRABO" => $grabo, ":COD_CLIENT" => $cod_client, ":NOMBRE_ARCHIVO" => $nombre_archivo, ":REINTENTOS" => $reintento));
        //$filas = $consulta->fetch(PDO::FETCH_ASSOC);
        $consulta->closeCursor();
    }

    /* Actualizo el archivo de cliente importado */

    public function actualizoArchivoArticuloImpo() {
        $query = $this->db_sql;
        $update = "UPDATE RXN_CSV SET ESTADO = 'P' WHERE NOMBRE_ARCHIVO LIKE 'ARTS%'";
        $consulta = $query->prepare($update);
        $consulta->execute();
        $consulta->closeCursor();
    }

    /* Actualizo la información de los clientes ya procesados */

    public function actualizoArchivoClientesImpo() {
        $query = $this->db_sql;
        $update = "UPDATE RXN_CSV SET ESTADO = 'P' WHERE NOMBRE_ARCHIVO LIKE 'CLI%'";
        $consulta = $query->prepare($update);
        $consulta->execute();
        $consulta->closeCursor();
    }

    public function actualizaPedidos() {
        $query = $this->db_sql;
        $update = "UPDATE RXN_CSV SET ESTADO = 'P' WHERE NOMBRE_ARCHIVO LIKE 'D20%' OR NOMBRE_ARCHIVO LIKE 'C20%'";
        $consulta = $query->prepare($update);
        $consulta->execute();
        $consulta->closeCursor();
    }

    /* Devuelvo el parámetro buscado según corresponda */

    public function leoParametroBd($nombre_col) {
        $consulta = $this->db_sql->query("SELECT $nombre_col FROM RXN_PARAMETROS");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        return $this->param = $filas[$nombre_col];


        $consulta->closeCursor();
    }

    /* Controlo que los artículos existan en la base */

    public function ctrlArtsBase($articulo) {
        $consulta = $this->db_sql->query("SELECT TOP(1)* FROM STA11 WHERE COD_ARTICU = '$articulo'");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        return $this->ctrl_articu = $filas;


        $consulta->closeCursor();
    }

    /* Traigo el último ID para colocar en la API */

    public function devuelvoIdPedido() {
        $consulta = $this->db_sql->query("SELECT MAX(ID) AS ID FROM RXN_PEDIDOS_INGRESADOS");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        return $this->id_orden = $filas['ID'];


        $consulta->closeCursor();
    }

    public $cli_csv;

    public function clientesCsv() {
        $this->leoArchivosBdCli();
        if (isset($this->nombre_archivo)) {
            foreach ($this->nombre_archivo as $archivo) {
                /* Recorro las iteraciones para ingresar a pantalla */
                $archivo2 = fopen($this->leoParametroBd('RUTAXML') . '\\' . $archivo['NOMBRE_ARCHIVO'], "r");
                //echo 'Ruta:' . $this->leoParametroBd('RUTAXML') . '\\' . $archivo['NOMBRE_ARCHIVO'] . '<br>';
                while (($datos = fgetcsv($archivo2, ",")) == true) {

                    $array_nombre_archivo = array($archivo['NOMBRE_ARCHIVO']);

                    /* Creo un array con los datos del DNI del cliente para pasarlos a la variable */
                    $dni[] = array_merge($datos, $array_nombre_archivo);
                    /* Completo un array para luego compararlos con los códigos de clientes */
                    $this->cli_csv = $dni;
                }
                fclose($archivo2);
            }
        } else {
            //echo "No hay artículos para procesar<br>";
            $this->ingresoMensajesApi('SIN', 'CLIENTES', 'NO-HAY-ARCHIVOS-PARA-PROCESAR', 0, '', '', 0);
        }
    }

    public $enc_pedi_csv;

    //public $archivo_procesado_csv;

    /* Guardo en arrray los datos del CSV de encabezados de pedidos */

    public function encPedidos() {
        $this->leoArchivosBdEncPed();
        if (isset($this->nombre_archivo_enc_pedidos)) {
            foreach ($this->nombre_archivo_enc_pedidos as $archivo) {
                /* Recorro las iteraciones para ingresar a pantalla */
                $archivo2 = fopen($this->leoParametroBd('RUTAXML') . '\\' . $archivo['NOMBRE_ARCHIVO'], "r");

                while (($datos = fgetcsv($archivo2, ",")) == true) {
                    //$num = count($datos);

                    $array_nombre_archivo = array($archivo['NOMBRE_ARCHIVO']);

                    /* Creo un array con los datos del DNI del cliente para pasarlos a la variable */
                    $enc_pedi[] = array_merge($datos, $array_nombre_archivo);
                    /* Completo un array para luego compararlos con los códigos de clientes */
                    $this->enc_pedi_csv = $enc_pedi;
                }

                //$this->archivo_procesado_csv = $archivo['NOMBRE_ARCHIVO'];
                fclose($archivo2);
            }
        }
    }

    public $cue_pedi_csv;

    public function cuePedidos($nombre_archivo) {

        $this->leoArchivosBdCuePed($nombre_archivo);
        //echo $nombre_archivo.'-->NombreArchivo dentro del método';
        foreach ($this->nombre_archivo_cue_pedidos as $archivo) {
            unset($this->cue_pedi_csv);
            /* Recorro las iteraciones para ingresar a pantalla */

            $archivo = fopen($this->leoParametroBd('RUTAXML') . '\\' . $archivo['NOMBRE_ARCHIVO'], "r");
            //echo 'Ruta:' . $this->leoParametroBd('RUTAXML') . '\\' . $archivo['NOMBRE_ARCHIVO'] . '<br>';
            while (($datos = fgetcsv($archivo, ",")) == true) {
                //$num = count($datos);
                /* Creo un array con los datos del DNI del cliente para pasarlos a la variable */
                $cue_pedi[] = $datos;
                /* Completo un array para luego compararlos con los códigos de clientes */
                $this->cue_pedi_csv = $cue_pedi;

                //echo $archivo['NOMBRE_ARCHIVO'].'<- este es el valor que no se imprime';
            }
        }
        fclose($archivo);
    }

    public $dato_pedi_cue;

    public function procesoPedidos() {
        $this->encPedidos();

        $csv_enc = array();

        if (isset($this->enc_pedi_csv)) {
            /* Correspondiente al cuerpo del pedido */
            /* Saco la primera línea del CSV que no se importa */
            foreach ($this->enc_pedi_csv as $ctrl_enc_pedi) {
                if ($ctrl_enc_pedi['0'] != 'orden') {
                    $csv_enc[] = $ctrl_enc_pedi;
                }
            }

            foreach ($csv_enc as $valor_cue_csv) {
                $dato_pedi_enc[] = array("N_COMP" => $valor_cue_csv[1], "COD_CLIENT" => $valor_cue_csv[3], "IMPORTE" => $valor_cue_csv[5], "COD_ZONA" => $valor_cue_csv[18], "BONIFCOSME" => $valor_cue_csv[13], "PRACTICOSAS" => $valor_cue_csv[14], "GASTADMIN" => $valor_cue_csv[11], "IMPORTE_GRAVADO" => $valor_cue_csv[7], "FECHA" => $valor_cue_csv[2], "IMP_IVA" => $valor_cue_csv[9], "ORDEN" =>$valor_cue_csv[0], "NOMBRE_ARCHIVO" => $valor_cue_csv[19]);
            }

            /*Busco el nombre de archivo una sola vez para eliminar búsquedas innecesarias*/
            //Traigo el método que lee los archivos del cuerpo


            /* Recorro el encabezado del pedido */
            foreach ($dato_pedi_enc as $pedi_enc) {
                //unset()
                unset($this->cue_pedi_csv);
                //echo $pedi_enc['NOMBRE_ARCHIVO'].'-> Nombre Achivo <br>';

                /* Busco el archivo en cuestión para poder procesar solo el correspondiente al búcle */
                $this->cuePedidos($pedi_enc['NOMBRE_ARCHIVO']);
                unset($csv_cue);
                $this->dato_pedi_cue = array();
                //$csv_cue = array();
                /* Correspondiente al cuerpo del pedido */
                foreach ($this->cue_pedi_csv as $ctrl_cue_pedi) {
                    if ($ctrl_cue_pedi['0'] != 'orden') {
                        $csv_cue[] = $ctrl_cue_pedi;
                    }
                }


                /* Transformación de array de lectura de CSV */
                foreach ($csv_cue as $valor_cue_csv) {
                    $this->dato_pedi_cue[] = array("N_COMP" => $valor_cue_csv[1], "COD_ARTICU" => $valor_cue_csv[3], "CANT" => $valor_cue_csv[4], "PRECIO" => $valor_cue_csv[9], "REVEND" => $valor_cue_csv[12], "IMPORTE_NETO" => $valor_cue_csv[5], "TOTAL_RENGLON" => $valor_cue_csv[10], "PRECIO_NETO" => $valor_cue_csv[6], "ORDEN" => $valor_cue_csv[0]);
                }

                /* Controlo si existe el pedido en la base, si existe no se ingresará */
                $this->ctrlPedi($pedi_enc['N_COMP']);
                /* Si no existe en la tabla de control entonces se ingresa */
                if ($this->ctrlPediRxnApiCtrl['COD_COMP'] == '') {

                    /* Correspondiente al cuerpo del pedido */
                    $this->buscoPedido($pedi_enc['N_COMP'], $pedi_enc['COD_CLIENT'], $pedi_enc['ORDEN']);

                    $this->ingresoFactura($pedi_enc['COD_CLIENT'], $pedi_enc['IMPORTE'], implode($this->articulos), $pedi_enc['N_COMP'], $pedi_enc['COD_ZONA'], $pedi_enc['IMPORTE_GRAVADO'], $pedi_enc['FECHA'], $pedi_enc['BONIFCOSME'], $pedi_enc['PRACTICOSAS'], $pedi_enc['GASTADMIN'], $pedi_enc['IMP_IVA']);


                    if (!isset($this->mensaje_api['savedId'])) {
                        $id = 0;
                        $mensaje = 'nulo';
                    } else {
                        $id = $this->mensaje_api['savedId'];
                        //$mensaje = $this->mensaje_api['messages'];
                    }
                    /* Función interna para poder obtener el mensaje de error */
                    $stringConvertido = $this->convertirATexto($this->mensaje_api);

                    if ($this->mensaje_api['Succeeded'] == '') {
                        $grabo = 0;
                    } else {
                        $grabo = 1;
                    }

                    $mensaje_api = 'ID_INTERNO: ' . $id . ' Mensaje: ' . $stringConvertido . ' ¿Grabó?: ' . $this->mensaje_api['Succeeded'];

                    $this->ingresoMensajesApi($pedi_enc['N_COMP'], 'PEDIDOS', $mensaje_api, 0, $pedi_enc['COD_CLIENT'], $pedi_enc['NOMBRE_ARCHIVO'], 0);
                    //$this->ingresoMensajesApi($pedi_enc['N_COMP'], 'PEDIDOS', $mensaje_api, $grabo, $pedi_enc['COD_CLIENT'], $pedi_enc['NOMBRE_ARCHIVO'], 0);

                    echo $mensaje_api . '<br>';
                    unset($csv_cue);
                    unset($this->dato_pedi_cue);
                } else {
                    /* Al existir ingreso mensaje en la tabla no ingreso para procesar siempre el mismo pedido */
                    $this->ingresoMensajesApi($pedi_enc['N_COMP'], 'PEDIDOS', 'EL Pedido ya existe', 1, $pedi_enc['COD_CLIENT'], $pedi_enc['NOMBRE_ARCHIVO'], 0);
                    echo 'El pedido: ' . $pedi_enc['N_COMP'] . ' para el cliente: ' . $pedi_enc['COD_CLIENT'] . ' ya existe.<br>';
                }
            }

            //$this->actualizaPedidos();
        } else {
            echo "No hay archivos de pedidos para procesar";
            $this->ingresoMensajesApi('SIN', 'PEDIDOS', 'NO-HAY-ARCHIVOS-PARA-PROCESAR', 0, '', '', 0);
        }
    }

    /* Formateo los valores de importes sin redondear. */

    function formatearNumero($numero) {
        // Utilizar sprintf sin redondeo para completar los decimales con ceros
        $numeroConDecimales = sprintf("%.2f", $numero);

        // Utilizar number_format para agregar la coma y formatear el resultado sin redondeo
        $numeroFormateado = number_format($numeroConDecimales, 2, '.', '');

        // Eliminar la coma del resultado
        $numeroFinal = str_replace(',', '', $numeroFormateado);

        return $numeroFinal;
    }

    /* Función para buscar el pedido y devolver los artículos */

    public function buscoPedido($pedido, $cliente, $orden_x) {

        /* Correspondiente al cuerpo del pedido */
        $busco = $pedido;
        $contar = 0;
        echo 'Pedido: '.$pedido . ' Orden: ' .$orden_x.'<br>';
        $this->tot_para_ped = 0;
        /* Prueba para armar el string de artículos */
        foreach ($this->dato_pedi_cue as $articu) {

            $contar = ++$contar;
            //$articulos[] = $articu;
            /* Saco el código de artículo para que pueda ingresar correctamente a la base y matchear con los originales. */
            //$articu_formato = substr($articu['COD_ARTICU'], 2, 6);
            /* Busco la descripción del artículo */
            $search = array('/', '-');
            $replace = "";
            $articu_ini = substr($articu['COD_ARTICU'], 1);
            $subject = $articu_ini;
            $articu_formato = substr(str_replace($search, $replace, $subject), 0, -1);


            //echo 'Con formato: ' . $articu_formato . 'Original ->' . $articu['COD_ARTICU'] . '->' . ++$contar . '<br>';
            $this->ctrlArtsBase($articu_formato);
            /* Controlo si el artículo está en la base */
            //echo $articu['COD_ARTICU'] . '<br>';

            if ($articu['N_COMP'] == $busco AND $articu['ORDEN'] == $orden_x) {
                $nro_pedido = $articu['N_COMP'];
                if ($contar > 0) {

                    if ($this->ctrl_articu['COD_ARTICU'] == '') {
                        $descripcio = 'NO EXISTE EL ARTICULO EN LA BASE';
                    } else {
                        /* Controlo si el comprobante es expo para agregar el revend */
                        $pv_pedi = substr($nro_pedido, 0, 6);

                        if ($pv_pedi == 'E00011') {
                            $completo_con_50 = str_pad($this->ctrl_articu['DESCRIPCIO'] . '' . $articu['COD_ARTICU'], 50, ' ');
                            $descripcio = $completo_con_50 + $articu['REVEND'];
                        } else {
                            $completo_con_30 = str_pad($this->ctrl_articu['DESCRIPCIO'], 30, ' ');
                            $descripcio = $this->ctrl_articu['DESCRIPCIO'] . '' . $articu['COD_ARTICU'];
                            //$descripcio = $this->ctrl_articu['DESCRIPCIO'] . ' ' . $this->ctrl_articu['DESC_ADIC'];
                        }
                    }

                    /* Busco el cliente para poder condicionar las vearialbes que componen el json */
                    $this->busco_cliente($cliente);

                    $cant_x_precio_neto = ($articu['TOTAL_RENGLON'] / 1.21);
                    $precio = $articu['TOTAL_RENGLON'];
                    $precio_art = $articu['PRECIO'];

                    /* Traido del cliente */
                    $p_porc = round($this->tabla_cliente_cod_cliente['PORCENTAJE'], 2);
                    //$p_porc = 0.00;
                    $p_base = $cant_x_precio_neto;
                    //$p_base = 0;
                    $p_importe = $p_base * ($p_porc / 100);
                    //$p_importe = 0;

                    $p_importe_array[] = $p_importe;
                    $this->p_imp_importe = $p_importe_array;

                    /* Completo el IVA en el caso que el cliente sea CF */
                    if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'CF') {
                        $importe_iva_formato = round((($precio/1.21) * 0.21),2);
                    }


                    //echo '<br>'.$importe_iva.'<-IVA | '.$p_snc_importe.' Cantidad por precio neto: '.($cant_x_precio_neto * 0.21).'<br>';

                    /* Si el cliente */
                    if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'SNC') {
                        $p_snc = 10.5;
                        $p_snc_base = $precio;
                        $p_snc_importe = ($p_snc_base * ($p_snc / 100));
                        /* Si el cliente es SNC entonces le sumo el IVA SNC */
                        $importe_iva = round(($cant_x_precio_neto * 0.21), 2) + $p_snc_importe;
                        $importe_iva_formato = round($importe_iva, 2);
                        if ($this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] != '') {
                            //Controlo si viene la 3er alicuota viene para poder calcularla
                            $alic = ',{
						"codigoAlicuota": ' . $this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] . ',
						"codigoPercepcion": "",
						"porcentaje": ' . round($p_porc, 2) . ',
						"base": ' . round($p_base, 2) . ',
						"importe": ' . round($p_importe, 2) . '
                                        	}';
                        } else {
                            $alic = '';
                        }

                        $percepciones = ',
                           "percepciones":
                                        [{
						"codigoAlicuota": 11,
						"codigoPercepcion": "",
						"porcentaje": ' . round($p_snc, 2) . ',
						"base": ' . round($p_snc_base, 2) . ',
						"importe": ' . round($p_snc_importe, 2) . '
					} ' . $alic . '
                                	]';

                        //$percepciones = $percepciones;

                        $art_tot_10_5[] = round($p_snc_importe, 2);
                        $art_perc[] = round($p_importe, 2);
                        //Tot IVA 10_5.
                        $this->art_total_10_50 = $art_tot_10_5;
                        //Tot Perc.
                        $this->art_total_perc = $art_perc;
                    }
                    if (empty($percepciones)) {
                        $percepciones = '';
                    }
                    $articulos[] = '{
				"codigo": "' . $articu_formato . '",
				"descripcion": "TU HERMANA",
				"descargaStock" : false,
				"cantidad": ' . $articu['CANT'] . ',
                                "importeIva": ' . $importe_iva_formato . ',
				"codigoDeposito": "1",
				"codigoUM": "UNI",
				"precio": ' . round($precio_art, 2) . ',
                                "importe": ' . round($precio, 2) . ',
                                "importeSinImpuestos": ' . round($cant_x_precio_neto, 2) . $percepciones . '
                        	}
                     ,';

                    /* Configuro los totales para llevarlos al ingreso de comprobantes */
                    $art_tot_iva[] = $importe_iva_formato;

                    $art_tot_precio[] = round($precio, 2);
                    $art_imp_sin_impuestos[] = round($cant_x_precio_neto, 2);

                    $tot_para_ped = $articu['TOTAL_RENGLON'];
                    $tot_2[] = $tot_para_ped;
                    $this->tot_para_ped = $tot_2;
                }
            }
            //echo '<br>' . print_r($this->art_total_precio) . '<br>';
        }

        unset($this->dato_pedi_cue);

        //echo '->'.array_sum($this->art_total_iva).'<-Soy el iva<br>';

        $this->articulos = $articulos;

        //Tot iva.
        $this->art_total_iva = $art_tot_iva;
        //Subtotal
        $this->art_total_precio = $art_tot_precio;
        //Importe sin impuestos
        $this->tot_arts_sin_impuestos = $art_imp_sin_impuestos;
        //echo '<br><br>';
        //echo '<br>' . array_sum($this->art_total_iva) . '<br>';
        //print_r($articulos);
    }

    public $mensaje_api;

    /* Ingreso pedidos por API */

    public function ingresoPedidoApi($cliente, $importe, $articulo, $nro_pedido, $cod_zona, $imp_sin_impuestos, $fecha, $bonif_cosme, $practicosas, $gastadmin) {

        $token = $this->token_api;
        //setup the request, you can also use CURLOPT_URL
        $ch = curl_init('https://tiendas.axoft.com/api/Aperture/order');

        $this->ingresoPedidoControl($nro_pedido, $cliente);


        /* IMPORTANTES LÍNEAS PARA DESACTIVAR EL USO DE CERTIFICADOS DEL SERVER
          Desactivar el uso de certificados online en el server local */
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        /* Desactivar el uso de certificados online en el server local */

        $this->devuelvoIdPedido();

        /* Busco el COD_CLIENT original */
        $this->busco_cliente($cliente);

        //echo $cliente . "<br>";

        /* Verifico el punto de venta del pedido para apuntarlo al talonario */
        //Extraigo el PV del pedido
        $pv_pedi = substr($nro_pedido, 0, 6);
        //echo $pv_pedi.'<BR>';
        if ($pv_pedi == 'B00003') {
            $talon_pedi = 10;
            $talon_fac = 5;
        }
        if ($pv_pedi == 'A00003') {
            $talon_pedi = 10;
            $talon_fac = 1;
        }

        if ($pv_pedi == 'E00011') {
            $talon_pedi = 10;
            $talon_fac = 22;
        }

        $tot_pedi_x = array_sum($this->tot_para_ped);
        $tot_pedi = $tot_pedi_x - $bonif_cosme - $practicosas + $gastadmin;


        //echo $tot_pedi.'<br>';
        //echo '->' . $this->tabla_cliente_cod_cliente["COD_PROVIN"] . '<-';
        //' . $fecha . '
        $orden = '{
        "Date": "2024-02-27 T00:00:00",
        "Total": ' . $tot_pedi . ', 
        "PaidTotal": ' . $tot_pedi . ',
        "FinancialSurcharge": 0.0,
        "IvaIncluded": true,
        "WarehouseCode": "1",
        "SellerCode": "' . $cod_zona . '",
        "TransportCode": "01",
        "SaleCondition": "1",
        "OrderID": ' . $this->id_orden . ',    
        "OrderNumber": ' . $this->id_orden . ',
        "OrderCounterfoil": ' . $talon_pedi . ',
        "InvoiceCounterfoil": ' . $talon_fac . ',
        "ValidateTotalWithPaidTotal": false,
        "ValidateTotalWithItems": false,
        "Customer": {
        "CustomerID": 1000,
        "Code": "' . $this->tabla_cliente_cod_cliente['COD_CLIENT'] . '",      
        "DocumentType": "96",
        "DocumentNumber": "' . $this->tabla_cliente_cod_cliente['CUIT'] . '",
        "IVACategoryCode": "' . $this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] . '",
        "User": "RXN-APP-TIENDAS",
        "Email": "soporte@reaxion.com.ar",
        "FirstName": "Rxn",
        "LastName": "RxnLastName",
        "BusinessName": "",        
        "Street": "",
        "HouseNumber": "",
        "Floor": "",
        "Apartment": "",
        "City": "",
        "ProvinceCode": "' . $this->tabla_cliente_cod_cliente['ID_GVA18'] . '",
        "PostalCode": "",
        "PhoneNumber1": "",
        "PhoneNumber2": "",
        "BusinessAddress": "' . $this->tabla_cliente_cod_cliente['DIRECCION'] . '",
        "NumberListPrice": 1
        },
        "OrderItems": [
        ' . $articulo . '
        ],
        "CashPayment": {
        "PaymentID": ' . $this->id_orden . ',
        "PaymentMethod": "A01",
        "PaymentTotal": ' . $tot_pedi . '
        }
        }';

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");

        curl_setopt($ch, CURLOPT_POSTFIELDS, $orden);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //Set your auth headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Expect:",
            "Content-Length: " . strlen($orden),
            "Authorization: Bearer " . $token,
            "accesstoken: $token"
        ));

        //echo '<br>->' . $token . '<-hola';
        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $verbose = fopen('log_curl.txt', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        $data = curl_exec($ch);

        $info = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $data2 = json_decode($data, true);

        $data3 = json_encode($data);

        echo $orden . '<br>';

        $this->mensaje_api = $data2;

        //print_r($data2);

        curl_close($ch);
    }

    public $clientesTango;

    /* Ejecuto la lectura del archivo para su posterior ingreso */

    public function procesoCsvClientes() {
        /* Ejecuto el método que guarda el nombre en un array */

        $this->consultoClientes();

        /* Llamo al método que completa los arrays de validación */
        $this->clientesCsv();

        /* Controlo si viene algún valor del CSV para poder comenzar a procesar */
        if (isset($this->cli_csv)) {

            /* Array para csv */
            foreach ($this->cli_csv as $ctrlf) {

                if ($ctrlf['0'] != 'codcliente') {
                    $csv[] = $ctrlf;
                }
            }

            /* Extraigo el vaor de DNI desde el CSV */
            foreach ($csv as $valor_csv) {
                $nuevo_array_csv[] = array("CUIT" => $valor_csv[4], "COD_CLIENT" => $valor_csv[0], "RAZON_SOCI" => $valor_csv[2], "DOMIC" => $valor_csv[5], "LOCALIDAD" => $valor_csv[9], "CAT_IVA" => $valor_csv[17], "COD_PROVIN" => $valor_csv[16], "NUCUIT" => $valor_csv[18], "COD_VENDED" => $valor_csv[25], "TIP_DOC" => $valor_csv[3], "ALIC_PERC" => $valor_csv[24], "COD_POST" => $valor_csv[14], "NOMBRE_ARCHIVO" => $valor_csv[26]);
            }

            foreach ($nuevo_array_csv as $cliente) {
                $this->maxIdGva14();
                /* Cambio de variable en base a tipdoc en el caso que venga 96 sería CUIT que sería el docnro del CSV */
                if ($cliente['TIP_DOC'] == 96) {
                    $cuit_dni = $cliente['CUIT'];
                }
                if ($cliente['TIP_DOC'] == 80) {
                    $cuit_dni = $cliente['NUCUIT'];
                }
                /* Controlo si el cliente existe para su ingreso */
                $this->selectGva14($cuit_dni, $cliente['TIP_DOC']);
                //echo '->' . $cliente['TIP_DOC'] . '<- <br>';
                //echo '->CUIT: ' . $this->selectGva14['CUIT'] . '<- <br>';
                //echo '->CUIT csv: ' . $cuit_dni . '<- <br>';
                /* Controlo si el CUIT que estoy recorriendo es distinto al que está en la base */
                if ($this->selectGva14['CUIT'] !== $cuit_dni) {

                    $maxId = $this->maxIdGva14['COD_CLIENT'] + 1;
                    //echo '->' . $this->selectGva14['TIPO_DOC'] . '<- <br>';
                    //echo '->' . $cliente['TIP_DOC'] . '<- <br>';
                    $this->ingresoCliente($maxId, $cliente['CUIT'], $cliente['RAZON_SOCI'], $cliente['DOMIC'], $cliente['LOCALIDAD'], $cliente['CAT_IVA'], $cliente['COD_PROVIN'], $cliente['COD_CLIENT'], $cliente['NUCUIT'], $cliente['COD_VENDED'], $cliente['TIP_DOC'], $cliente['ALIC_PERC'], $cliente['COD_POST']);
                    /* Ingreso el dato de alícuota de cliente para usar en trigger de facturación */
                    $this->ingresoClieAlic($maxId, $cliente['CAT_IVA'], $cliente['ALIC_PERC']);

                    if (!isset($this->mensaje_api['savedId'])) {
                        $id = 0;
                        $mensaje = 'nulo';
                    } else {
                        $id = $this->mensaje_api['savedId'];
                        //$mensaje = $this->mensaje_api['messages'];
                    }
                    /* Función interna para poder obtener el mensaje de error */
                    $stringConvertido = $this->convertirATexto($this->mensaje_api);

                    if ($this->mensaje_api['succeeded'] == '') {
                        $grabo = 0;
                    } else {
                        $grabo = 1;
                    }

                    $mensaje_api = 'ID_INTERNO: ' . $id . ' Mensaje: ' . $stringConvertido . ' ¿Grabó?: ' . $this->mensaje_api['succeeded'];
                    $this->ingresoMensajesApi($cuit_dni, 'CLIENTES', $mensaje_api, $grabo, $cliente['COD_CLIENT'], $cliente['NOMBRE_ARCHIVO'], 0);

                    echo $mensaje_api . '<br>';
                } else {
                    echo 'Se procesó el cliente con el DNI : ' . $cliente['CUIT'] . ' ya existe<br>';
                    $this->ingresoMensajesApi($cuit_dni, 'CLIENTES', 'SE PROCESO PERO YA EXISTIA EN LA BASE', 0, $cliente['COD_CLIENT'], $cliente['NOMBRE_ARCHIVO'], 0);
                    $this->busco_cliente($cliente['COD_CLIENT']);
                    $this->ingresoClieAlic($this->tabla_cliente_cod_cliente['COD_CLIENT'], $cliente['CAT_IVA'], $cliente['ALIC_PERC']);
                }
            }
            /* Limpio los archivos a procesar */
            $this->actualizoArchivoClientesImpo();
            return;
        }
        echo 'No hay archivos de clientes para procesar.<br>';
        return;
    }

    /* Convertir el array de mensaje a texto */

    public function convertirATexto($array, $nivel = 0) {
        $resultado = '';

        foreach ($array as $clave => $elemento) {
            $resultado .= str_repeat(' ', $nivel * 4); // Agrega espacios para indentación
            $resultado .= "$clave: ";

            if (is_array($elemento)) {
                // Si el elemento es un subarray, llamar recursivamente a la función
                $resultado .= "\n" . self::convertirATexto($elemento, $nivel + 1);
            } else {
                // Si el elemento es un valor escalar, convertir a texto y agregar al resultado
                $resultado .= strval($elemento) . "\n";
            }
        }

        return $resultado;
    }

    /* Método para el ingreso de artículos */

    public function ingresoArticulo($cod_articulo, $descripcio, $desc_adic) {
        $token = '
        {
        "ApiAuthorization": "' . $this->token_api_local . '";
        "Company: "' . $this->id_empresa . '"
        }
        ';

        /* Busco la configuración del server desde la tabla */
        $this->devuelvoTokens();


        //setup the request, you can also use CURLOPT_URL
        $ch = curl_init($this->token['RUTA_LOCAL'] . '/Api/Create?process=87');

        // Returns the data/output as a string instead of raw data

        $data_string = '
        {
        "OBSERVACIONES": "",
        "CONTROL_STOCK_NOTMAPPED": "UM de stock 1",
        "STA_ARTICULO_UNIDAD_COMPRA": [],
        "CTA_ARTICULO_POR_SUCURSAL": [],
        "GVA17": [],
        "GVA11": [],
        "CPA15": [],
        "STA83": [],
        "COD_STA11_DEFECTO": "' . $cod_articulo . '",
        "ACTUALIZAR_PRECIOS_COMBINACIONES": false,
        "PRECIO_MODIFICADO": false,
        "ID_STA11_DEFECTO": null,
        "DECIMALES_GVA17": null,
        "COD_STA11": "' . $cod_articulo . '",
        "DESCRIPCIO": "' . $descripcio . '",
        "SINONIMO": null,
        "COD_BARRA": null,
        "FECHA_MODI": "2024-01-17T11:41:37.207",
        "STOCK_NEG": false,
        "FECHA_ALTA": "2016-03-22T00:00:00",
        "PROMO_MENU": "A",
        "USA_ESC": "N",
        "ID_STA11_BASE": null,
        "ID_STA33_VALOR1": null,
        "ID_STA33_VALOR2": null,
        "DESC_ADIC": "' . $desc_adic . '",
        "PERFIL": "V",
        "STOCK": false,
        "LLEVA_DOBLE_UNIDAD_MEDIDA": false,
        "USA_PARTID": false,
        "DESCARGA_NEGATIVO_STOCK": false,
        "MET_DES_PA": null,
        "ORD_DES_PA": null,
        "EGRESO_MODIFICA_PARTIDA_PROPUESTA": null,
        "USA_SERIE": false,
        "USA_SCRAP": false,
        "PORC_SCRAP": 0,
        "ID_TYPS": null,
        "COMISION_V": 0,
        "DESCUENTO": 0,
        "PORC_UTILI": 0,
        "ID_GVA22": null,
        "DESVIO_CIERRE_PEDIDOS": 0,
        "DESCARGA_NEGATIVO_VENTAS": false,
        "REMITIBLE": "S",
        "FACT_IMPOR": false,
        "PORC_DESVI": 0,
        "USA_CTRPRE": false,
        "AFECTA_AF": false,
        "ID_TIPO_BIEN": null,
        "ID_STA115": null,
        "ID_STA32_ESCALA_1": null,
        "ID_STA32_ESCALA_2": null,
        "TIPO_PROMO": null,
        "PROMODESDE": null,
        "PROMOHASTA": null,
        "ID_MEDIDA_STOCK": 4,
        "STOCK_MAXI": 0,
        "STOCK_MINI": 0,
        "PTO_PEDIDO": 0,
        "ID_MEDIDA_STOCK_2": null,
        "EQUIVALENCIA_STOCK_2": 0,
        "RELACION_UNIDADES_STOCK": "A",
        "USA_CONTROL_UNIDADES_STOCK": false,
        "DESVIO_CONTROL_UNIDADES_STOCK": 0,
        "ID_MEDIDA_VENTAS": 4,
        "EQUIVALE_V": 1,
        "ID_GVA41_COD_IVA": 5,
        "ID_GVA41_COD_S_IVA": 11,
        "ID_GVA41_COD_II": 7,
        "IMPUESTO_I": 0,
        "ID_GVA41_COD_S_II": 3,
        "ID_GVA41_COD_II_V_2": 2,
        "IMP_II_V_2": 0,
        "ID_GVA41_COD_SII_V2": 3,
        "GEN_IB": true,
        "ID_GVA41_COD_IB": 8,
        "GEN_IB3": false,
        "ID_GVA41_COD_IB3": null,
        "PERC_NO_CA": false,
        "ID_GVA41_ALI_NO_CAT": null,
        "ID_CPA14_COD_IVA_CO": null,
        "ID_CPA14_COD_S_IV_C": null,
        "ID_CPA14_COD_II_CO": null,
        "IMPUEST_IC": 0,
        "ID_CPA14_COD_S_II_C": null,
        "GENERACOT": false,
        "PRODUCTO_TERMINADO_COT": "S",
        "ID_GVA125": null,
        "RENTA_UM_S": null,
        "RENTA_EQ_S": 0,
        "RENTA_UM_V": null,
        "RENTA_EQ_V": 0,
        "ID_UNIDAD_MEDIDA_AFIP_UM_S": 8,
        "AFIP_EQ_S": 1,
        "ID_UNIDAD_MEDIDA_AFIP_UM_V": 8,
        "AFIP_EQ_V": 1,
        "COD_NCM": null,
        "ID_UNIDAD_MEDIDA_AFIP_UMEX_S": 8,
        "ID_UNIDAD_MEDIDA_AFIP_UMEX_V": 8,
        "AFIP_UMEX_S": 7,
        "AFIP_UMEX_V": 7,
        "ID_TIPO_ITEM_AFIP": null,
        "ID_CODIGO_ITEM_TURISMO": null,
        "ID_TIPO_UNIDAD_TURISMO": null,
        "CL_SIAP_GV": "SIN",
        "ID_CLASIFICACION_SIAP_CL_SIAP_GV": 14,
        "CL_SIAP_CP": "SIN",
        "ID_CLASIFICACION_SIAP_CL_SIAP_CP": 13,
        "ID_ACTIVIDAD_DGI": 889,
        "ID_MODELO_PERCEPCION_VENTAS": null,
        "ALI_NO_CAT": null,
        "COD_ACTIVI": null,
        "COD_IB": 51,
        "COD_IB3": null,
        "COD_II": 40,
        "COD_II_CO": null,
        "COD_IVA": 1,
        "COD_IVA_CO": null,
        "COD_S_II": 41,
        "COD_S_II_C": null,
        "COD_S_IVA": 12,
        "COD_S_IV_C": null,
        "CTA_COMPRA": null,
        "CTA_VENTAS": null,
        "CTO_COMPRA": null,
        "CTO_VENTAS": null,
        "DESTI_ART": false,
        "ESCALA_1": null,
        "ESCALA_2": null,
        "RET_RNI": true,
        "RET_RNI_CO": null,
        "RENTA_PROD": null,
        "COD_II_V_2": 21,
        "COD_SII_V2": 41,
        "BASE": null,
        "VALOR1": null,
        "VALOR2": null,
        "AFIP_UM_S": 99,
        "AFIP_UM_V": 99,
        "COD_PLANTI": null,
        "COD_TIPOB": null,
        "ID_MEDIDA_CONTROL_STOCK": 5,
        "SERIE_DESC_ADICIONAL_1": null,
        "SERIE_DESC_ADICIONAL_2": null,
        "PUBLICA_WEB_PEDIDO": "N",
        "SINCRONIZA_WEB_PEDIDO": "N",
        "FILLER": null
    }';

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //Set your auth headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'ApiAuthorization: ' . $this->token_api_local . '',
            'Company: ' . $this->id_empresa . '',
            'Content-Type: application/json'
        ));

        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $verbose = fopen('log_curl.txt', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        // get stringified data/output. See CURLOPT_RETURNTRANSFER
        $data = curl_exec($ch);

        // get info about the request
        $info = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $data2 = json_decode($data, true);

        $this->mensaje_api = $data2;

        curl_close($ch);
    }

    /* Método que genera la lectura e ingreso a sistema de los datos del CSV */

    public function procesoCsvArticulos() {
        $this->articulosCsv();

        /* Controlo que el array de archivos venga completo para ingresar al proceso */
        if (isset($this->arts_csv)) {
            foreach ($this->arts_csv as $valor_csv) {
                $matriz_ars[] = array("COD_ARTICU" => $valor_csv[0], "DESCRIPCIO" => $valor_csv[2], "NOMBRE_ARCHIVO" => $valor_csv[24]);
            }

            // print_r($matriz_ars);

            /* Recorro los artículos del array */
            foreach ($matriz_ars as $arts_csv) {
                /* Hago el control del artículo que está ingresar a la base */
                $this->ctrlArtsBase($arts_csv['COD_ARTICU']);
                //echo $this->ctrl_articu['COD_ARTICU'].' <- Intento ingresar este artículo <br> ';
                //echo $arts_csv['COD_ARTICU'] . ' <- Este es el del CSV <br> ';
                //echo $this->ctrl_articu['COD_ARTICU'];
                if ($this->ctrl_articu['COD_ARTICU'] == $arts_csv['COD_ARTICU']) {
                    echo "No hay artículos para ingresar, sin embargo el artículo ya existe en la base de datos: " . $arts_csv['COD_ARTICU'] . "<br>";
                    $this->ingresoMensajesApi($arts_csv['COD_ARTICU'], 'ARTICULOS', 'NO-HAY-ARTICULOS-PARA-PROCESAR SIN EMBARGO SE VERIFICA LA EXISTENCIA DEL ARTICULO: ' . $arts_csv['COD_ARTICU'], 0, '', $arts_csv['NOMBRE_ARCHIVO'], 0);
                } else {
                    // Array de reemplazo, donde las claves son los caracteres a reemplazar y los valores son los caracteres de reemplazo
                    $reemplazos = array(
                        'á' => 'a',
                        'Á' => 'A',
                        'é' => 'e',
                        'É' => 'E',
                        'í' => 'i',
                        'Í' => 'I',
                        'ó' => 'o',
                        'Ó' => 'O',
                        'ú' => 'u',
                        'Ú' => 'U',
                        'Ñ' => 'N',
                        'ñ' => 'n',
                        'ë' => 'e',
                            // Agrega más caracteres según tus necesidades
                    );

                    $cambiar = strtr($arts_csv['DESCRIPCIO'], $reemplazos);

                    //$a_convertir = mb_convert_encoding($convertido, 'UTF-8');
                    $this->ingresoArticulo($arts_csv['COD_ARTICU'], substr($cambiar, 0, 30), substr($cambiar, 30, 50));
                    //echo $arts_csv['COD_ARTICU'].' <- Intento ingresar este artículo <br> ';
                    echo 'Se ingresó: ' . $arts_csv['COD_ARTICU'] . ' <br> ';
                    if (!isset($this->mensaje_api['savedId'])) {
                        $id = 0;
                        $mensaje = 'nulo';
                    } else {
                        $id = $this->mensaje_api['savedId'];
                        //$mensaje = $this->mensaje_api['messages'];
                    }
                    /* Función interna para poder obtener el mensaje de error */
                    $stringConvertido = $this->convertirATexto($this->mensaje_api);

                    if ($this->mensaje_api['succeeded'] == '') {
                        $grabo = 0;
                    } else {
                        $grabo = 1;
                    }

                    $mensaje_api = 'ID_INTERNO: ' . $id . ' Mensaje: ' . $stringConvertido . ' ¿Grabó?: ' . $this->mensaje_api['succeeded'];

                    echo $mensaje_api . '<br>';

                    $this->ingresoMensajesApi($arts_csv['COD_ARTICU'], 'ARTICULOS', $mensaje_api, $grabo, '', $arts_csv['NOMBRE_ARCHIVO'], 0);
                }
                /* Limpio el excel importado actualizando el archivo importado a procesado P */
                $this->actualizoArchivoArticuloImpo();
            }
        }
    }

    public $arts_csv;

    public function articulosCsv() {
        /* Ejecuto el método que guarda el nombre en un array */
        $this->leoArchivosBdArt();
        if (isset($this->nombre_archivo_art)) {
            foreach ($this->nombre_archivo_art as $archivo) {
                /* Leo el nombre de archivo en directorio */
                $archivo2 = fopen($this->leoParametroBd('RUTAXML') . '\\' . $archivo['NOMBRE_ARCHIVO'], "r");

                while (($datos = fgetcsv($archivo2, ",")) == true) {
                    //$num = count($datos);
                    $array_nombre_archivo = array($archivo['NOMBRE_ARCHIVO']);
                    /* Array con los datos del CSV */
                    $arts[] = array_merge($datos, $array_nombre_archivo);
                    // $this->arts_csv = $arts;
                }
                /* Limpio el array de artículos */
                foreach ($arts as $ctrl_cod_art) {
                    if ($ctrl_cod_art[0] != 'cod_articulo') {
                        $ctrl[] = $ctrl_cod_art;
                        $this->arts_csv = $ctrl;
                    }
                    //print_r($this->arts_csv);
                }

//Cerramos el archivo
                fclose($archivo2);
            }
        } else {
            echo "No hay artículos para procesar<br>";
            $this->ingresoMensajesApi('SIN', 'ARTICULOS', 'NO-HAY-ARCHIVOS-PARA-PROCESAR', 0, '', '', 0);
        }
    }

    public function ingresoFactura($cliente, $importe, $articulo, $nro_pedido, $cod_zona, $imp_sin_impuestos, $fecha, $bonif_cosme, $practicosas, $gastadmin, $imp_iva) {
        $token = '
        {
        "ApiAuthorization": "' . $this->token_api_local . '";
        "Company: "' . $this->id_empresa . '"
        }
        ';

        /* Busco la configuración del server desde la tabla */
        $this->devuelvoTokens();


        //setup the request, you can also use CURLOPT_URL
        $ch = curl_init($this->token['RUTA_LOCAL'] . '/FacturadorVenta/registrar');

        $this->ingresoPedidoControl($nro_pedido, $cliente);

        $this->devuelvoIdPedido();

        /* Busco el COD_CLIENT original */
        $this->busco_cliente($cliente);


        /* Verifico el punto de venta del pedido para apuntarlo al talonario */
        //Extraigo el PV del pedido
        $pv_pedi = substr($nro_pedido, 0, 6);
        //echo $pv_pedi.'<BR>';
        if ($pv_pedi == 'B00003') {
            $talon_pedi = 10;
            $talon_fac = 5;
        }
        if ($pv_pedi == 'A00003') {
            $talon_pedi = 10;
            $talon_fac = 1;
        }

        if ($pv_pedi == 'E00011') {
            $talon_pedi = 10;
            $talon_fac = 22;
        }

        /* Busco cliente */
        $this->busco_cliente($cliente);


        /* Completo el IVA en el caso que el cliente sea CF */
        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'CF') {
            $bonif_cosme_iva_formato = round((($bonif_cosme/1.21) * 0.21),2);
        }
        //echo '<br> Importe de IVA ' . $bonif_cosme_iva_formato . '<br>';
        $bonif_cosme_sin_iva = ($bonif_cosme / 1.21);
        $precio_bonif = $bonif_cosme;
        $p_porc_cosme = $this->tabla_cliente_cod_cliente['PORCENTAJE'];
        $p_cosme_base = $bonif_cosme;
        $p_cosme_importe = $bonif_cosme_sin_iva * ($p_porc_cosme / 100);

        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'SNC') {
            $p_snc_cosme = 10.5;
            $p_snc_cosme_base = $bonif_cosme;
            $p_snc_cosme_importe = ($p_cosme_base * ($p_snc_cosme / 100));
            $bonif_cosme_iva = round($bonif_cosme_sin_iva * 0.21, 2) + $p_snc_cosme_importe;
            $bonif_cosme_iva_formato = round($bonif_cosme_iva, 2);

            if ($this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] != '') {
                $alic = ',{
						"codigoAlicuota": ' . $this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] . ',
						"codigoPercepcion": "",
						"porcentaje": ' . round($p_porc_cosme, 2) . ',
						"base": -' . round($p_cosme_base, 2) . ',
						"importe": -' . round($p_cosme_importe, 2) . '
                                        	}';
            }
            if(empty($alic)){$alic = '';}

            $percepciones = ',"percepciones":
                                        [{
						"codigoAlicuota": 11,
						"codigoPercepcion": "",
						"porcentaje" : ' . round($p_snc_cosme, 2) . ',
						"base": -' . round($p_snc_cosme_base, 2) . ',
						"importe": -' . round($p_snc_cosme_importe, 2) . '
					} ' . $alic . '
                                	]';

            $this->tot_bonif_10_5 = round($p_snc_cosme_importe, 2);
            $this->tot_bonif_ali = round($p_cosme_importe, 2);
        }

        if (empty($percepciones)) {
            $percepciones = '';
        }
        $bonif_cosme = '{
				"codigo": "03",
				"descripcion": "Bonfificacion Cosméticos",
				"descargaStock" : false,
				"cantidad": -1,
				"codigoDeposito": "1",
                                "importeIva": ' . $bonif_cosme_iva_formato . ',
				"codigoUM": "BON",
				"precio": ' . round($precio_bonif, 2) . ',
                                "importe": ' . round($precio_bonif, 2) . ',
                                "importeSinImpuestos": ' . round($bonif_cosme_sin_iva, 2) . $percepciones . '
                        	},
                     ';
        //Configuro los totales a sumar.
        $this->tot_bonificaciones = round($precio_bonif, 2);
        $this->tot_bonificaciones_iva = $bonif_cosme_iva_formato;
        $this->tot_bonif_sin_impuestos = round($bonif_cosme_sin_iva, 2);
        $this->tot_bonif_subtotal = round($precio_bonif, 2);


        /* Completo el IVA en el caso que el cliente sea CF */
        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'CF') {
            $bonif_practicosas_iva_formato = round((($practicosas/1.21) * 0.21),2);
            //$bonif_practicosas_iva_formato = round($practicosas * 0.21,2);
        }

        $bonif_practicosas_sin_iva = ($practicosas / 1.21);
        $precio_practicosas = $practicosas;
        $p_porc_practicosas = $this->tabla_cliente_cod_cliente['PORCENTAJE'];
        $p_practicosas_base = $practicosas;
        $p_practicosas_importe = $bonif_practicosas_sin_iva * ($p_porc_practicosas / 100);

        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'SNC') {
            $p_snc_practicosas = 10.5;
            $p_snc_practicosas_base = $practicosas;
            $p_snc_practicosas_importe = ($p_practicosas_base * ($p_snc_practicosas / 100));
            $bonif_practicosas_iva = round($bonif_practicosas_sin_iva * 0.21, 2) + $p_snc_practicosas_importe;
            $bonif_practicosas_iva_formato = round($bonif_practicosas_iva, 2);

            if ($this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] != '') {
                $alic_practicosas = ',{
						"codigoAlicuota": ' . $this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] . ',
						"codigoPercepcion": "",
						"porcentaje": ' . round($p_porc_practicosas, 2) . ',
						"base": -' . round($p_practicosas_base, 2) . ',
						"importe": -' . round($p_practicosas_importe, 2) . '
                                        	}';
            }
            
            if(empty($alic_practicosas)){$alic_practicosas = '';}
            
            $percepciones_practicosas = ',"percepciones":
                                        [{
						"codigoAlicuota": 11,
						"codigoPercepcion": "",
						"porcentaje" : ' . round($p_snc_practicosas, 2) . ',
						"base": -' . round($p_snc_practicosas_base, 2) . ',
						"importe": -' . round($p_snc_practicosas_importe, 2) . '
					} ' . $alic_practicosas . '
                                	]';
            $this->tot_practicosas_10_5 = round($p_snc_practicosas_importe, 2);
            $this->tot_practicosas_ali = round($p_practicosas_importe, 2);
        }
        if (empty($percepciones_practicosas)) {
            $percepciones_practicosas = '';
        }
        $practicosas = '{
				"codigo": "04",
				"descripcion": "Bonfificacion Practicosas",
				"descargaStock" : false,
				"cantidad": -1,
				"codigoDeposito": "1",
                                "importeIva": ' . $bonif_practicosas_iva_formato . ',
				"codigoUM": "BON",
				"precio": ' . round($precio_practicosas, 2) . ',
                                "importe": ' . round($precio_practicosas, 2) . ',
                                "importeSinImpuestos": ' . round($bonif_practicosas_sin_iva, 2) . $percepciones_practicosas . '
                        	},
                     ';
        //Configuro los totales a sumar.
        $this->tot_practicosas = round($precio_practicosas, 2);
        $this->tot_practicosas_iva = $bonif_practicosas_iva_formato;
        $this->tot_practicosas_sin_impuestos = round($bonif_practicosas_sin_iva, 2);
        $this->tot_practicosas_subtotal = round($precio_practicosas, 2);


        /* Completo el IVA en el caso que el cliente sea CF */
        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'CF') {
            $bonif_gastos_administrativos_iva_formato = round((($gastadmin/1.21) * 0.21),2);
            //$bonif_gastos_administrativos_iva_formato = round($gastadmin * 0.21,2);
        }

        $bonif_gastos_administrativos_sin_iva = ($gastadmin / 1.21);
        $precio_gastos_administrativos = $gastadmin;
        $p_porc_gastos_administrativos = $this->tabla_cliente_cod_cliente['PORCENTAJE'];
        $p_gastos_administrativos_base = $gastadmin;
        $p_gastos_administrativos_importe = $bonif_gastos_administrativos_sin_iva * ($p_porc_gastos_administrativos / 100);

        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'SNC') {
            $p_snc_gastos_administrativos = 10.5;
            $p_snc_gastos_administrativos_base = $gastadmin;
            $p_snc_gastos_administrativos_importe = ($p_gastos_administrativos_base * ($p_snc_gastos_administrativos / 100));
            $bonif_gastos_administrativos_iva = round($bonif_gastos_administrativos_sin_iva * 0.21, 2) + $p_snc_gastos_administrativos_importe;
            $bonif_gastos_administrativos_iva_formato = round($bonif_gastos_administrativos_iva, 2);

            if ($this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] != '') {
                $alic_gastos_administrativos = ',{
						"codigoAlicuota": ' . $this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] . ',
						"codigoPercepcion": "",
						"porcentaje": ' . round($p_porc_gastos_administrativos, 2) . ',
						"base": ' . round($p_gastos_administrativos_base, 2) . ',
						"importe": ' . round($p_gastos_administrativos_importe, 2) . '
                                        	}';
            }
            if(empty($alic_gastos_administrativos)){$alic_gastos_administrativos = '';}
            $percepciones_gastos_administrativos = ',"percepciones":
                                        [{
						"codigoAlicuota": 11,
						"codigoPercepcion": "",
						"porcentaje" : ' . round($p_snc_gastos_administrativos, 2) . ',
						"base": ' . round($p_snc_gastos_administrativos_base, 2) . ',
						"importe": ' . round($p_snc_gastos_administrativos_importe, 2) . '
					} ' . $alic_gastos_administrativos . '
                                	]';

            $this->tot_gastos_administrativos_10_5 = round($p_snc_gastos_administrativos_importe, 2);
            $this->tot_gastos_administrativos_ali = round($p_gastos_administrativos_importe, 2);
        }
        if (empty($percepciones_gastos_administrativos)) {
            $percepciones_gastos_administrativos = '';
        }
        $gastadmin = '{
				"codigo": "04",
				"descripcion": "Bonfificacion gastos_administrativos",
				"descargaStock" : false,
				"cantidad": -1,
				"codigoDeposito": "1",
                                "importeIva": ' . $bonif_gastos_administrativos_iva_formato . ',
				"codigoUM": "BON",
				"precio": ' . round($precio_gastos_administrativos, 2) . ',
                                "importe": ' . round($precio_gastos_administrativos, 2) . ',
                                "importeSinImpuestos": ' . round($bonif_gastos_administrativos_sin_iva, 2) . $percepciones_gastos_administrativos . '
                        	}
                     ';
        //Configuro los totales a sumar.
        $this->tot_gastos_administrativos = round($precio_gastos_administrativos, 2);
        $this->tot_gastos_administrativos_iva = $bonif_gastos_administrativos_iva_formato;
        $this->tot_gastos_administrativos_sin_impuestos = round($bonif_gastos_administrativos_sin_iva, 2);
        $this->tot_gastos_administrativos_subtotal = round($precio_gastos_administrativos, 2);

        /* Controlo si los arrays de artíuclos vienen vacíos para dejar un cero */
        if (empty($this->art_total_10_50)) {
            $art_total_10_50 = 0;
        } else {
            $art_total_10_50 = array_sum($this->art_total_10_50);
        }
        if (empty($this->art_total_perc)) {
            $art_total_perc = 0;
        } else {
            $art_total_perc = array_sum($this->art_total_perc);
        }

        $total_iva = array_sum($this->art_total_iva);
        $subtotal = array_sum($this->art_total_precio);
        $subtotal_sin_imp = array_sum($this->tot_arts_sin_impuestos);
        $total_comp = array_sum($this->art_total_precio) + $art_total_10_50 + $art_total_perc;
        $articulo = $articulo . $bonif_cosme . $practicosas . $gastadmin;
        //echo 'Total comprobante: ' . $total_comp . '<br>';

        /* Si las variables de percepción vienen vacías entonces omito la carga en el total */
        if (empty($this->art_total_perc)) {
            $this->tot_bonif_10_5 = 0;
        }if (empty($this->tot_bonif_ali)) {
            $this->tot_bonif_ali = 0;
        }if (empty($this->tot_practicosas)) {
            $this->tot_practicosas = 0;
        }if (empty($this->tot_practicosas_10_5)) {
            $this->tot_practicosas_10_5 = 0;
        }if (empty($this->tot_practicosas_ali)) {
            $this->tot_practicosas_ali = 0;
        }
        if (empty($this->tot_gastos_administrativos)) {
            $this->tot_gastos_administrativos = 0;
        }if (empty($this->tot_gastos_administrativos_10_5)) {
            $this->tot_gastos_administrativos_10_5 = 0;
        }if (empty($this->tot_gastos_administrativos_ali)) {
            $this->tot_gastos_administrativos_ali = 0;
        }
        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'SNC') {
        //Configuro los totales + las bonificaciones que llegaron desde arriba
        $tot_finales_iva = $total_iva - $this->tot_bonificaciones_iva - $this->tot_practicosas_iva + $this->tot_gastos_administrativos_iva;
        $tot_finales_subtotal = $subtotal - $this->tot_bonif_subtotal - $this->tot_practicosas_subtotal + $this->tot_gastos_administrativos_subtotal;
        $tot_finales_subtotal_sin_imp = $subtotal_sin_imp - $this->tot_bonif_sin_impuestos - $this->tot_practicosas_sin_impuestos + $this->tot_gastos_administrativos_sin_impuestos;
        $tot_finales_tot_comp = $total_comp - $this->tot_bonificaciones - $this->tot_bonif_10_5 - $this->tot_bonif_ali - $this->tot_practicosas - $this->tot_practicosas_10_5 - $this->tot_practicosas_ali + $this->tot_gastos_administrativos + $this->tot_gastos_administrativos_10_5 + $this->tot_gastos_administrativos_ali;
        }
        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'CF') {
        $tot_finales_iva = $total_iva - $this->tot_bonificaciones_iva - $this->tot_practicosas_iva + $this->tot_gastos_administrativos_iva;
        $tot_finales_subtotal = $subtotal - $this->tot_bonif_subtotal - $this->tot_practicosas_subtotal + $this->tot_gastos_administrativos_subtotal;
        $tot_finales_subtotal_sin_imp = $subtotal_sin_imp - $this->tot_bonif_sin_impuestos - $this->tot_practicosas_sin_impuestos + $this->tot_gastos_administrativos_sin_impuestos;
        $tot_finales_tot_comp = $total_comp - $this->tot_bonificaciones  - $this->tot_practicosas + $this->tot_gastos_administrativos;
        }
        
        echo '<br> Total de Iva: ' . $tot_finales_iva . ' Subotal: ' . $tot_finales_subtotal . ' Subtotal sin impuestos: ' . $tot_finales_subtotal_sin_imp . ' Total del comprobante : ' . $tot_finales_tot_comp . '<br>';



        $ceros = str_pad($this->id_orden, 8, '0', STR_PAD_LEFT);
        $n_fac = 'B00003' . $ceros;
        //$tot_pedi_x = array_sum($this->tot_para_ped);
        //$tot_pedi = $tot_pedi_x - $bonif_cosme - $practicosas + $gastadmin;
        // Returns the data/output as a string instead of raw data
        $data_string = '
        [
	{
		"codigoTipoComprobante": "FAC",
		"numeroComprobante": "' . $n_fac . '",
		"codigoTalonario": "' . $talon_fac . '",
		"codigoCliente": "' . $this->tabla_cliente_cod_cliente['COD_CLIENT'] . '",
		"codigoCondicionDeVenta": 1,
		"numeroDeProyecto": "",
		"codigoOperacionRG3685": "",
		"codigoClasificacion": "",
		"fechaComprobante": "' . $fecha . '",
		"fechaCierreTesoreria": "' . $fecha . '",
		"codigoListaPrecio": "1",
		"cotizacionVentas": null,
		"codigoContracuenta": "20",
		"codigoDeposito": "1",
		"codigoVendedor": "1",
		"idMotivo": "3",
		"codigoAsiento": "1",
		"leyenda1": "Leyenda 1",
		"leyenda2": "Leyenda 2",
		"leyenda3": "Leyenda 3",
		"leyenda4": "Leyenda 4",
		"leyenda5": "Leyenda 5",
		"esMonedaExtranjera": false,
		"total" : ' . $this->formatearNumero($tot_finales_tot_comp) . ',
		"totalExento": 0.00,
		"totalIva": ' . $this->formatearNumero($tot_finales_iva) . ',
		"subtotal": ' . $this->formatearNumero($tot_finales_subtotal) . ',
		"totalSinImpuestos": ' . $this->formatearNumero($tot_finales_subtotal_sin_imp) . ',
		"subtotalSinImpuestos": ' . $this->formatearNumero($tot_finales_subtotal_sin_imp) . ',
		"descuentoPorcentaje": 0,
		"descuentoMonto": 0,
		"descuentoMontoSinIva": 0,
		"recargoPorcentaje": 0,
		"recargoMonto": 0,
		"recargoMontoSinIva": 0,
		"recargoFletePorcentaje": 0,
		"recargoFleteMonto": 0,
		"recargoFleteMontoSinIva": 0,
		"interesesPorcentaje": 0.00,
		"interesesMontoSinIva": 0.00,
		"observaciones": "",
		"rg3668TipoIdentificacionFirmante": null,
		"rg3668CaracterDelFirmante": null,
		"rg3668CodigoIdentificacionFirmante": "",
		"rg3668MotivoDeExcepcion": null,
		"rg3668CodigoWeb": "666",
		"items": [
                ' . $articulo . '
		],
		"cuotasCuentaCorriente" :
		[
			{
				"fechaVencimiento": "' . $fecha . '",
				"importe" : ' . $this->formatearNumero($tot_finales_tot_comp) . '
			}
                        ]
            }
        ]';

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //Set your auth headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'ApiAuthorization: ' . $this->token_api_local . '',
            'Company: ' . $this->id_empresa . '',
            'Content-Type: application/json'
        ));

        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $verbose = fopen('log_curl.txt', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        // get stringified data/output. See CURLOPT_RETURNTRANSFER
        $data = curl_exec($ch);

        // get info about the request
        $info = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $data2 = json_decode($data, true);

        $this->mensaje_api = $data2;

        print_r($data2);
        //echo '<br>' . $data_string . '<br>';
        echo '<br>' . $data_string . '<br>';
        //print_r($info);


        curl_close($ch);
    }

    public function ingresoCliente($codcliente, $cuit, $razon_social, $domicilio, $localidad, $cat_iva, $provincia, $cliente_ori, $nucuit, $cod_vended, $tip_doc, $iibb, $cod_post) {
        $token = '
        {
        "ApiAuthorization": "' . $this->token_api_local . '";
        "Company: ' . $this->id_empresa . '"
        }
        ';

        echo $iibb . '<-Ali <br>';

        /* Busco la configuración del server desde la tabla */
        $this->devuelvoTokens();

        //setup the request, you can also use CURLOPT_URL
        $ch = curl_init($this->token['RUTA_LOCAL'] . '/Api/Create?process=2117');

        /* Busco la provincia */
        $this->busco_provincia($provincia);

        /* Busco la alícuota para devolver ID_GVA41 */
        $this->busco_alicuota($iibb);

        /* Busco el ID_GVA23 */
        $this->busco_vendedor($cod_vended);
        /* Verifico si el CAT_IVA es 10 o sea SNC configuro la columna ALI_NO_CAT como 11 */
        if ($cat_iva == 10) {
            $ali_no_cat = 11;
            $id_gva41 = 1;
            //echo $id_gva41 . '<br>';
        } else {
            $ali_no_cat = 12;
            $id_gva41 = null;
            //echo '->' . $id_gva41 . '<br>';
        }

        if ($tip_doc == 96) {
            $cuit_tabla = $cuit;
            $tipo_doc_gv = 40; //Corresponde a la info que existe en la tabla TIPO_DOCUMENTO_GV 
        } else if ($tip_doc == 80) {
            $cuit_tabla = $nucuit;
            $tipo_doc_gv = 26; //Corresponde a la info que existe en la tabla TIPO_DOCUMENTO_GV 
        } else {
            $cuit_tabla = $cuit;
            $tipo_doc_gv = 40;
        }

        /* Ingeso de valores para clietnes CF */
        $data_string = '
{
    "COD_GVA14": "' . $codcliente . '",
    "COD_CLIENT": "' . $codcliente . '",
    "ID_TIPO_DOCUMENTO_GV": ' . $tipo_doc_gv . ',
    "CUIT": "' . $cuit_tabla . '",
    "RAZON_SOCI": "' . $razon_social . '",
    "DOMICILIO": "' . $domicilio . '",
    "LOCALIDAD": "' . $localidad . '",
    "C_POSTAL": "' . $cod_post . '",
    "ID_GVA05": 01,
    "ID_GVA18": ' . $this->tabla_provin . ',
    "TELEFONO_1": "' . $cliente_ori . '",
    "TELEFONO_2": null,
    "TELEFONO_MOVIL": null,
    "E_MAIL": null,
    "WEB": null,
    "NOM_COM": "' . $razon_social . '",
    "DIR_COM": "' . $domicilio . '",
    "ID_GVA151": null,
    "ID_GVA62": null,
    "ID_GVA23": ' . $this->id_gva23 . ',
    "ID_GVA24": null,
    "FECHA_ALTA": "2023-12-29T00:00:00",
    "CUMPLEANIO": "2023-12-29T00:00:00",
    "FECHA_INHA": null,
    "SEXO": null,
    "OBSERVACIO": null,
    "ID_CATEGORIA_IVA": ' . $cat_iva . ',
    "ID_GVA41_NO_CAT": ' . $id_gva41 . ',
    "SOBRE_IVA": "N",
    "PORC_EXCL": 0,
    "II_L": "N",
    "II_D": "N",
    "SOBRE_II": "N",
    "ID_GVA150": null,
    "NRO_INSCR_RG1817": null,
    "FECHA_VTO": null,
    "RG_3572_EMPRESA_VINCULADA_CLIENTE": false,
    "ID_RG_3572_TIPO_OPERACION_HABITUAL": null,
    "ID_RG_3685_TIPO_OPERACION_VENTAS": 1,
    "N_IMPUESTO": null,
    "ID_TIPO_DOCUMENTO_EXTERIOR": null,
    "NUMERO_DOCUMENTO_EXTERIOR": null,
    "ID_GVA01": 1,
    "ID_GVA10": 1,
    "CUPO_CREDI": 0,
    "MON_CTE": true,
    "PORC_DESC": 0,
    "CLAUSULA": false,
    "TIPO": null,
    "EXPORTA": false,
    "ID_SUCURSAL_DESTINO_FACTURA_REMITO": null,
    "ID_SUCURSAL_DESTINO_FACTURA": null,
    "N_ING_BRUT": null,
    "CM_VIGENCIA_COEFICIENTE": null,
    "CBU": null,
    "N_PAGOELEC": null,
    "APLICA_MORA": "N",
    "ID_INTERES_POR_MORA": null,
    "COBRA_LUNES": "N",
    "COBRA_MARTES": "N",
    "COBRA_MIERCOLES": "N",
    "COBRA_JUEVES": "N",
    "COBRA_VIERNES": "N",
    "COBRA_SABADO": "N",
    "COBRA_DOMINGO": "N",
    "HORARIO_COBRANZA": null,
    "INHABILITADO_NEXO_COBRANZAS": "N",
    "PUBLICA_WEB_CLIENTES": "N",
    "MAIL_NEXO": null,
    "DESTINO_DE": "T",
    "MAIL_DE": null,
    "IDENTIF_AFIP": null,
    "IDIOMA_CTE": "1",
    "DET_ARTIC": "P",
    "INC_COMENT": "P",
    "TYP_FEX": "H",
    "ID_GVA44_FEX": "",
    "COMENTARIO_TYP_FAC": null,
    "TYP_NCEX": "H",
    "ID_GVA44_NCEX": "",
    "COMENTARIO_TYP_NC": null,
    "TYP_NDEX": "H",
    "ID_GVA44_NDEX": "",
    "COMENTARIO_TYP_ND": null,
    "OBSERVACIONES": null,
    "FILLER": null,
    "COD_GVA18": "' . $this->tabla_provin . '",
    "ALI_NO_CAT": ' . $ali_no_cat . ',
    "RG_3572_TIPO_OPERACION_HABITUAL_VENTAS": null,
    "COD_GVA05": "01",
    "COD_GVA24": null,
    "COD_GVA151": null,
    "COD_GVA62": null,
    "COD_GVA23": null,
    "COD_RUBRO": null,
    "NRO_LISTA": "1",
    "IVA_D": null,
    "IVA_L": null,
    "COD_GVA150": null,
    "GRUPO_EMPR": null,
    "CLA_IMP_CL": "",
    "INHABILITADO_NEXO_PEDIDOS": "S",
    "RG_3685_TIPO_OPERACION_VENTAS": "0",
    "COD_PROVIN": null,
    "COD_TRANSP": "1",
    "COD_VENDED": "' . $cod_vended . '",
    "COD_ZONA": "01",
    "COND_VTA": null,
    "ID_SUCURSAL": null,
    "ADJUNTO": null,
    "CALLE": null,
    "DTO_LEGAL": null,
    "ENV_PROV": null,
    "FECHA_ANT": null,
    "NRO_LEGAL": null,
    "PISO_LEGAL": null,
    "SALDO_ANT": 0,
    "SALDO_CC": 0,
    "SALDO_DOC": 0,
    "SALDO_D_UN": 0,
    "TIPO_DOC": 80,
    "ZONA_ENVIO": "",
    "FECHA_MODI": null,
    "SAL_AN_UN": 0,
    "SALDO_CC_U": 0,
    "SUCUR_ORI": null,
    "COD_GVA05_ENV": null,
    "COD_GVA18_ENV": null,
    "WEB_CLIENT_ID": null,
    "COD_DESCRIP": null,
    "ID_GVA14_DEFECTO": null,
    "DIRECCION_ENTREGA": [
        {
            "COD_DIRECCION_ENTREGA": "PRINCIPAL",
            "HABITUAL": "S",
            "HABILITADO": "S",
            "DIRECCION": "' . $domicilio . '",
            "LOCALIDAD": "' . $localidad . '",
            "CODIGO_POSTAL": "' . $cod_post . '",
            "ID_GVA18": ' . $this->tabla_provin . ',
            "TELEFONO1": null,
            "TELEFONO2": null,
            "ENTREGA_LUNES": "N",
            "ENTREGA_MARTES": "N",
            "ENTREGA_MIERCOLES": "N",
            "ENTREGA_JUEVES": "N",
            "ENTREGA_VIERNES": "N",
            "ENTREGA_SABADO": "N",
            "ENTREGA_DOMINGO": "N",
            "HORARIO_ENTREGA": null,
            "TOMA_IMPUESTO_HABITUAL": "N",
            "LIB": "N",
            "PORC_L": 0,
            "IB_L": true,
            "ID_ALI_FIJ_IB": ' . $this->tabla_alicuo['ID_GVA41'] . ',
            "CONSIDERA_IVA_BASE_CALCULO_IIBB": "N",
            "ID_ALI_ADI_IB": null,
            "CONSIDERA_IVA_BASE_CALCULO_IIBB_ADIC": "N",
            "IB_L3": false,
            "ID_AL_FIJ_IB3": null,
            "II_IB3": false,
            "COD_CLIENTE": null,
            "COD_PROVINCIA": null,
            "FILLER": null,
            "OBSERVACIONES": null,
            "AL_FIJ_IB3": null,
            "ALI_ADI_IB": null,
            "ALI_FIJ_IB": null,
            "WEB_ADDRESS_ID": null,
            "GVA144": null
        }
    ]


}';

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

//Set your auth headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'ApiAuthorization: ' . $this->token_api_local . '',
            'Company: ' . $this->id_empresa . '',
            'Content-Type: application/json'
        ));

        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $verbose = fopen('log_curl.txt', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

// get stringified data/output. See CURLOPT_RETURNTRANSFER
        $data = curl_exec($ch);

// get info about the request
        $info = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $data2 = json_decode($data, true);

        $this->mensaje_api = $data2;

        //print_r($data2);
        //print_r($data2);
        //echo $data;
        //print_r($info);
        // close curl resource to free up system resources
        curl_close($ch);
    }

    public $clientesApiLocal;

    /* Método que devuelve Array para devolver los clientes consultados por API */

    public function consultoClientes() {
        $token = '
        {
        "ApiAuthorization": "' . $this->token_api_local . '";
        "Company: ' . $this->id_empresa . '"
        }
        ';
//setup the request, you can also use CURLOPT_URL
        $ch = curl_init('http://svrrxn:17001/Api/Get?process=2117&pageSize=10&pageIndex=0&view=RXN_Clientes');


        $data_string = '{
        "ApiAuthorization": "c894f9cf-9078-4cce-b296-888b7439e390";
        "Company: 282"
        }';

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

//Set your auth headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'ApiAuthorization: ' . $this->token_api_local . '',
            'Company: ' . $this->id_empresa . '',
        ));
//'Content-Type: application/json',
//'Authorization: Bearer ' . $token,
//'accesstoken: 1363f43b-6e58-455e-975a-f09a7baf28d2_11031'

        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $verbose = fopen('log_curl.txt', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

// get stringified data/output. See CURLOPT_RETURNTRANSFER
        $data = curl_exec($ch);

// get info about the request
        $info = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $data2 = json_decode($data, true);

//print_r($data2);

        $this->clientesApiLocal = $data2;

// close curl resource to free up system resources
        curl_close($ch);
    }

    /* Busco el valor de GVA18 para grabar el ID_GVA18 correspondiente */

    public function busco_provincia($provincia) {
        $consulta = $this->db_sql->query("SELECT * FROM GVA18 WHERE COD_PROVIN = '$provincia'");
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        return $this->tabla_provin = $fila['ID_GVA18'];
        $consulta->closeCursor();
    }

    /* Busco ID_GVA23 */

    public function busco_vendedor($vendedor) {
        $consulta = $this->db_sql->query("SELECT * FROM GVA23 WHERE COD_VENDED = '$vendedor'");
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        return $this->id_gva23 = $fila['ID_GVA23'];
        $consulta->closeCursor();
    }

    /* Busco el valor de GVA23 para dejar el vendedor asignado */

    public $tabla_cliente_cod_cliente = array();

    /* Busco el cliente original para pasarlo a la API */

    public function busco_cliente($cliente) {
        $consulta = $this->db_sql->query("SELECT GVA14.COD_CLIENT, GVA14.CUIT, GVA14.ID_GVA18, DIRECCION_ENTREGA.DIRECCION, COD_CATEGORIA_IVA, ALI_FIJ_IB, GVA41.PORCENTAJE, GVA41.COD_ALICUO FROM GVA14 INNER JOIN DIRECCION_ENTREGA ON DIRECCION_ENTREGA.COD_CLIENTE collate Modern_Spanish_CI_AI = GVA14.COD_CLIENT LEFT JOIN CATEGORIA_IVA ON CATEGORIA_IVA.ID_CATEGORIA_IVA = GVA14.ID_CATEGORIA_IVA LEFT JOIN GVA41 ON GVA41.COD_ALICUO = DIRECCION_ENTREGA.ALI_FIJ_IB WHERE TELEFONO_1 = '$cliente' AND DIRECCION_ENTREGA.HABITUAL = 'S'");
        //$consulta = $this->db_sql->query("SELECT GVA14.COD_CLIENT, GVA14.CUIT, GVA14.ID_GVA18, DIRECCION_ENTREGA.DIRECCION FROM GVA14 INNER JOIN DIRECCION_ENTREGA ON DIRECCION_ENTREGA.COD_CLIENTE collate Modern_Spanish_CI_AI = GVA14.COD_CLIENT WHERE TELEFONO_1 = '$cliente' AND DIRECCION_ENTREGA.HABITUAL = 'S'");
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        return $this->tabla_cliente_cod_cliente = $fila;
        $consulta->closeCursor();
    }

    /* Busco alícuota */

    public function busco_alicuota($ali) {
        $consulta = $this->db_sql->query("SELECT * FROM GVA41 WHERE COD_ALICUO = '$ali'");
        //$consulta = $this->db_sql->query("SELECT GVA14.COD_CLIENT, GVA14.CUIT, GVA14.ID_GVA18, DIRECCION_ENTREGA.DIRECCION FROM GVA14 INNER JOIN DIRECCION_ENTREGA ON DIRECCION_ENTREGA.COD_CLIENTE collate Modern_Spanish_CI_AI = GVA14.COD_CLIENT WHERE TELEFONO_1 = '$cliente' AND DIRECCION_ENTREGA.HABITUAL = 'S'");
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        return $this->tabla_alicuo = $fila;
        $consulta->closeCursor();
    }

    /* Método dedicado a ingresar los valores en GVA38 */

    public function ingreso_cliente_ocasionalGva38($razon_social) {
//Conecto con la base de datos
        $query = $this->db_sql;

        /* Creo la query que va a generar el ingreso de los nuevos archivos para ser leídos */
        $ingreso_valor_bd = "INSERT INTO [dbo].[GVA38]
           ([FILLER]
           ,[ALI_ADI_IB]
           ,[ALI_FIJ_IB]
           ,[ALI_NOCATE]
           ,[AL_FIJ_IB3]
           ,[COD_PROVIN]
           ,[C_POSTAL]
           ,[DOMICILIO]
           ,[E_MAIL]
           ,[IB_L]
           ,[IB_L3]
           ,[II_D]
           ,[II_IB3]
           ,[II_L]
           ,[IVA_D]
           ,[IVA_L]
           ,[LOCALIDAD]
           ,[N_COMP]
           ,[N_CUIT]
           ,[N_ING_BRUT]
           ,[N_IVA]
           ,[PORC_EXCL]
           ,[RAZON_SOCI]
           ,[SOBRE_II]
           ,[SOBRE_IVA]
           ,[TALONARIO]
           ,[TELEFONO_1]
           ,[TELEFONO_2]
           ,[TIPO]
           ,[TIPO_DOC]
           ,[T_COMP]
           ,[DESTINO_DE]
           ,[CLA_IMP_CL]
           ,[RECIBE_DE]
           ,[AUT_DE]
           ,[WEB]
           ,[COD_RUBRO]
           ,[CTA_CLI]
           ,[CTO_CLI]
           ,[IDENTIF_AFIP]
           ,[DIRECCION_ENTREGA]
           ,[CIUDAD_ENTREGA]
           ,[COD_PROVINCIA_ENTREGA]
           ,[LOCALIDAD_ENTREGA]
           ,[CODIGO_POSTAL_ENTREGA]
           ,[TELEFONO1_ENTREGA]
           ,[TELEFONO2_ENTREGA]
           ,[ID_CATEGORIA_IVA]
           ,[CONSIDERA_IVA_BASE_CALCULO_IIBB]
           ,[CONSIDERA_IVA_BASE_CALCULO_IIBB_ADIC]
           ,[MAIL_DE]
           ,[FECHA_NACIMIENTO]
           ,[SEXO])
SELECT 
            [FILLER]										   --[FILLER]
           ,[ALI_ADI_IB]									   --[ALI_ADI_IB]
           ,[ALI_FIJ_IB]									   --[ALI_FIJ_IB]
           ,[ALI_NOCATE]									   --[ALI_NOCATE]
           ,[AL_FIJ_IB3]									   --[AL_FIJ_IB3]
           ,[COD_PROVIN]									   --[COD_PROVIN] Verificar que la provincia sea la de SanLuis 
           ,[C_POSTAL]										   --[C_POSTAL]
           ,[DOMICILIO]										   --[DOMICILIO]
           ,[E_MAIL]										   --[E_MAIL]
           ,[IB_L]										   --[IB_L]
           ,[IB_L3]										   --[IB_L3]
           ,[II_D]										   --[II_D]
           ,[II_IB3]										   --[II_IB3]
           ,[II_L]									           --[II_L]
           ,[IVA_D]									           --[IVA_D]
           ,[IVA_L]									           --[IVA_L]
           ,[LOCALIDAD]										   --[LOCALIDAD]
           ,(SELECT TOP(1) ' ' + RIGHT('0000000000000' + Ltrim(Rtrim((SELECT MAX(NRO_PEDIDO) + 1 FROM GVA21))),13) FROM GVA21 WHERE TALON_PED = 6 ORDER BY ID_GVA21 DESC)--[N_COMP]
           ,[N_CUIT]										   --[N_CUIT]
           ,[N_ING_BRUT]									   --[N_ING_BRUT]
           ,[N_IVA]										   --[N_IVA]
           ,[PORC_EXCL]										   --[PORC_EXCL]
           ,CONVERT(varchar(60),'$razon_social')					           --[RAZON_SOCI]
           ,[SOBRE_II]										   --[SOBRE_II]
           ,[SOBRE_IVA]										   --[SOBRE_IVA]
           ,[TALONARIO]										   --[TALONARIO]
           ,[TELEFONO_1]									   --[TELEFONO_1]
           ,[TELEFONO_2]									   --[TELEFONO_2]
           ,[TIPO]									           --[TIPO]
           ,[TIPO_DOC]										   --[TIPO_DOC]
           ,[T_COMP]										   --[T_COMP]
           ,[DESTINO_DE]									   --[DESTINO_DE]
           ,[CLA_IMP_CL]									   --[CLA_IMP_CL]
           ,[RECIBE_DE]										   --[RECIBE_DE]
           ,[AUT_DE]										   --[AUT_DE]
           ,[WEB]										   --[WEB]
           ,[COD_RUBRO]								                   --[COD_RUBRO]
           ,[CTA_CLI]								                   --[CTA_CLI]
           ,[CTO_CLI]								                   --[CTO_CLI]
           ,[IDENTIF_AFIP]							                   --[IDENTIF_AFIP]
           ,[DIRECCION_ENTREGA]								           --[DIRECCION_ENTREGA]
           ,[CIUDAD_ENTREGA]								           --[CIUDAD_ENTREGA]
           ,[COD_PROVINCIA_ENTREGA]							           --[COD_PROVINCIA_ENTREGA]
           ,[LOCALIDAD_ENTREGA]								           --[LOCALIDAD_ENTREGA]
           ,[CODIGO_POSTAL_ENTREGA]							           --[CODIGO_POSTAL_ENTREGA]
           ,[TELEFONO1_ENTREGA]								           --[TELEFONO1_ENTREGA]
           ,[TELEFONO2_ENTREGA]								           --[TELEFONO2_ENTREGA]
           ,[ID_CATEGORIA_IVA]								           --[ID_CATEGORIA_IVA]
           ,[CONSIDERA_IVA_BASE_CALCULO_IIBB]				                           --[CONSIDERA_IVA_BASE_CALCULO_IIBB]
           ,[CONSIDERA_IVA_BASE_CALCULO_IIBB_ADIC]			                           --[CONSIDERA_IVA_BASE_CALCULO_IIBB_ADIC]
           ,[MAIL_DE]									           --[MAIL_DE]
           ,[FECHA_NACIMIENTO]								           --[FECHA_NACIMIENTO]
           ,[SEXO]								                   --[SEXO]
            FROM RXN_GVA38_MAESTRA";
        $ingreso_valor = $query->prepare($ingreso_valor_bd);
        $ingreso_valor->execute();
        $ingreso_valor->closeCursor();
    }

    /* Ingreso el pedido a la base de datos */

    public function ingreso_encabezadoGva21() {

//Conecto con la base de datos
        $query = $this->db_sql;

        /* Creo la query que va a generar el ingreso de los nuevos archivos para ser leídos */
        $ingreso_valor_bd = "INSERT INTO [dbo].[GVA21]
           ([FILLER]
           ,[APRUEBA]
           ,[CIRCUITO]
           ,[COD_CLIENT]
           ,[COD_SUCURS]
           ,[COD_TRANSP]
           ,[COD_VENDED]
           ,[COMENTARIO]
           ,[COMP_STK]
           ,[COND_VTA]
           ,[COTIZ]
           ,[ESTADO]
           ,[EXPORTADO]
           ,[FECHA_APRU]
           ,[FECHA_ENTR]
           ,[FECHA_PEDI]
           ,[HORA_APRUE]
           ,[ID_EXTERNO]
           ,[LEYENDA_1]
           ,[LEYENDA_2]
           ,[LEYENDA_3]
           ,[LEYENDA_4]
           ,[LEYENDA_5]
           ,[MON_CTE]
           ,[N_LISTA]
           ,[N_REMITO]
           ,[NRO_O_COMP]
           ,[NRO_PEDIDO]
           ,[NRO_SUCURS]
           ,[ORIGEN]
           ,[PORC_DESC]
           ,[REVISO_FAC]
           ,[REVISO_PRE]
           ,[REVISO_STK]
           ,[TALONARIO]
           ,[TALON_PED]
           ,[TOTAL_PEDI]
           ,[TIPO_ASIEN]
           ,[MOTIVO]
           ,[HORA]
           ,[COD_CLASIF]
           ,[ID_ASIENTO_MODELO_GV]
           ,[TAL_PE_ORI]
           ,[NRO_PE_ORI]
           ,[FECHA_INGRESO]
           ,[HORA_INGRESO]
           ,[USUARIO_INGRESO]
           ,[TERMINAL_INGRESO]
           ,[FECHA_ULTIMA_MODIFICACION]
           ,[HORA_ULTIMA_MODIFICACION]
           ,[USUA_ULTIMA_MODIFICACION]
           ,[TERM_ULTIMA_MODIFICACION]
           ,[ID_DIRECCION_ENTREGA]
           ,[ES_PEDIDO_WEB]
           ,[WEB_ORDER_ID]
           ,[FECHA_O_COMP]
           ,[ACTIVIDAD_COMPROBANTE_AFIP]
           ,[ID_ACTIVIDAD_EMPRESA_AFIP]
           ,[TIPO_DOCUMENTO_PAGADOR]
           ,[NUMERO_DOCUMENTO_PAGADOR]
           ,[USUARIO_TIENDA]
           ,[TIENDA]
           ,[ORDER_ID_TIENDA]
           ,[NRO_OC_COMP]
           ,[TIENDA_QUE_VENDE]
           ,[TOTAL_DESC_TIENDA]
           ,[PORCEN_DESC_TIENDA]
           ,[USUARIO_TIENDA_VENDEDOR]
           ,[ID_NEXO_PEDIDOS_ORDEN]
           ,[ID_GVA01]
           ,[ID_GVA10]
           ,[ID_GVA14]
           ,[ID_GVA23]
           ,[ID_GVA24]
           ,[ID_GVA38]
           ,[ID_GVA43_TALON_PED]
           ,[ID_GVA81]
           ,[ID_SUCURSAL]
           ,[METODO_EXPORTACION]
           ,[NRO_SUCURSAL_DESTINO_PEDIDO])
SELECT 
		   	[FILLER]									   -- [FILLER]								
           ,[APRUEBA]									   --,[APRUEBA]								
           ,[CIRCUITO]									   --,[CIRCUITO]								
           ,[COD_CLIENT]								   --,[COD_CLIENT]							
           ,[COD_SUCURS]								   --,[COD_SUCURS]							
           ,[COD_TRANSP]								   --,[COD_TRANSP]							
           ,[COD_VENDED]								   --,[COD_VENDED]							
           ,[COMENTARIO]								   --,[COMENTARIO]							
           ,[COMP_STK]									   --,[COMP_STK]								
           ,[COND_VTA]									   --,[COND_VTA]								
           ,[COTIZ]										   --,[COTIZ]									
           ,[ESTADO]									   --,[ESTADO]								
           ,[EXPORTADO]									   --,[EXPORTADO]								
           ,[FECHA_APRU]								   --,[FECHA_APRU]							
           ,[FECHA_ENTR]								   --,[FECHA_ENTR]							
           ,(SELECT GETDATE())								   --,[FECHA_PEDI]							
           ,[HORA_APRUE]								   --,[HORA_APRUE]							
           ,[ID_EXTERNO]								   --,[ID_EXTERNO]							
           ,[LEYENDA_1]									   --,[LEYENDA_1]								
           ,[LEYENDA_2]									   --,[LEYENDA_2]								
           ,[LEYENDA_3]									   --,[LEYENDA_3]								
           ,[LEYENDA_4]									   --,[LEYENDA_4]								
           ,[LEYENDA_5]									   --,[LEYENDA_5]								
           ,[MON_CTE]									   --,[MON_CTE]								
           ,[N_LISTA]									   --,[N_LISTA]								
           ,[N_REMITO]									   --,[N_REMITO]								
           ,[NRO_O_COMP]								   --,[NRO_O_COMP]							
           ,(SELECT TOP(1) ' ' + RIGHT('0000000000000' + Ltrim(Rtrim((SELECT MAX(NRO_PEDIDO) + 1 FROM GVA21))),13) FROM GVA21 ORDER BY ID_GVA21 DESC)--,[NRO_PEDIDO]							
           ,[NRO_SUCURS]								   --,[NRO_SUCURS]							
           ,[ORIGEN]									   --,[ORIGEN]								
           ,[PORC_DESC]									   --,[PORC_DESC]								
           ,[REVISO_FAC]								   --,[REVISO_FAC]							
           ,[REVISO_PRE]								   --,[REVISO_PRE]							
           ,[REVISO_STK]								   --,[REVISO_STK]							
           ,[TALONARIO]									   --,[TALONARIO]								
           ,[TALON_PED]									   --,[TALON_PED]								
           ,[TOTAL_PEDI]								   --,[TOTAL_PEDI]							
           ,[TIPO_ASIEN]								   --,[TIPO_ASIEN]							
           ,[MOTIVO]									   --,[MOTIVO]								
           ,[HORA]										   --,[HORA]									
           ,[COD_CLASIF]								   --,[COD_CLASIF]							
           ,[ID_ASIENTO_MODELO_GV]						   --,[ID_ASIENTO_MODELO_GV]					
           ,[TAL_PE_ORI]								   --,[TAL_PE_ORI]							
           ,[NRO_PE_ORI]								   --,[NRO_PE_ORI]							
           ,(SELECT GETDATE())								   --,[FECHA_INGRESO]							
           ,[HORA_INGRESO]								   --,[HORA_INGRESO]							
           ,[USUARIO_INGRESO]							   --,[USUARIO_INGRESO]						
           ,[TERMINAL_INGRESO]							   --,[TERMINAL_INGRESO]						
           ,[FECHA_ULTIMA_MODIFICACION]					   --,[FECHA_ULTIMA_MODIFICACION]				
           ,[HORA_ULTIMA_MODIFICACION]					   --,[HORA_ULTIMA_MODIFICACION]				
           ,[USUA_ULTIMA_MODIFICACION]					   --,[USUA_ULTIMA_MODIFICACION]				
           ,[TERM_ULTIMA_MODIFICACION]					   --,[TERM_ULTIMA_MODIFICACION]				
           ,[ID_DIRECCION_ENTREGA]						   --,[ID_DIRECCION_ENTREGA]					
           ,[ES_PEDIDO_WEB]								   --,[ES_PEDIDO_WEB]							
           ,[WEB_ORDER_ID]								   --,[WEB_ORDER_ID]							
           ,[FECHA_O_COMP]								   --,[FECHA_O_COMP]							
           ,[ACTIVIDAD_COMPROBANTE_AFIP]				   --,[ACTIVIDAD_COMPROBANTE_AFIP]			
           ,[ID_ACTIVIDAD_EMPRESA_AFIP]					   --,[ID_ACTIVIDAD_EMPRESA_AFIP]				
           ,[TIPO_DOCUMENTO_PAGADOR]					   --,[TIPO_DOCUMENTO_PAGADOR]				
           ,[NUMERO_DOCUMENTO_PAGADOR]					   --,[NUMERO_DOCUMENTO_PAGADOR]				
           ,[USUARIO_TIENDA]							   --,[USUARIO_TIENDA]						
           ,[TIENDA]									   --,[TIENDA]								
           ,[ORDER_ID_TIENDA]							   --,[ORDER_ID_TIENDA]						
           ,[NRO_OC_COMP]								   --,[NRO_OC_COMP]							
           ,[TIENDA_QUE_VENDE]							   --,[TIENDA_QUE_VENDE]						
           ,[TOTAL_DESC_TIENDA]							   --,[TOTAL_DESC_TIENDA]						
           ,[PORCEN_DESC_TIENDA]						   --,[PORCEN_DESC_TIENDA]					
           ,[USUARIO_TIENDA_VENDEDOR]					   --,[USUARIO_TIENDA_VENDEDOR]				
           ,[ID_NEXO_PEDIDOS_ORDEN]						   --,[ID_NEXO_PEDIDOS_ORDEN]					
           ,[ID_GVA01]									   --,[ID_GVA01]								
           ,[ID_GVA10]									   --,[ID_GVA10]								
           ,[ID_GVA14]									   --,[ID_GVA14]								
           ,[ID_GVA23]									   --,[ID_GVA23]								
           ,[ID_GVA24]									   --,[ID_GVA24]								
           ,(SELECT MAX(ID_GVA38) FROM GVA38)			   --,[ID_GVA38]								
           ,[ID_GVA43_TALON_PED]						   --,[ID_GVA43_TALON_PED]					
           ,[ID_GVA81]									   --,[ID_GVA81]								
           ,[ID_SUCURSAL]								   --,[ID_SUCURSAL]							
           ,[METODO_EXPORTACION]						   --,[METODO_EXPORTACION]					
           ,[NRO_SUCURSAL_DESTINO_PEDIDO]				   --,[NRO_SUCURSAL_DESTINO_PEDIDO]			
FROM RXN_GVA21_MAESTRA";
        $ingreso_valor = $query->prepare($ingreso_valor_bd);
        $ingreso_valor->execute();
        $ingreso_valor->closeCursor();
    }

    /* Método que actualiza el total y descuento */

    public function actualizoTotalDescuentoGva21($total, $porc_descuento) {
//Conecto con la base de datos
        $query = $this->db_sql;

        /* Creo la query que va a generar el ingreso de los nuevos archivos para ser leídos */
        $ingreso_valor_bd = "UPDATE GVA21 SET TOTAL_PEDI = $total, PORC_DESC = $porc_descuento WHERE NRO_PEDIDO = (SELECT MAX(NRO_PEDIDO) AS NRO_PEDIDO FROM GVA21)";
        $ingreso_valor = $query->prepare($ingreso_valor_bd);
        $ingreso_valor->execute();
        $ingreso_valor->closeCursor();
    }

    /* Actualizo la RutaXML */

    public function actualizoRutaXml($ruta_xml) {
//Conecto con la base de datos
        $query = $this->db_sql;

        /* Creo la query que va a generar el ingreso de los nuevos archivos para ser leídos */
        $ingreso_valor_bd = "UPDATE RXN_PARAMETROS SET RUTAXML = '$ruta_xml'";
        $ingreso_valor = $query->prepare($ingreso_valor_bd);
        $ingreso_valor->execute();
        $ingreso_valor->closeCursor();
    }

    /* Actualizo el XML ingresado */

    public function actualizoXmlIngresado($nombre_archivo) {

//Conecto con la base de datos
        $query = $this->db_sql;

        /* Creo la query que va a generar el ingreso de los nuevos archivos para ser leídos */
        $ingreso_valor_bd = "UPDATE RXN_XML SET ESTADO  = 'P' WHERE NOMBRE_ARCHIVO = '$nombre_archivo'";
        $ingreso_valor = $query->prepare($ingreso_valor_bd);
        $ingreso_valor->execute();
        $ingreso_valor->closeCursor();
    }

    /* Genero el ingreso del cuerpo para GVA03 */

    public function ingreso_cuerpoGva03($cantidad, $precio) {
//Conecto con la base de datos
        $query = $this->db_sql;

        /* Creo la query que va a generar el ingreso de los nuevos archivos para ser leídos */
        $ingreso_valor_bd = "INSERT INTO [dbo].[GVA03]
           ([FILLER]
           ,[CAN_EQUI_V]
           ,[CANT_A_DES]
           ,[CANT_A_FAC]
           ,[CANT_PEDID]
           ,[CANT_PEN_D]
           ,[CANT_PEN_F]
           ,[COD_ARTICU]
           ,[DESCUENTO]
           ,[N_RENGLON]
           ,[NRO_PEDIDO]
           ,[PEN_REM_FC]
           ,[PEN_FAC_RE]
           ,[PRECIO]
           ,[TALON_PED]
           ,[COD_CLASIF]
           ,[CANT_A_DES_2]
           ,[CANT_A_FAC_2]
           ,[CANT_PEDID_2]
           ,[CANT_PEN_D_2]
           ,[CANT_PEN_F_2]
           ,[PEN_REM_FC_2]
           ,[PEN_FAC_RE_2]
           ,[ID_MEDIDA_VENTAS]
           ,[ID_MEDIDA_STOCK_2]
           ,[ID_MEDIDA_STOCK]
           ,[UNIDAD_MEDIDA_SELECCIONADA]
           ,[COD_ARTICU_KIT]
           ,[RENGL_PADR]
           ,[PROMOCION]
           ,[PRECIO_ADICIONAL_KIT]
           ,[KIT_COMPLETO]
           ,[INSUMO_KIT_SEPARADO]
           ,[PRECIO_LISTA]
           ,[PRECIO_BONIF]
           ,[DESCUENTO_PARAM]
           ,[PRECIO_FECHA]
           ,[FECHA_MODIFICACION_PRECIO]
           ,[USUARIO_MODIFICACION_PRECIO]
           ,[TERMINAL_MODIFICACION_PRECIO]
           ,[ID_NEXO_PEDIDOS_RENGLON_ORDEN]
           ,[CANT_A_DES_EXPORTADA]
           ,[CANT_A_FAC_EXPORTADA]
           ,[CANT_A_DES_2_EXPORTADA]
           ,[CANT_A_FAC_2_EXPORTADA])
SELECT      [FILLER]
           ,[CAN_EQUI_V]   									--[CAN_EQUI_V]
           ,$cantidad   									--[CANT_A_DES]
           ,$cantidad   									--[CANT_A_FAC]
           ,$cantidad   									--[CANT_PEDID]
           ,$cantidad   									--[CANT_PEN_D]
           ,$cantidad   									--[CANT_PEN_F]
           ,[COD_ARTICU]									--[COD_ARTICU]
           ,[DESCUENTO]										--[DESCUENTO]
           ,(SELECT TOP(1)CASE WHEN GVA03.NRO_PEDIDO IS NULL THEN 1 ELSE ROW_NUMBER() OVER(PARTITION BY  GVA03.NRO_PEDIDO ORDER BY  GVA03.NRO_PEDIDO) + 1 END AS N_RENGLON FROM GVA21 LEFT OUTER JOIN GVA03 ON GVA03.NRO_PEDIDO = GVA21.NRO_PEDIDO WHERE GVA21.NRO_PEDIDO =  (SELECT MAX(NRO_PEDIDO) FROM GVA21) ORDER BY N_RENGLON DESC)--[N_RENGLON]
           ,(SELECT MAX(NRO_PEDIDO) FROM GVA21 WHERE TALON_PED = 6)--[NRO_PEDIDO] INGRESADO
           ,[PEN_REM_FC]								         --[PEN_REM_FC]
           ,[PEN_FAC_RE]								         --[PEN_FAC_RE]
           ,$precio									         --[PRECIO]
           ,[TALON_PED]									         --[TALON_PED]
           ,[COD_CLASIF]								         --[COD_CLASIF]
           ,[CANT_A_DES_2]								         --[CANT_A_DES_2]
           ,[CANT_A_FAC_2]								         --[CANT_A_FAC_2]
           ,[CANT_PEDID_2]								         --[CANT_PEDID_2]
           ,[CANT_PEN_D_2]								         --[CANT_PEN_D_2]
           ,[CANT_PEN_F_2]								         --[CANT_PEN_F_2]
           ,[PEN_REM_FC_2]								         --[PEN_REM_FC_2]
           ,[PEN_FAC_RE_2]								         --[PEN_FAC_RE_2]
           ,[ID_MEDIDA_VENTAS]									 --[ID_MEDIDA_VENTAS]
           ,[ID_MEDIDA_STOCK_2]									 --[ID_MEDIDA_STOCK_2]
           ,[ID_MEDIDA_STOCK]									 --[ID_MEDIDA_STOCK]
           ,[UNIDAD_MEDIDA_SELECCIONADA]						         --[UNIDAD_MEDIDA_SELECCIONADA]
           ,[COD_ARTICU_KIT]									 --[COD_ARTICU_KIT]
           ,[RENGL_PADR]									 --[RENGL_PADR]
           ,[PROMOCION]										 --[PROMOCION]
           ,[PRECIO_ADICIONAL_KIT]								 --[PRECIO_ADICIONAL_KIT]
           ,[KIT_COMPLETO]									 --[KIT_COMPLETO]
           ,[INSUMO_KIT_SEPARADO]								 --[INSUMO_KIT_SEPARADO]
           ,[PRECIO_LISTA]									 --[PRECIO_LISTA]
           ,$precio									         --[PRECIO_BONIF]
           ,[DESCUENTO_PARAM]									 --[DESCUENTO_PARAM]
           ,[PRECIO_FECHA]								         --[PRECIO_FECHA]
           ,[FECHA_MODIFICACION_PRECIO]							         --[FECHA_MODIFICACION_PRECIO]
           ,[USUARIO_MODIFICACION_PRECIO]						         --[USUARIO_MODIFICACION_PRECIO]
           ,[TERMINAL_MODIFICACION_PRECIO]						         --[TERMINAL_MODIFICACION_PRECIO]
           ,[ID_NEXO_PEDIDOS_RENGLON_ORDEN]						         --[ID_NEXO_PEDIDOS_RENGLON_ORDEN]
           ,[CANT_A_DES_EXPORTADA]								 --[CANT_A_DES_EXPORTADA]
           ,[CANT_A_FAC_EXPORTADA]								 --[CANT_A_FAC_EXPORTADA]
           ,[CANT_A_DES_2_EXPORTADA]							         --[CANT_A_DES_2_EXPORTADA]
           ,[CANT_A_FAC_2_EXPORTADA]							         --[CANT_A_FAC_2_EXPORTADA]
           FROM RXN_GVA03_MAESTRA";
        $ingreso_valor = $query->prepare($ingreso_valor_bd);
        $ingreso_valor->execute();
        $ingreso_valor->closeCursor();
    }

    /* Genero el ingreso para las descripciones adicionales */

    public function ingreso_cuerpoGva45($descrip, $codigo) {
        //Conecto con la base de datos
        $query = $this->db_sql;

        /* Creo la query que va a generar el ingreso de los nuevos archivos para ser leídos */
        $ingreso_valor_bd = "INSERT INTO [dbo].[GVA45]
           ([FILLER]
           ,[COD_MODELO]
           ,[DESC]
           ,[DESC_ADIC]
           ,[N_COMP]
           ,[N_RENGLON]
           ,[TALONARIO]
           ,[T_COMP])
        SELECT [FILLER]
           ,[COD_MODELO]				--[COD_MODELO]
           ,CONVERT(varchar,'$descrip')			--[DESC]
           ,'$codigo'					--[DESC_ADIC]
           ,(SELECT MAX(NRO_PEDIDO) FROM GVA21 WHERE TALON_PED = 6)--[N_COMP]
           ,(SELECT TOP(1)CASE WHEN GVA45.N_COMP IS NULL THEN 1 ELSE ROW_NUMBER() OVER(PARTITION BY  GVA45.N_COMP ORDER BY  GVA45.N_COMP) + 1 END AS N_RENGLON FROM GVA21 LEFT OUTER JOIN GVA45 ON GVA45.N_COMP = GVA21.NRO_PEDIDO WHERE GVA21.NRO_PEDIDO =  (SELECT MAX(NRO_PEDIDO) FROM GVA21) AND GVA21.TALON_PED = 6 ORDER BY N_RENGLON DESC)--[N_RENGLON]
           ,[TALONARIO]					--[TALONARIO]
           ,[T_COMP]					--[T_COMP]
        FROM RXN_GVA45_MAESTRA";

        $ingreso_valor = $query->prepare($ingreso_valor_bd);
        $ingreso_valor->execute();
        $ingreso_valor->closeCursor();
    }

}

?>
