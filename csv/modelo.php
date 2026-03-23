<?php

require("vista.php");

class modelo extends vista
{

    // Variables de uso de la aplicación
    private $db;
    public $tipoDoc;
    public $id_gva23;
    public $tabla_provin;
    public $tabla_alicuo;
    private $db_sql;
    public $token_api;
    public $token_api_local;
    public $id_empresa;
    public $tot_para_ped;
    public $token;
    public $n_fac_reproceso;
    public $nombre_archivo_ini;
    public $nombre_archivo;
    public $nombre_archivo_enc_pedidos;
    public $nombre_archivo_art;
    public $talon_ped;
    public $nombre_archivo_cue_pedidos;
    public $selectGva14;
    public $maxIdGva14;
    public $ruta_xml;
    public $ctrlNoProcesados;
    public $ctrlPediRxnApiCtrl;
    public $param_numeracion;
    public $id_orden;
    public $param;
    public $ctrl_articu;
    public $fechaFac;
    public $articulos;
    public $resultados_array_gen;
    public $fechaNormalizada;
    public $art_total_precio;
    public $tot_arts_sin_impuestos;
    public $art_total_solo_iva_snc;
    public $art_total_perc;
    public $art_total_10_50;
    public $p_imp_importe;
    public $art_total_iva;
    public $base_seleccionada;
    public $numero;
    public $numeroConDecimales;
    public  $numeroFinal;
    public $numeroFormateado;
    public $tot_bonificaciones_iva;
    public $tot_bonif_sin_impuestos;
    public $tot_bonif_subtotal;
    public $tot_bonificaciones;
    public $tot_bonif_adicional;
    public $tot_bonif_adicional_iva;
    public $tot_bonif_adicional_sin_impuestos;
    public $tot_bonif_adicional_subtotal;
    public $tot_practicosas;
    public $tot_practicosas_iva;
    public $tot_practicosas_sin_impuestos;
    public $tot_practicosas_subtotal;
    public $tot_gastos_administrativos;
    public $tot_gastos_administrativos_iva;
    public $tot_gastos_administrativos_sin_impuestos;
    public $tot_gastos_administrativos_subtotal;
    public $tot_bonif_ali;
    public $tot_practicosas_10_5;
    public $tot_practicosas_ali;
    public $tot_gastos_administrativos_10_5;
    public $tot_gastos_administrativos_ali;
    public $tot_bonif_adicional_10_5;
    public $tot_bonif_adicional_ali;
    public $tot_bonif_10_5;
    public $stringConvertido;


    public function __construct()
    {
        /* String de conexion con la base de datos */
        //require_once("../ConectarM.php");
        //$this->db = Conectar::conexion();
        //Base fija seleccionada - utilizada en los parametros de copia que usan los SP de la base de datos origen.
        $this->base_seleccionada = 'LADY_WAY_SRL';

        require_once("../Conectar.php");
        require_once("../ConectarBase.php");
        $this->db_sql = Conectar_SQL_static::conexion_origen();

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

    public function selec_talonario()
    {
        $consulta = $this->db_sql->query("SELECT GVA43.TALONARIO, GVA43.DESCRIP FROM GVA43 INNER JOIN RXN_PARAMETROS ON GVA43.TALONARIO = RXN_PARAMETROS.TALON_PED WHERE GVA43.COMPROB = 'PED'");
        while ($filas = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $this->talonario[] = $filas;
        }
        return $this->talonario;
        $consulta->closeCursor();
    }

    /* Se recorren todas las facturas para el reproceso */

    public function devuelvo_talonarios()
    {
        $consulta = $this->db_sql->query("SELECT N_COMP FROM GVA12DE WHERE ESTADO_DE IN ('P','R')");
        while ($filas = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $this->n_fac_reproceso[] = $filas;
        }
        return $this->n_fac_reproceso;
        $consulta->closeCursor();
    }

    /* Método para actualizar los comprobantes para reprocesar */

    public function actualizo_para_reproceso()
    {
        $query = $this->db_sql;
        $this->devuelvo_talonarios();

        foreach ($this->n_fac_reproceso as $reproceso) {

            /* Creo la query que va a generar el ingreso de los nuevos archivos para ser leídos */
            $ingreso_valor_bd = "UPDATE RXN_API_CTRL SET GRABO = 0 WHERE MENSAJE_API LIKE '%" . $reproceso['N_COMP'] . "%'";
            $ingreso_valor = $query->prepare($ingreso_valor_bd);
            $ingreso_valor->execute();
            $ingreso_valor->closeCursor();
        }
    }

    public function leo_ingreso_directorio_csv()
    {

        //Conecto con la base de datos
        $query = $this->db_sql;

        //Leo los archivos del directorio
        $ruta_de_la_carpeta = $this->leoParametroBd('RUTAXML') . '/';
        if ($handler = opendir($ruta_de_la_carpeta)) {
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
            }
            closedir($handler);
        }
    }

    /* Leo los archivos para mostrarlo en la pantalla principal */

    public function leoArchivosBd()
    {

        $consulta = $this->db_sql->query("SELECT NOMBRE_ARCHIVO FROM RXN_CSV WHERE ESTADO = 'I'");

        while ($filas = $consulta->fetch(PDO::FETCH_ASSOC)) {

            $this->nombre_archivo_ini[] = $filas;
        }

        if (isset($this->nombre_archivo_ini)) {
            return $this->nombre_archivo_ini;
        }

        $consulta->closeCursor();
    }

    public function leoArchivosBdReproceso()
    {

        $consulta = $this->db_sql->query("SELECT NOMBRE_ARCHIVO FROM RXN_API_CTRL WHERE GRABO = 0 AND NOMBRE_ARCHIVO != '' AND NOMBRE_ARCHIVO NOT LIKE 'CLI%' AND NOMBRE_ARCHIVO NOT LIKE 'ARTS%' AND NOMBRE_ARCHIVO NOT LIKE '%@%' GROUP BY NOMBRE_ARCHIVO");

        while ($filas = $consulta->fetch(PDO::FETCH_ASSOC)) {

            $this->nombre_archivo_ini[] = $filas;
        }

        if (isset($this->nombre_archivo_ini)) {
            return $this->nombre_archivo_ini;
        }

        $consulta->closeCursor();
    }

    /* Método que devuelve la vista de la ventana del reproceso */


    /* Busco todos los archivos leídos en la base de datos para luego recorrerlos */

    public function leoArchivosBdCli()
    {

        $consulta = $this->db_sql->query("SELECT NOMBRE_ARCHIVO FROM RXN_CSV WHERE NOMBRE_ARCHIVO NOT IN ('.','..') AND NOMBRE_ARCHIVO LIKE '%.csv%' AND ESTADO = 'I' AND NOMBRE_ARCHIVO LIKE 'CLI%'");

        while ($filas = $consulta->fetch(PDO::FETCH_ASSOC)) {

            $this->nombre_archivo[] = $filas;
        }

        if (isset($this->nombre_archivo)) {
            $resultado = $this->nombre_archivo;
        } else {
            $resultado = null;
        }
        $consulta->closeCursor();
        return $resultado;
    }

    /* Leo el archivo de artículos */

    public function leoArchivosBdArt()
    {

        $consulta = $this->db_sql->query("SELECT NOMBRE_ARCHIVO FROM RXN_CSV WHERE NOMBRE_ARCHIVO NOT IN ('.','..') AND NOMBRE_ARCHIVO LIKE '%.csv%' AND ESTADO = 'I' AND NOMBRE_ARCHIVO LIKE 'ARTS%'");

        while ($filas = $consulta->fetch(PDO::FETCH_ASSOC)) {

            $this->nombre_archivo_art[] = $filas;
        }
        /* Controlo que haya un nombre de archivo que sea devuelto para pasar el array */
        if (isset($this->nombre_archivo_art)) {
            $resultado = $this->nombre_archivo_art;
        } else {
            $resultado = null;
        }

        $consulta->closeCursor();
        return $resultado;
    }

    /* Leo el archivo de encabezados de pedidos */

    public function leoArchivosBdEncPed()
    {

        $consulta = $this->db_sql->query("SELECT NOMBRE_ARCHIVO FROM RXN_CSV WHERE NOMBRE_ARCHIVO NOT IN ('.','..') AND (NOMBRE_ARCHIVO LIKE 'C20%' OR NOMBRE_ARCHIVO LIKE 'CABE20%') AND NOMBRE_ARCHIVO LIKE '%.csv' AND ESTADO = 'I'");

        while ($filas = $consulta->fetch(PDO::FETCH_ASSOC)) {

            $this->nombre_archivo_enc_pedidos[] = $filas;
        }

        if (isset($this->nombre_archivo_enc_pedidos)) {
            $resultado = $this->nombre_archivo_enc_pedidos;
        } else {
            $resultado = null;
        }

        $consulta->closeCursor();
        return $resultado;
    }

    public function leoArchivosBdNoProcesados()
    {

        $consulta = $this->db_sql->query("SELECT NOMBRE_ARCHIVO FROM RXN_API_CTRL WHERE GRABO = 0 AND NOMBRE_ARCHIVO != '' AND NOMBRE_ARCHIVO NOT LIKE 'CLI%' AND NOMBRE_ARCHIVO NOT LIKE 'ARTS%' AND NOMBRE_ARCHIVO NOT LIKE '%@%' GROUP BY NOMBRE_ARCHIVO");

        while ($filas = $consulta->fetch(PDO::FETCH_ASSOC)) {

            $this->nombre_archivo_enc_pedidos[] = $filas;
        }

        if (isset($this->nombre_archivo_enc_pedidos)) {
            return $this->nombre_archivo_enc_pedidos;
        }

        $consulta->closeCursor();
    }

    /* Busco archivos no procesados */



    /* Leo el archivo del cuerpo del pedido */

    public function leoArchivosBdCuePed($archivo)
    {
        unset($this->nombre_archivo_cue_pedidos);
        $this->nombre_archivo_cue_pedidos = array();

        /* Sí el archivo es cabe ejecuto una consulta y si es c (comunes) ejecuto la otra */
        $ctrl_nombre = substr($archivo, 0, 4);
        if ($ctrl_nombre === 'cabe') {
            $consulta = $this->db_sql->query("SELECT NOMBRE_ARCHIVO FROM RXN_CSV WHERE NOMBRE_ARCHIVO LIKE 'deta20%' AND NOMBRE_ARCHIVO = 'deta' + SUBSTRING('$archivo',5,200)");
            //$consulta = $this->db_sql->query("SELECT NOMBRE_ARCHIVO FROM RXN_CSV WHERE NOMBRE_ARCHIVO LIKE 'D20%' AND NOMBRE_ARCHIVO = 'D' + SUBSTRING('$archivo',2,200) OR NOMBRE_ARCHIVO LIKE 'DETA20%' OR NOMBRE_ARCHIVO = 'DETA' + SUBSTRING('$archivo',5,200) AND ESTADO = 'I'");
        } else {
            $consulta = $this->db_sql->query("SELECT NOMBRE_ARCHIVO FROM RXN_CSV WHERE NOMBRE_ARCHIVO LIKE 'D20%' AND NOMBRE_ARCHIVO = 'D' + SUBSTRING('$archivo',2,200)");
        }
        //$consulta = $this->db_sql->query("SELECT NOMBRE_ARCHIVO FROM RXN_CSV WHERE NOMBRE_ARCHIVO LIKE 'D20%' AND NOMBRE_ARCHIVO LIKE '%.csv'");
        //$consulta = $this->db_sql->query("SELECT NOMBRE_ARCHIVO FROM RXN_CSV WHERE NOMBRE_ARCHIVO NOT IN ('.','..') AND NOMBRE_ARCHIVO LIKE '%.csv%' AND ESTADO = 'I' AND NOMBRE_ARCHIVO LIKE 'D20%' AND NOMBRE_ARCHIVO LIKE (SELECT TOP(1)'%' + SUBSTRING(NOMBRE_ARCHIVO, 12, 3) + '%' AS NOMBRE_RECORTADO FROM RXN_CSV WHERE NOMBRE_ARCHIVO NOT IN ('.','..') AND NOMBRE_ARCHIVO LIKE '%.csv%' AND ESTADO = 'I' AND NOMBRE_ARCHIVO LIKE 'C20%')");
        while ($filas = $consulta->fetch(PDO::FETCH_ASSOC)) {

            $this->nombre_archivo_cue_pedidos[] = $filas;
        }

        return $this->nombre_archivo_cue_pedidos;


        $consulta->closeCursor();
    }

    /* Devuelvo el valor del talonario seleccionado */

    public function devuelvoValorTalonSelec()
    {
        $consulta = $this->db_sql->query("SELECT TALON_PED FROM RXN_PARAMETROS");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        return $this->talon_ped = $filas['TALON_PED'];


        $consulta->closeCursor();
    }

    /* Busco los datos de Token */

    public function devuelvoTokens()
    {
        $consulta = $this->db_sql->query("SELECT * FROM RXN_PARAMETROS");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        return $this->token = $filas;


        $consulta->closeCursor();
    }

    /* Devuelvo el último ID de GVA14 para llevarlo a los clientes */

    public function maxIdGva14()
    {
        $consulta = $this->db_sql->query("SELECT CASE WHEN MAX(CONVERT(int, COD_CLIENT)) IS NULL THEN 1 ELSE MAX(CONVERT(int, COD_CLIENT)) END AS COD_CLIENT FROM GVA14");
        //SELECT TOP(1) CASE WHEN COD_CLIENT IS NULL THEN 1 ELSE COD_CLIENT END AS COD_CLIENT FROM GVA14 ORDER BY ID_GVA14 DESC
        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        return $this->maxIdGva14 = $filas;


        $consulta->closeCursor();
    }

    /* Busco valores de Tipo Doc en la tabla de Tango */

    public function buscoTipoDoc($cod_tipo_doc)
    {
        $consulta = $this->db_sql->query("SELECT * FROM TIPO_DOCUMENTO_GV WHERE COD_TIPO_DOCUMENTO_GV = '$cod_tipo_doc'");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        return $this->tipoDoc = $filas;

        $consulta->closeCursor();
    }

    /* Devuelvo todos los valores del GVA14 en princpio para controlar los CUIT de los clientes */

    public function selectGva14($cod_client)
    {
        $consulta = $this->db_sql->query("SELECT TOP(1)* FROM GVA14 WHERE TELEFONO_1 = '$cod_client'");
        //AND TIPO_DOC IN ($tipo_doc,96)

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        return $this->selectGva14 = $filas;

        $consulta->closeCursor();
    }

    /* Vacío tabla de clientes para el momento del ingreso y en su facturación poder cargar la configuración de los artículos */

    public function vacioClientes()
    {
        $consulta = $this->db_sql->query("TRUNCATE TABLE RXN_IMP_CLI");

        $consulta->closeCursor();
    }

    /* Controlo el número de pedido en la BD para no re-procesar en caso de existir por tema re-ingreso de pedidos por error en cliente de CSV */

    public function ctrlPedi($nro_pedido, $nombre_archivo)
    {
        $consulta = $this->db_sql->query("SELECT TOP(1)* FROM RXN_API_CTRL WHERE COD_COMP = '$nro_pedido' AND NOMBRE_ARCHIVO = '$nombre_archivo' AND GRABO = 1 ORDER BY ID DESC");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        $consulta->closeCursor();

        if (!$filas) {
            // Si no hay resultados, devolver un array vacío
            $filas = [];
        }

        return $this->ctrlPediRxnApiCtrl = $filas;
    }

    /* Método que solo devuelve el pedido con error */

    public function ctrlPediError($nro_pedido)
    {
        $consulta = $this->db_sql->query("SELECT TOP(1)* FROM RXN_API_CTRL WHERE COD_COMP = '$nro_pedido' AND GRABO = 0 AND PROCESO NOT IN ('ARTICULOS','CLIENTES') ORDER BY ID DESC");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        return $this->ctrlPediRxnApiCtrl = $filas;


        $consulta->closeCursor();
    }

    /* Recorro los pedidos no procesados para re-intentar */

    public function ctrlNoProcesados()
    {
        $consulta = $this->db_sql->query("SELECT * FROM RXN_API_CTRL WHERE GRABO = 0 AND PROCESO NOT IN ('ARTICULOS','CLIENTES') ORDER BY ID DESC");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        return $this->ctrlNoProcesados = $filas;


        $consulta->closeCursor();
    }

    /* Llamo a la ruta XML configurada */

    public function rutaXmlConfigurada()
    {
        $consulta = $this->db_sql->query("SELECT RUTAXML FROM RXN_PARAMETROS");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        return $this->ruta_xml = $filas['RUTAXML'];


        $consulta->closeCursor();
    }

    /* Ingreso el pedido a la tabla de control */

    public function ingresoPedidoControl($pedido, $cliente)
    {
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

    public function ingresoClieAlic($cliente, $id_alicuota, $alic_perc)
    {
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

    public function ingresoMensajesApi($cod_comp, $proceso, $mensaje, $grabo, $cod_client, $nombre_archivo, $reintento, $detalle_api, $id_cliente, $razon_social)
    {
        $query = $this->db_sql;
        $ingreso = "INSERT INTO [dbo].[RXN_API_CTRL]
           ([COD_COMP]
           ,[PROCESO]
           ,[MENSAJE_API]
           ,[FECHA],
           [GRABO],
           [COD_CLIENT],
           [NOMBRE_ARCHIVO],
           [REINTENTOS],
           [DETALLE_API],
           [ID_CLIENTE],
           [RAZON_SOCIAL])
           VALUES(
           :COD_COMP,
           :PROCESO,
           :MENSAJE,
           getdate(),
           :GRABO,
           :COD_CLIENT,
           :NOMBRE_ARCHIVO,
           :REINTENTOS,
           :DETALLE_API,
           :ID_CLIENTE,
           :RAZON_SOCIAL
           )";

        $consulta = $query->prepare($ingreso);
        $consulta->execute(array(":COD_COMP" => $cod_comp, ":PROCESO" => $proceso, ":MENSAJE" => $mensaje, ":GRABO" => $grabo, ":COD_CLIENT" => $cod_client, ":NOMBRE_ARCHIVO" => $nombre_archivo, ":REINTENTOS" => $reintento, ":DETALLE_API" => $detalle_api, ":ID_CLIENTE" => $id_cliente, ":RAZON_SOCIAL" => $razon_social));
        //$filas = $consulta->fetch(PDO::FETCH_ASSOC);
        $consulta->closeCursor();
    }

    /* Actualizo el archivo de cliente importado */

    public function actualizoArchivoArticuloImpo()
    {
        $query = $this->db_sql;
        $update = "UPDATE RXN_CSV SET ESTADO = 'P' WHERE NOMBRE_ARCHIVO LIKE 'ARTS%'";
        $consulta = $query->prepare($update);
        $consulta->execute();
        $consulta->closeCursor();
    }

    /* Actualizo los valores de GVA14 en el caso que corresponda en caso de CUIT - CUIL, */

    public function actTipoDocCliente($cod_client, $tipo_doc, $cuit_cuil_dni, $correo, $cli_interno)
    {
        $query = $this->db_sql;
        /* Saco los guiones */
        $cuit_cuil_dni_sin_guiones = str_replace("-", "", $cuit_cuil_dni);
        /* Busco el tipo doc  C.U.I.L.*/
        $this->buscoTipoDoc($tipo_doc);

        $id_t_d = $this->tipoDoc['ID_TIPO_DOCUMENTO_GV'];
        if ($correo == 'default@mail.com') {
            /* Sin correo */
            $update = "UPDATE GVA14 SET TIPO_DOC = $tipo_doc, ID_TIPO_DOCUMENTO_GV = $id_t_d, CUIT = '$cuit_cuil_dni_sin_guiones' WHERE TELEFONO_1 = '$cli_interno'";
            error_log('[' . date('Y-m-d H:i:s') . '] actTipoDocCliente | Tipo Doc: ' . $tipo_doc . ' | ID_TIPO_DOC: ' . $id_t_d . ' | CUIT: ' . $cuit_cuil_dni_sin_guiones . ' | Cod.Cliente: ' . $cli_interno);
        } else {
            $update = "UPDATE GVA14 SET TIPO_DOC = $tipo_doc, ID_TIPO_DOCUMENTO_GV = $id_t_d, CUIT = '$cuit_cuil_dni_sin_guiones', E_MAIL = '$correo', MAIL_DE = '$correo' WHERE TELEFONO_1 = '$cli_interno'";
        }
        $consulta = $query->prepare($update);
        $consulta->execute();
        $consulta->closeCursor();
    }

    /* Actualizo la información de los clientes ya procesados */

    public function actualizoArchivoClientesImpo()
    {
        $query = $this->db_sql;
        $update = "UPDATE RXN_CSV SET ESTADO = 'P' WHERE NOMBRE_ARCHIVO LIKE 'CLI%'";
        $consulta = $query->prepare($update);
        $consulta->execute();
        $consulta->closeCursor();
    }

    public function actualizaPedidos()
    {
        $query = $this->db_sql;
        $update = "UPDATE RXN_CSV SET ESTADO = 'P' WHERE NOMBRE_ARCHIVO LIKE 'D20%' OR NOMBRE_ARCHIVO LIKE 'C20%' OR NOMBRE_ARCHIVO LIKE 'cabe%' OR NOMBRE_ARCHIVO LIKE 'deta%'";
        $consulta = $query->prepare($update);
        $consulta->execute();
        $consulta->closeCursor();
    }

    /* Devuelvo el parámetro buscado según corresponda */

    public function leoParametroBd($nombre_col)
    {
        $consulta = $this->db_sql->query("SELECT $nombre_col FROM RXN_PARAMETROS");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        return $this->param = $filas[$nombre_col];


        $consulta->closeCursor();
    }

    /* Controlo que los artículos existan en la base */

    public function ctrlArtsBase($articulo)
    {
        $consulta = $this->db_sql->query("SELECT TOP(1)* FROM STA11 WHERE COD_ARTICU = '$articulo'");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        return $this->ctrl_articu = $filas;


        $consulta->closeCursor();
    }

    /* Traigo el último ID para colocar en la API */

    public function devuelvoIdPedido($columna)
    {
        $consulta = $this->db_sql->query("SELECT $columna FROM RXN_PARAMETROS");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        return $this->id_orden = $filas[$columna];


        $consulta->closeCursor();
    }

    /* Actualizo el ID registrado en la tabla parametros */

    public function actIdFac($num_fac, $campo)
    {
        //Conecto con la base de datos
        $query = $this->db_sql;

        /* Creo la query que va a generar el ingreso de los nuevos archivos para ser leídos */
        $ingreso_valor_bd = "UPDATE RXN_PARAMETROS SET $campo = $num_fac";
        $ingreso_valor = $query->prepare($ingreso_valor_bd);
        $ingreso_valor->execute();
        $ingreso_valor->closeCursor();
    }

    public $cli_csv;

    public function clientesCsv()
    {
        $this->leoArchivosBdCli();
        if (isset($this->nombre_archivo)) {
            foreach ($this->nombre_archivo as $archivo) {
                /* Recorro las iteraciones para ingresar a pantalla */
                $archivo2 = fopen($this->leoParametroBd('RUTAXML') . '/' . $archivo['NOMBRE_ARCHIVO'], "r");
                while (($datos = fgetcsv($archivo2, 0, ",")) == true) {

                    $array_nombre_archivo = array($archivo['NOMBRE_ARCHIVO']);

                    /* Creo un array con los datos del DNI del cliente para pasarlos a la variable */
                    $dni[] = array_merge($datos, $array_nombre_archivo);
                    /* Completo un array para luego compararlos con los códigos de clientes */
                    $this->cli_csv = $dni;
                }
                fclose($archivo2);
            }
        } else {
            $this->ingresoMensajesApi('SIN', 'CLIENTES', 'NO-HAY-ARCHIVOS-PARA-PROCESAR', 0, '', '', 0, '', '', '');
        }
    }

    public $enc_pedi_csv;

    //public $archivo_procesado_csv;

    /* Guardo en arrray los datos del CSV de encabezados de pedidos */

    public function encPedidos($menu)
    {
        if ($menu === 'PROCESAR') {
            $this->leoArchivosBdEncPed();
            if (isset($this->nombre_archivo_enc_pedidos)) {
                foreach ($this->nombre_archivo_enc_pedidos as $archivo) {
                    /* Recorro las iteraciones para ingresar a pantalla */
                    $archivo2 = fopen($this->leoParametroBd('RUTAXML') . '/' . $archivo['NOMBRE_ARCHIVO'], "r");

                    while (($datos = fgetcsv($archivo2, 0, ",")) == true) {
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
        if ($menu === 'REPROCESAR') {
            $this->leoArchivosBdNoProcesados();
            if (isset($this->nombre_archivo_enc_pedidos)) {
                foreach ($this->nombre_archivo_enc_pedidos as $archivo) {
                    /* Recorro las iteraciones para ingresar a pantalla */
                    $archivo2 = fopen($this->leoParametroBd('RUTAXML') . '/' . $archivo['NOMBRE_ARCHIVO'], "r");

                    while (($datos = fgetcsv($archivo2, 0, ",")) == true) {
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
    }

    public $cue_pedi_csv;

    public function cuePedidos($archivo_en_proceso, $n_comp, $menu)
    {
        /* Si es RERPROCESO entonces regenero el array del cuerpo */
        //if ($menu === 'REPROCESAR') {
        unset($this->cue_pedi_csv);
        $this->cue_pedi_csv = array();
        //}

        if (empty($this->cue_pedi_csv)) {
            $this->leoArchivosBdCuePed($archivo_en_proceso);
            /* Verifico si cambia el nombre de archivo entre arrays para no leerlo cada vez que llega */
            foreach ($this->nombre_archivo_cue_pedidos as $archivo) {
                /* Recorro las iteraciones para ingresar a pantalla */

                $archivo2 = fopen($this->leoParametroBd('RUTAXML') . '/' . $archivo['NOMBRE_ARCHIVO'], "r");
                while (($datos = fgetcsv($archivo2, 0, ",")) == true) {

                    $array_nombre_archivo = array($archivo['NOMBRE_ARCHIVO']);
                    $cue_pedi[] = array_merge($datos, $array_nombre_archivo);



                    $this->cue_pedi_csv = $cue_pedi;
                    //$cue_pedi
                }
                fclose($archivo2);
            }
        } else {
        }

        /* Correspondiente al cuerpo del pedido */
        foreach ($this->cue_pedi_csv as $ctrl_cue_pedi) {
            if ($ctrl_cue_pedi['0'] != 'orden') {
                $csv_cue[] = $ctrl_cue_pedi;
            }
            //$csv_cue[] = $ctrl_cue_pedi;
        }

        $this->buscarEnArray($csv_cue, $n_comp, 1);

        /* Transformación de array de lectura de CSV */
        foreach ($this->resultados_array_gen as $valor_cue_csv) {
            $this->dato_pedi_cue[] = array("N_COMP" => $valor_cue_csv[1], "COD_ARTICU" => $valor_cue_csv[3], "CANT" => $valor_cue_csv[4], "PRECIO" => $valor_cue_csv[9], "REVEND" => $valor_cue_csv[12], "IMPORTE_NETO" => $valor_cue_csv[5], "TOTAL_RENGLON" => $valor_cue_csv[10], "PRECIO_NETO" => $valor_cue_csv[6], "ORDEN" => $valor_cue_csv[0]);
        }
    }

    public function evaluarYActualizarClienteAPI($cod_cliente, $importe_gatillo) {
        $cod_cliente_safe = is_scalar($cod_cliente) ? htmlspecialchars((string)$cod_cliente) : 'N/A';
        $importe_gatillo_safe = is_numeric($importe_gatillo) ? number_format((float)$importe_gatillo, 2) : '0.00';
        
        echo "<div style='color: #0d6efd; font-family: monospace; font-size: 14px; padding: 6px; border-bottom: 1px solid #444; margin-bottom: 2px;'>[CLIENTE-TRIBUTARIO] 🔍 Detectado importe/impuesto gatillo (\${$importe_gatillo_safe}) superior a $250 para cliente {$cod_cliente_safe}. Leyendo CSV Maestro...</div>";
        
        // Sanitización: Validar instancia de matriz en memoria
        if (!isset($this->cli_csv) || !is_array($this->cli_csv) || empty($this->cli_csv)) {
            $this->clientesCsv();
        }
        
        if (!isset($this->cli_csv) || !is_array($this->cli_csv) || empty($this->cli_csv)) {
            echo "<div style='color: #dc3545; font-family: monospace; font-size: 14px; padding: 6px; border-bottom: 1px solid #444; margin-bottom: 2px;'>[CLIENTE-TRIBUTARIO] ✘ Error: No se pudo cargar el archivo CSV maestro de clientes o su formato es corrupto.</div>";
            return; // Fallback seguro
        }

        $cliente_encontrado = null;
        $codigo_fila = null;
        foreach ($this->cli_csv as $valor_csv) {
            // Sanitización: Evitar offsets falopas donde cada fila sea un string o boolean
            if (is_array($valor_csv)) {
                $codigo_fila_csv = $valor_csv[0] ?? null;
                if ($codigo_fila_csv !== null && trim((string)$codigo_fila_csv) === trim((string)$cod_cliente)) {
                    $cliente_encontrado = $valor_csv;
                    $codigo_fila = trim((string)$codigo_fila_csv);
                    break;
                }
            }
        }

        if (!is_array($cliente_encontrado) || empty($codigo_fila)) {
            echo "<div style='color: #ff9800; font-family: monospace; font-size: 14px; padding: 6px; border-bottom: 1px solid #444; margin-bottom: 2px;'>[CLIENTE-TRIBUTARIO] ⚠ Advertencia: Cliente {$cod_cliente_safe} no hallado en el CSV Maestro. Se omite actualización.</div>";
            return;
        }

        // --- Búsqueda del cliente real en Tango por TELEFONO_1 ---
        $codigo_fila_safe = htmlspecialchars($codigo_fila);
        echo "<div style='color: #0dcaf0; font-family: monospace; font-size: 14px; padding: 6px; border-bottom: 1px solid #444; margin-bottom: 2px;'>[CLIENTE-TRIBUTARIO] 🔍 Buscando cliente definitivo en SQL Tango con TELEFONO_1 = '{$codigo_fila_safe}'...</div>";
        
        $this->busco_cliente($codigo_fila);
        
        // Sanitización: la respuesta de la DB podría ser false si no lo halla
        if (!is_array($this->tabla_cliente_cod_cliente) || empty($this->tabla_cliente_cod_cliente['COD_CLIENT'])) {
            echo "<div style='color: #dc3545; font-family: monospace; font-size: 14px; padding: 6px; border-bottom: 1px solid #444; margin-bottom: 2px;'>[CLIENTE-TRIBUTARIO] ✘ Error: No existe ningún cliente en Tango asociado al TELEFONO_1 '{$codigo_fila_safe}'. Abortando UPDATE.</div>";
            return;
        }

        $tango_cod_client = trim((string)$this->tabla_cliente_cod_cliente['COD_CLIENT']);
        $tango_id_gva14 = htmlspecialchars((string)($this->tabla_cliente_cod_cliente['ID_GVA14'] ?? 'Desc.'));

        echo "<div style='color: #198754; font-family: monospace; font-size: 14px; padding: 6px; border-bottom: 1px solid #444; margin-bottom: 2px;'>[CLIENTE-TRIBUTARIO] ✔ Cliente de Tango Confirmado -> COD_CLIENT: " . htmlspecialchars($tango_cod_client) . " | ID_GVA14: {$tango_id_gva14}</div>";
        
        // Sanitización de nulls o truncamientos en CSV Tributario
        $alic_perc_csv = $cliente_encontrado[24] ?? ''; 
        $cat_iva_csv = $cliente_encontrado[17] ?? '';

        $alic_safe = is_scalar($alic_perc_csv) ? htmlspecialchars((string)$alic_perc_csv) : 'N/A';
        $cat_safe = is_scalar($cat_iva_csv) ? htmlspecialchars((string)$cat_iva_csv) : 'N/A';

        echo "<div style='color: #198754; font-family: monospace; font-size: 14px; padding: 6px; border-bottom: 1px solid #444; margin-bottom: 2px;'>[CLIENTE-TRIBUTARIO] ✔ Valores tributarios leídos -> alic_perc (Y): {$alic_safe} | cat_iva (R): {$cat_safe}</div>";

        // Mapeo real
        $alic_perc_clean = is_numeric($alic_perc_csv) ? (float)$alic_perc_csv : $alic_perc_csv;
        $this->busco_alicuota($alic_perc_clean);
        // Sanitización: Validar Helper DB return array
        $id_ali_fij_ib = is_array($this->tabla_alicuo) ? ($this->tabla_alicuo['ID_GVA41'] ?? null) : null;
        
        // Chequeos duros requeridos por SQL
        if (!is_numeric($cat_iva_csv) || !is_numeric($id_ali_fij_ib)) {
            echo "<div style='color: #dc3545; font-family: monospace; font-size: 14px; padding: 6px; border-bottom: 1px solid #444; margin-bottom: 2px;'>[CLIENTE-TRIBUTARIO] ✘ Error: Valores numéricos tributarios inválidos. Abortando UPDATE SQL.</div>";
            return;
        }

        $id_gva41_no_cat = (trim((string)$cat_iva_csv) === '10') ? 1 : 'NULL';
        
        // Construcción de UPDATE a GVA14 (Cuerpo principal IVA)
        $sql_update_gva14 = "UPDATE GVA14 SET ID_CATEGORIA_IVA = {$cat_iva_csv}";
        if ($id_gva41_no_cat !== 'NULL') {
            $sql_update_gva14 .= ", ID_GVA41_NO_CAT = {$id_gva41_no_cat}";
        } else {
            $sql_update_gva14 .= ", ID_GVA41_NO_CAT = NULL";
        }
        $sql_update_gva14 .= " WHERE COD_CLIENT = '{$tango_cod_client}'";

        // Construcción de UPDATE a DIRECCION_ENTREGA (Cuerpo Ingresos Brutos)
        $sql_update_entrega = "UPDATE DIRECCION_ENTREGA SET ID_ALI_FIJ_IB = {$id_ali_fij_ib} WHERE COD_CLIENTE = '{$tango_cod_client}'";

        echo "<div style='color: #0dcaf0; font-family: monospace; font-size: 14px; padding: 6px; border-bottom: 1px solid #444; margin-bottom: 2px;'>[CLIENTE-TRIBUTARIO] ⚙ Ejecutando Query GVA14 y DIRECCION_ENTREGA...</div>";

        try {
            $consulta1 = $this->db_sql->query($sql_update_gva14);
            $afectadas_gva14 = $consulta1 ? $consulta1->rowCount() : 0;
            
            $consulta2 = $this->db_sql->query($sql_update_entrega);
            $afectadas_entrega = $consulta2 ? $consulta2->rowCount() : 0;
        } catch (PDOException $e) {
            echo "\nPDO EXCEPTION: " . $e->getMessage() . "\n";
            die();
        }

        // Verificación POST-UPDATE integral
        $check_gva14 = $this->db_sql->query("SELECT TOP 1 ID_CATEGORIA_IVA, ID_GVA41_NO_CAT FROM GVA14 WHERE COD_CLIENT = '{$tango_cod_client}'")->fetch(PDO::FETCH_ASSOC);
        $new_cat_iva = htmlspecialchars((string)($check_gva14['ID_CATEGORIA_IVA'] ?? 'N/A'));
        $new_gva41_no_cat = htmlspecialchars((string)($check_gva14['ID_GVA41_NO_CAT'] ?? 'N/A'));
        
        // Fetch manual para DIRECCION_ENTREGA
        $check_ib = $this->db_sql->query("SELECT TOP 1 ID_ALI_FIJ_IB FROM DIRECCION_ENTREGA WHERE COD_CLIENTE = '{$tango_cod_client}'")->fetch(PDO::FETCH_ASSOC);
        $new_ali_fij = htmlspecialchars((string)($check_ib['ID_ALI_FIJ_IB'] ?? 'N/A'));
        
        $total_afectadas = $afectadas_gva14 + $afectadas_entrega;
        
        if ($total_afectadas > 0) {
            echo "<div style='color: #20c997; font-family: monospace; font-size: 15px; font-weight: bold; padding: 6px; border-bottom: 1px solid #444; margin-bottom: 2px;'>[CLIENTE-TRIBUTARIO] ✅ UPDATE SQL EXITOSO (Filas afectadas: {$total_afectadas}) -> Quedó Guardado: ID_CATEGORIA_IVA = {$new_cat_iva} | ID_ALI_FIJ_IB = {$new_ali_fij} | ID_GVA41_NO_CAT = {$new_gva41_no_cat}</div>";
        } else {
            echo "<div style='color: #ffc107; font-family: monospace; font-size: 14px; padding: 6px; border-bottom: 1px solid #444; margin-bottom: 2px;'>[CLIENTE-TRIBUTARIO] ⚠ UPDATE SQL ejecutado, pero 0 filas impactaron. Los datos ya eran idénticos. Guardado actual: ID_CATEGORIA_IVA = {$new_cat_iva} | ID_ALI_FIJ_IB = {$new_ali_fij}</div>";
        }
    }

    public $dato_pedi_cue;

    public function procesoPedidos($menu)
    {
        /* Recorro el */
        $this->encPedidos($menu);

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
                $dato_pedi_enc[] = array("N_COMP" => $valor_cue_csv[1], "COD_CLIENT" => $valor_cue_csv[3], "IMPORTE" => $valor_cue_csv[5], "COD_ZONA" => $valor_cue_csv[18], "BONIFCOSME" => $valor_cue_csv[13], "PRACTICOSAS" => $valor_cue_csv[14], "GASTADMIN" => $valor_cue_csv[11], "IMPORTE_GRAVADO" => $valor_cue_csv[7], "FECHA" => $valor_cue_csv[2], "IMP_IVA" => $valor_cue_csv[9], "ORDEN" => $valor_cue_csv[0], "BONIF_ADIC" => $valor_cue_csv[17], "NOMBRE_ARCHIVO" => $valor_cue_csv[19]);
            }

            $this->devuelvoTokens();
            $modo_proceso = $this->leoParametroBd('MODO_PROCESO') ?? 'FACTURA';
            $numeroFac = $this->token['FAC_B'];

            /* Recorro el encabezado del pedido */
            foreach ($dato_pedi_enc as $pedi_enc) {
                /* Busco el archivo en cuestión para poder procesar solo el correspondiente al búcle */
                $this->cuePedidos($pedi_enc['NOMBRE_ARCHIVO'], $pedi_enc['N_COMP'], $menu);

                /* Controlo si existe el pedido en la base, si existe no se ingresará */
                $this->ctrlPedi($pedi_enc['N_COMP'], $pedi_enc['NOMBRE_ARCHIVO']);

                /*                 if ($this->ctrlPediRxnApiCtrl !== false) {
                    echo 'entro acá?';
                    $valor = $this->ctrlPediRxnApiCtrl['COD_COMP'] ?? null;
                } */

                /* Si no existe en la tabla de control entonces se ingresa */
                if (($this->ctrlPediRxnApiCtrl['COD_COMP'] ?? '') == '') {

                    // --- NUEVA LÓGICA FASE 3: EVALUACIÓN UMBRAL TRIBUTARIO ---
                    $importes_a_controlar = [
                        $pedi_enc['IMPORTE'],
                        $pedi_enc['BONIFCOSME'],
                        $pedi_enc['PRACTICOSAS'],
                        $pedi_enc['GASTADMIN'],
                        $pedi_enc['IMPORTE_GRAVADO'],
                        $pedi_enc['IMP_IVA'],
                        $pedi_enc['BONIF_ADIC']
                    ];

                    $importe_gatillo = 0;
                    foreach ($importes_a_controlar as $imp_val) {
                        // Sanitización robusta ante strings sucios
                        $clean_val = trim($imp_val ?? '');
                        if ($clean_val === '') continue;
                        $clean_val = str_replace(',', '.', $clean_val);
                        // Parseo tolerante a miles
                        $float_val = floatval($clean_val);
                        if ($float_val > 250) {
                            $importe_gatillo = $float_val;
                            break;
                        }
                    }

                    // Se comprueba si el ticket es superior a $250 en la línea de impuestos
                    if ($importe_gatillo > 250) {
                        $this->evaluarYActualizarClienteAPI($pedi_enc['COD_CLIENT'], $importe_gatillo);
                    }
                    // -----------------------------------------------------------



                    // --- BIFURCACIÓN TARDÍA DE FLUJOS ---
                    if ($modo_proceso === 'PEDIDO') {
                        // --- FLUJO NUEVO: PEDIDOS ---
                        $this->buscoPedidoRXN($pedi_enc['N_COMP'], $pedi_enc['COD_CLIENT'], $pedi_enc['ORDEN'], $pedi_enc['NOMBRE_ARCHIVO']);
                        $this->ingresoPedido($pedi_enc['COD_CLIENT'], $pedi_enc['IMPORTE'], $this->articulos, $pedi_enc['N_COMP'], $pedi_enc['COD_ZONA'], $pedi_enc['IMPORTE_GRAVADO'], $pedi_enc['FECHA'], $pedi_enc['BONIFCOSME'], $pedi_enc['PRACTICOSAS'], $pedi_enc['GASTADMIN'], $pedi_enc['IMP_IVA'], $pedi_enc['BONIF_ADIC']);

                        $is_succeeded = is_array($this->mensaje_api) && isset($this->mensaje_api['succeeded']) && $this->mensaje_api['succeeded'] === true;

                        if ($is_succeeded) {
                            $grabo = 1;
                            $savedId = $this->mensaje_api['savedId'] ?? null;
                            $affected = $this->mensaje_api['recordAffectedCount'] ?? 0;

                            if (!empty($savedId)) {
                                $mensaje_log = "Pedido grabado con éxito | ID {$savedId} | {$affected} filas afectadas";
                            } else {
                                $mensaje_log = "Pedido grabado con éxito | {$affected} filas afectadas";
                            }

                            // --- FIX: Cierre del ciclo de reprocesamiento en Pedidos ---
                            // Actualizar en cascada a 1 todos los intentos fallidos anteriores
                            $this->actualizoReproceso($pedi_enc['N_COMP'], $pedi_enc['COD_CLIENT'], $pedi_enc['NOMBRE_ARCHIVO']);
                        } else {
                            $grabo = 0;
                            $raw_msg = is_array($this->mensaje_api) ? ($this->mensaje_api['message'] ?? '') : '';
                            $raw_exc = is_array($this->mensaje_api) ? ($this->mensaje_api['exceptionInfo'] ?? '') : '';
                            
                            // Sanitización: Evitar Array to string conversion si la API devuelve arrays anidados
                            $msg = is_array($raw_msg) ? json_encode($raw_msg, JSON_UNESCAPED_UNICODE) : (string)$raw_msg;
                            $exc = is_array($raw_exc) ? json_encode($raw_exc, JSON_UNESCAPED_UNICODE) : (string)$raw_exc;

                            if ($msg !== '' && $exc !== '') {
                                $mensaje_log = "ERROR API | message={$msg} | exception={$exc}";
                            } elseif ($msg !== '') {
                                $mensaje_log = "ERROR API | message={$msg}";
                            } elseif ($exc !== '') {
                                $mensaje_log = "ERROR API | exception={$exc}";
                            } else {
                                $mensaje_log = "ERROR | Respuesta inválida API";
                            }
                        }

                        $detalle_api_str = is_array($this->mensaje_api) ? json_encode($this->mensaje_api, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : (is_string($this->mensaje_api) ? $this->mensaje_api : '');

                        $mensaje_txt = "Mensaje: " . (is_array($this->mensaje_api) ? ($this->mensaje_api['message'] ?? 'N/A') : 'N/A') . " Estado: " . ($is_succeeded ? 'succeeded' : 'failed');
                        $fh_log = fopen("detalle_proceso.txt", "a");
                        if ($fh_log !== false) {
                            fwrite($fh_log, PHP_EOL . "$mensaje_txt");
                            fclose($fh_log);
                        } else {
                            error_log('[' . date('Y-m-d H:i:s') . '] No se pudo escribir detalle_proceso.txt');
                        }

                        $this->ingresoMensajesApi($pedi_enc['N_COMP'], 'PEDIDOS', $mensaje_log, $grabo, $pedi_enc['COD_CLIENT'], $pedi_enc['NOMBRE_ARCHIVO'], 0, $detalle_api_str, '', '');
                        
                        // --- INYECCIÓN UI (LOG DE PEDIDOS) ---
                        $ui_color = ($grabo == 1) ? '#4caf50' : '#dc3545';
                        $ui_icon  = ($grabo == 1) ? '✔' : '✘';
                        
                        echo "<div style='color: {$ui_color}; font-family: monospace; font-size: 14px; padding: 6px; border-bottom: 1px solid #444; margin-bottom: 2px;'>
                                [PID-{$pedi_enc['N_COMP']}] {$ui_icon} " . htmlspecialchars($mensaje_log) . "
                              </div>";
                        // --------------------------------------
                    } else {
                        // --- FLUJO HISTÓRICO: FACTURAS ---
                        $this->buscoPedido($pedi_enc['N_COMP'], $pedi_enc['COD_CLIENT'], $pedi_enc['ORDEN'], $pedi_enc['NOMBRE_ARCHIVO']);
                        $this->ingresoFactura($pedi_enc['COD_CLIENT'], $pedi_enc['IMPORTE'], implode($this->articulos), $pedi_enc['N_COMP'], $pedi_enc['COD_ZONA'], $pedi_enc['IMPORTE_GRAVADO'], $pedi_enc['FECHA'], $pedi_enc['BONIFCOSME'], $pedi_enc['PRACTICOSAS'], $pedi_enc['GASTADMIN'], $pedi_enc['IMP_IVA'], $pedi_enc['BONIF_ADIC']);

                        if (!isset($this->mensaje_api['savedId'])) {
                            $id = 0;
                            $mensaje = 'nulo';
                        } else {
                            $id = $this->mensaje_api['savedId'];
                        }
                        $stringConvertido = $this->convertirATexto($this->mensaje_api);

                        if (empty($this->mensaje_api['Succeeded'])) {
                            $grabo = 0;
                        } else {
                            $num_mas_uno = $this->id_orden + 1;
                            $this->actIdFac($num_mas_uno, $this->param_numeracion);
                            $grabo = 1;
                            $this->actualizoReproceso($pedi_enc['N_COMP'], $pedi_enc['COD_CLIENT'], $pedi_enc['NOMBRE_ARCHIVO']);
                        }

                        /* Verifico si viene el detalle para imprimirlo o ono */
                        if (!empty($this->mensaje_api['Comprobantes'][0]['exceptionMessage'])) {
                            $detalle = ' Detalle: ' . $this->mensaje_api['Comprobantes'][0]['exceptionMessage'] . ' en el código de cliente (teléfono 1): ' . $pedi_enc['COD_CLIENT'];
                        } else {
                            $detalle = '';
                        }
                        /* Elimino el muestro de mensajes para dejarlos en un archivo de texto a fin de que no se cuelgue el navegador */
                        $mensaje_txt = 'Mensaje: ' . ($this->mensaje_api['Message'] ?? '') . ' Comprobante: ' . ($this->mensaje_api['Comprobantes'][0]['numeroComprobante'] ?? '') . ' Estado: ' . ($this->mensaje_api['Comprobantes'][0]['estado'] ?? '') . $detalle;
                        /* Hardening Linux: fopen modo 'a' crea el archivo si no existe; guarda ante permisos */
                        $fh_log = fopen("detalle_proceso.txt", "a");
                        if ($fh_log !== false) {
                            fwrite($fh_log, PHP_EOL . "$mensaje_txt");
                            fclose($fh_log);
                        } else {
                            error_log('[' . date('Y-m-d H:i:s') . '] No se pudo escribir detalle_proceso.txt');
                        }

                        // --- INYECCIÓN UI (LOG DE FACTURAS) ---
                        $ui_color = ($grabo == 1) ? '#4caf50' : '#dc3545';
                        $ui_icon  = ($grabo == 1) ? '✔' : '✘';
                        $ui_msj   = ($grabo == 1) ? "Factura grabada con éxito | ID {$id}" : "ERROR API | " . ($this->mensaje_api['Message'] ?? 'Falla al procesar');

                        echo "<div style='color: {$ui_color}; font-family: monospace; font-size: 14px; padding: 6px; border-bottom: 1px solid #444; margin-bottom: 2px;'>
                                [FAC-{$pedi_enc['N_COMP']}] {$ui_icon} " . htmlspecialchars($ui_msj) . "
                              </div>";
                        // --------------------------------------

                        $mensaje_api = 'ID_INTERNO: ' . $id . ' Mensaje: ' . $stringConvertido . ' ¿Grabó?: ' . ($this->mensaje_api['Succeeded'] ?? '');

                        $this->ingresoMensajesApi($pedi_enc['N_COMP'], 'PEDIDOS', $mensaje_api, $grabo, $pedi_enc['COD_CLIENT'], $pedi_enc['NOMBRE_ARCHIVO'], 0, $stringConvertido, '', '');
                    }
                    //$grabo
                    //unset($this->cue_pedi_csv);
                } else {
                    /* Si el valor es reproceso no ingreso el registro a la base de datos (para no llenar al pedo en los reprocesos */
                    if ($menu === 'PROCESAR') {
                        $stringConvertido = '';
                        /* Al existir ingreso mensaje en la tabla no ingreso para procesar siempre el mismo pedido  $stringConvertido */
                        $this->ingresoMensajesApi($pedi_enc['N_COMP'], 'PEDIDOS', 'EL Pedido ya existe', 1, $pedi_enc['COD_CLIENT'], $pedi_enc['NOMBRE_ARCHIVO'], 0, $stringConvertido, '', '');
                        echo "<div style='color: #ff9800; font-family: monospace; font-size: 14px; padding: 6px; border-bottom: 1px solid #444; margin-bottom: 2px;'>[PID-{$pedi_enc['N_COMP']}] ⚠ El pedido ya existe para el cliente {$pedi_enc['COD_CLIENT']}.</div>";
                    }
                }
            }

            $this->actualizaPedidos();
        } else {
            echo "<div style='color: #ff9800; font-family: monospace; font-size: 14px; padding: 6px; border-bottom: 1px solid #444; margin-bottom: 2px;'>⚠ No hay archivos de pedidos para procesar.</div>";
            $this->ingresoMensajesApi('SIN', 'PEDIDOS', 'NO-HAY-ARCHIVOS-PARA-PROCESAR', 0, '', '', 0, '', '', '');
        }
        /* Los siguientes valores son para validar el total de facturas */
        /* Pruebo el método que ingresa todas las facturas */
        //$this->curlJsonCompleto();
    }

    /* Método que realiza el ingreso se pasará como parámetro el valor */

    /* Método para pasar la fecha al pedi y lo necesario */

    public $fecha;

    public function fechaFac($fecha)
    {
        $this->fechaFac = $fecha;
    }

    function formatearNumeroSinRedondear($numero)
    {
        // Utilizar sprintf sin redondeo para completar los decimales con ceros
        $numeroFormateado = sprintf("%.2f", floor($numero * 100) / 100);

        // Eliminar la coma del resultado
        $numeroFinal = str_replace(',', '', $numeroFormateado);

        return $numeroFinal;
    }

    function redondearNumeroEspecial($numero)
    {
        // Extraer la parte entera y decimal del número
        $parteEntera = floor(sprintf("%.2f", $numero));
        $parteDecimal = $numero - $parteEntera;

        // Redondear la parte decimal según las reglas específicas
        if ($parteDecimal <= 0.82) {
            $redondeadoDecimal = 0.80;
        } elseif ($parteDecimal <= 0.83) {
            $redondeadoDecimal = 0.85;
        } else {
            $redondeadoDecimal = 0.90;
        }

        // Devolver el número redondeado
        return $parteEntera + $redondeadoDecimal;
    }

    /* Formateo los valores de importes sin redondear. */

    function formatearNumero($numero, $param)
    {

        /*Completamos $numero con 0 en el caso de ser nulo había un bug aquí que estaba número en $value */
        $numero = $numero ?? 0;

        $numero = round($numero, 2);
        // Utilizar sprintf sin redondeo para completar los decimales con ceros
        //$numeroConDecimales = sprintf("%.2f", $numero);
        $numeroConDecimales = sprintf("%.2f", $numero);
        // Utilizar number_format para agregar la coma y formatear el resultado sin redondeo
        $numeroFormateado = number_format($numero, 2, '.', '');
        //$numeroConDecimales
        // Eliminar la coma del resultado
        $numeroFinal = str_replace(',', '', $numeroFormateado);

        return $numeroFinal;
    }

    function formatearNumeroArriba($numero, $param)
    {
        // Utilizar sprintf sin redondeo para completar los decimales con ceros
        $numeroConDecimales = sprintf("%.2f", $numero);

        // Utilizar number_format para agregar la coma y formatear el resultado sin redondeo
        $numeroFormateado = number_format($numero, 2, '.', '');
        //$numeroConDecimales
        // Eliminar la coma del resultado
        $numeroFinal = str_replace(',', '', $numeroFormateado);

        return $numeroFinal;
    }

    function formatearNumeroDosDecimales($numero, $param)
    {
        // Utilizar sprintf sin redondeo para completar los decimales con ceros
        //$numeroConDecimales = sprintf("%.2f", $numero);
        // Utilizar number_format para agregar la coma y formatear el resultado sin redondeo
        $numeroFormateado = number_format($numero, $param, '.', '');
        //$numeroFormateado = floor($numero * 100) / 100;
        //$numeroConDecimales
        // Eliminar la coma del resultado
        $numeroFinal = str_replace(',', '', $numeroFormateado);

        return $numeroFinal;
    }

    function formatearNumeroAbajo($numero)
    {

        return floor($numero * 100) / 100;
    }

    /* Buscar un valor de un array */

    public function buscarEnArray($array, $busqueda, $valor_buscado)
    {
        $resultados = array();

        foreach ($array as $elemento) {
            // Realizar la búsqueda en el campo 'NRO_PEDIDO'
            /* En este caso si bien es genérico le paso el N_COMP que sería el número de pedido */
            if (strpos($elemento[$valor_buscado], $busqueda) !== false) {
                // Agregar el número de pedido al array de resultados
                $resultados[] = $elemento;
            }
        }

        return $this->resultados_array_gen = $resultados;
    }

    /* Copia del método original para configurarlo tal cual requiere el proceso */
    public function buscoPedidoRXN($pedido, $cliente, $orden_x, $nombre_archivo)
    {

        $this->buscarEnArray($this->dato_pedi_cue, $pedido, 'N_COMP');

        /* Correspondiente al cuerpo del pedido */
        $busco = $pedido;
        $contar = 0;
        //echo 'Pedido: ' . $pedido . ' Orden: ' . $orden_x . ' Nombre archivo: ' . $nombre_archivo . '<br>';
        //print_r($this->resultados_array_gen);
        $this->tot_para_ped = 0;
        $articulos = [];

        /* Prueba para armar el string de artículos */
        foreach ($this->resultados_array_gen as $articu) {
            /*Defino las variables del precio que van a ser usadas más abajo*/
            $precio = $articu['TOTAL_RENGLON'];
            $precio_art = $articu['PRECIO'];
            //$importe = $articu['CANT'] * $articu['PRECIO_NETO'];

            //echo 'Comprobante: '. $articu['N_COMP'].'Orden: '.$articu['ORDEN']. ' Código de artículo: ' .$articu['COD_ARTICU'] .'<br>';
            $contar = ++$contar;
            /* Saco el código de artículo para que pueda ingresar correctamente a la base y matchear con los originales. */
            /* Busco la descripción del artículo */
            $search = array('/', '-');
            $replace = "";
            $articu_ini = substr($articu['COD_ARTICU'], 1);
            $subject = $articu_ini;
            $articu_formato = substr(str_replace($search, $replace, $subject), 0, -1);


            //echo 'Con formato: ' . $articu_formato . 'Original ->' . $articu['COD_ARTICU'] . '->' . ++$contar . '<br>';
            $this->ctrlArtsBase($articu_formato);
            /* Controlo si el artículo está en la base */
            if ($articu['N_COMP'] == $busco and $articu['ORDEN'] == $orden_x) {
                $nro_pedido = $articu['N_COMP'];
                if ($contar > 0) {

                    // Sanitización: Validar que el resultado de BD sea un array antes de buscar un offset
                    if (!is_array($this->ctrl_articu) || empty($this->ctrl_articu['COD_ARTICU'])) {
                        $descripcio = 'NO EXISTE EL ARTICULO EN LA BASE';
                    } else {
                        /* Controlo si el comprobante es expo para agregar el revend */
                        $pv_pedi = substr($nro_pedido, 0, 6);

                        if ($pv_pedi == 'E00011') {
                            /* Completa la descripción adicional */
                            $descripcio = str_pad($this->ctrl_articu['DESCRIPCIO'], 30, ' ', STR_PAD_RIGHT);

                            $revend_ok = str_replace(' - ', '-', $articu['REVEND']);
                            /* Corto revend para formar el string para las descripciones adicionales */
                            $cero_revend = '                   ';
                            $primer_revend = substr($revend_ok, 0, 29) . '';
                            $segundo_revend = substr($revend_ok, 18, 48) . '\r\n';
                            $tercer_revend = substr($revend_ok, 48, 66);
                            /* Controlo si el revend viene solo con el guión para no transportarlo como línea nueva */
                            if ($articu['REVEND'] === '-') {
                                $descrip_adic = '';
                            } else {
                                $descrip_adic = '                \r\n' . $primer_revend;
                            }
                        } else {
                            //$lista = 1;
                            $completo_con_30 = str_pad($this->ctrl_articu['DESCRIPCIO'], 30, ' ');
                            $descripcio = $this->ctrl_articu['DESCRIPCIO'] . '' . $articu['COD_ARTICU'];
                        }
                    }
                    if ($precio_art < 0 or $precio < 0) {
                        $precio_art = $precio_art * -1;
                        $precio = $precio * -1;
                        $art_negativo = -1;
                    } else {
                        $art_negativo = 1;
                    }

                    //echo '¿Entro al artículo?<br>';

                    /* Estructura de array solicitada para nueva API */
                    $id_sta11_recuperado = $this->devuelvoIdArticulo($articu_formato);

                    $articulos[] = [
                        "ID_STA11" => $id_sta11_recuperado ?? $articu_formato, // Fallback al código si no encuentra ID
                        "CANTIDAD_PEDIDA" => $articu['CANT'] * $art_negativo,
                        "DESCRIPCION_ARTICULO" => $descripcio,
                        "DESCRIPCION_ADICIONAL_ARTICULO" => $articu['COD_ARTICU'],
                        "CANTIDAD_A_FACTURAR" => $articu['CANT'] * $art_negativo,
                        "CANTIDAD_PENDIENTE_A_FACTURAR" => $articu['CANT'] * $art_negativo,
                        "PRECIO" => $precio_art,
                        //"IMPORTE" => $importe,
                        "PORCENTAJE_BONIFICACION" => 0,
                        "OBSERVACIONES" => null
                    ];
                }
            }
        }
        //echo '<br/>artículo después del foreach: ';
        //print_r($articulos);
        //return 
        $this->articulos = $articulos;
    }

    /* Método para devolver el ID del artículo */
    public function devuelvoIdArticulo($cod_articu)
    {
        $consulta = $this->db_sql->query("SELECT ID_STA11 FROM STA11 WHERE COD_ARTICU = '$cod_articu'");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        $consulta->closeCursor();

        if ($filas) {
            return $filas['ID_STA11'];
        }
        return null;
    }

    /* Función para buscar el pedido y devolver los artículos */

    public function buscoPedido($pedido, $cliente, $orden_x, $nombre_archivo)
    {


        $this->buscarEnArray($this->dato_pedi_cue, $pedido, 'N_COMP');

        /* Correspondiente al cuerpo del pedido */
        $busco = $pedido;
        $contar = 0;
        error_log('[' . date('Y-m-d H:i:s') . '] buscoPedido | Pedido: ' . $pedido . ' | Orden: ' . $orden_x . ' | Archivo: ' . $nombre_archivo);
        $this->tot_para_ped = 0;
        /* Prueba para armar el string de artículos */
        foreach ($this->resultados_array_gen as $articu) {

            $contar = ++$contar;
            /* Saco el código de artículo para que pueda ingresar correctamente a la base y matchear con los originales. */
            /* Busco la descripción del artículo */
            $search = array('/', '-');
            $replace = "";
            $articu_ini = substr($articu['COD_ARTICU'], 1);
            $subject = $articu_ini;
            $articu_formato = substr(str_replace($search, $replace, $subject), 0, -1);


            $this->ctrlArtsBase($articu_formato);
            /* Controlo si el artículo está en la base */

            if ($articu['N_COMP'] == $busco and $articu['ORDEN'] == $orden_x) {
                $nro_pedido = $articu['N_COMP'];
                if ($contar > 0) {

                    if ($this->ctrl_articu['COD_ARTICU'] == '') {
                        $descripcio = 'NO EXISTE EL ARTICULO EN LA BASE';
                    } else {
                        /* Controlo si el comprobante es expo para agregar el revend */
                        $pv_pedi = substr($nro_pedido, 0, 6);

                        if ($pv_pedi == 'E00011') {
                            /* Completa la descripción adicional */
                            $descripcio = str_pad($this->ctrl_articu['DESCRIPCIO'], 30, ' ', STR_PAD_RIGHT);

                            $revend_ok = str_replace(' - ', '-', $articu['REVEND']);
                            /* Corto revend para formar el string para las descripciones adicionales */
                            $cero_revend = '                   ';
                            $primer_revend = substr($revend_ok, 0, 29) . '';
                            $segundo_revend = substr($revend_ok, 18, 48) . '\r\n';
                            $tercer_revend = substr($revend_ok, 48, 66);
                            /* Controlo si el revend viene solo con el guión para no transportarlo como línea nueva */
                            if ($articu['REVEND'] === '-') {
                                $descrip_adic = '';
                            } else {
                                $descrip_adic = '                \r\n' . $primer_revend;
                            }
                        } else {
                            //$lista = 1;
                            $completo_con_30 = str_pad($this->ctrl_articu['DESCRIPCIO'], 30, ' ');
                            $descripcio = $this->ctrl_articu['DESCRIPCIO'] . '' . $articu['COD_ARTICU'];
                        }
                    }

                    /* Busco el cliente para poder condicionar las vearialbes que componen el json */
                    $this->busco_cliente($cliente);
                    //$articu['CANT'] * $articu['PRECIO_NETO'] * 1.21
                    $precio = $articu['TOTAL_RENGLON'];
                    $precio_art = $articu['PRECIO'];



                    /* Si el cliente es exento entonces reemplazo el precio original para que no calcule impuestos */
                    if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'EX') {
                        $cant_x_precio_neto = $precio;
                    } else {
                        $cant_x_precio_neto = $this->formatearNumero(($articu['TOTAL_RENGLON'] / 1.21), 2);
                    }


                    /* Traido del cliente */
                    $p_porc = $this->formatearNumero($this->tabla_cliente_cod_cliente['PORCENTAJE'], 2);
                    //$p_porc = 0.00;
                    $p_base = $cant_x_precio_neto;
                    //$p_base = 0;
                    $p_importe = $this->formatearNumero($p_base * ($p_porc / 100), 2);
                    //$p_importe = 0;

                    $p_importe_array[] = $this->formatearNumero($p_importe, 2);
                    $this->p_imp_importe = $p_importe_array;

                    /* Completo el IVA en el caso que el cliente sea CF */
                    if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'CF') {
                        $importe_iva_formato = $this->formatearNumero((($precio / 1.21) * 0.21), 2);
                        /* Sí el cliente es CF verifico tmb si tiene percepciones en la provincia y agrego el valor al json de percepciones */
                        if ($this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] != '') {
                            //Controlo si viene la 3er alicuota viene para poder calcularla
                            $percepciones = ',
                           "percepciones":
                                                [{
						"codigoAlicuota": ' . $this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] . ',
						"codigoPercepcion": "",
						"porcentaje": ' . $this->formatearNumero($p_porc, 2) . ',
						"base": ' . $this->formatearNumero($p_base, 2) . ',
						"importe": ' . $this->formatearNumero($p_importe, 2) . '
                                        	}]';
                            /* En el caso que el cliente sea CF y que tenga iibb se descuenta el importe por iteración. */
                            //$importe_iva_formato = (($precio / 1.21) * 0.21) - $p_importe;
                        }
                        $art_perc[] = $this->formatearNumero($p_importe, 2);
                        $this->art_total_perc = $art_perc;
                    }

                    /* Si el cliente */
                    if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'SNC') {
                        $p_snc = 10.5;
                        $p_snc_base = $precio;
                        $p_snc_importe = $this->formatearNumero($precio * ($p_snc / 100), 2);
                        /* Si el cliente es SNC entonces le sumo el IVA SNC */
                        $importe_iva = $this->formatearNumero(($cant_x_precio_neto * 0.21), 2);
                        $importe_iva_mas_snc = $this->formatearNumero(($cant_x_precio_neto * 0.21) + $p_snc_importe, 2);
                        $importe_iva_formato = $importe_iva;
                        if ($this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] != '') {
                            //Controlo si viene la 3er alicuota viene para poder calcularla
                            $alic = ',{
						"codigoAlicuota": ' . $this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] . ',
						"codigoPercepcion": "",
						"porcentaje": ' . $this->formatearNumero($p_porc, 2) . ',
						"base": ' . $this->formatearNumero($p_base, 2) . ',
						"importe": ' . $this->formatearNumero($p_importe, 2) . '
                                        	}';
                        } else {
                            $alic = '';
                        }

                        $percepciones = ',
                           "percepciones":
                                        [{
						"codigoAlicuota": 11,
						"codigoPercepcion": "",
						"porcentaje": ' . $this->formatearNumero($p_snc, 2) . ',
						"base": ' . $this->formatearNumero($p_snc_base, 2) . ',
						"importe": ' . $this->formatearNumero($p_snc_importe, 2) . '
					} ' . $alic . '
                                	]';


                        $art_tot_10_5[] = $this->formatearNumero($p_snc_importe, 2);
                        $art_perc[] = $this->formatearNumero($p_importe, 2);
                        //Tot IVA 10_5.
                        $this->art_total_10_50 = $art_tot_10_5;
                        //Tot Perc.
                        $this->art_total_perc = $art_perc;
                    }


                    if (empty($percepciones)) {
                        $percepciones = '';
                    }
                    /* Si el cliente tiene alícuota lo que hago es llenar la alícuota con su cálculo */

                    /* Si el cliente es excento entonces armo la variable del artículo sin los valores que llevan los datos de IVA */
                    if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'EX') {

                        $articulos[] = '{
				    "codigo": "' . $articu_formato . '",
                    "descripcion": "' . $descripcio . '",
                    "descripcionAdicional": "' . $descrip_adic . '",
				    "codigoDeposito": "1",
                    "codigoTasaIva": "3",
				    "cantidad": ' . $articu['CANT'] . ',
				    "precio": ' . $this->formatearNumero($precio_art, 2) . ',
                    "importe": ' . $this->formatearNumeroDosDecimales($precio, 2) . '
                        	}
                     ,';
                    } else {
                        /* Sí el cliente es CF y tiene iibb entonces al iva lo tengo que restar */
                        //if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'CF' AND $this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] != ''){
                        //    $importe_iva_formato = (($precio / 1.21) * 0.21) - $p_importe;
                        //}
                        /* Para los archivos que tienen un valor negativo (cabe-deta) cambio los valores que sean negativos porque no ingresan
                          y reemplazo la cantidada a negativo */

                        if ($precio_art < 0 or $precio < 0 or $cant_x_precio_neto < 0) {
                            $precio_art = $precio_art * -1;
                            $precio = $precio * -1;
                            $cant_x_precio_neto = $cant_x_precio_neto * -1;
                            $art_negativo = -1;
                        } else {
                            $art_negativo = 1;
                        }



                        $articulos[] = '{
				                        "codigo": "' . $articu_formato . '",
				                        "descripcion": "' . $descripcio . '",
                                        "descripcionAdicional": "' . $articu['COD_ARTICU'] . '",
				                        "descargaStock" : false,
				                        "cantidad": ' . $articu['CANT'] * $art_negativo . ',
                                        "importeIva": ' . $this->formatearNumero($importe_iva_formato, 2) . ',
				                        "codigoDeposito": "1",
				                        "codigoUM": "UNI",
				                        "precio": ' . $this->formatearNumero($precio_art, 2) . ',
                                        "importe": ' . $this->formatearNumero($precio, 2) . ',
                                         "importeSinImpuestos": ' . $this->formatearNumero($cant_x_precio_neto, 2) . $percepciones . '
                        	}
                     ,';


                        /* Configuro los totales para llevarlos al ingreso de comprobantes */
                        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'SNC') {
                            $art_tot_iva[] = $this->formatearNumero($importe_iva_mas_snc, 2);
                            $art_tot_iva_solo_snc[] = $this->formatearNumero($importe_iva_formato, 2);
                        } else {
                            $art_tot_iva[] = $this->formatearNumero($importe_iva_formato, 2);
                        }
                        //

                        $art_imp_sin_impuestos[] = $this->formatearNumero($cant_x_precio_neto, 2);

                        $tot_para_ped = $articu['TOTAL_RENGLON'];
                        $tot_2[] = $this->formatearNumero($tot_para_ped, 2);
                        $this->tot_para_ped = $tot_2;
                        //Tot iva.
                        $this->art_total_iva = $art_tot_iva;
                        if (!empty($art_tot_iva_solo_snc)) {
                            $this->art_total_solo_iva_snc = $art_tot_iva_solo_snc;
                        }
                        //Importe sin impuestos
                        $this->tot_arts_sin_impuestos = $art_imp_sin_impuestos;
                    }
                }
                $art_tot_precio[] = $this->formatearNumero($precio, 2);
            }
        }
        unset($this->dato_pedi_cue);
        $this->articulos = $articulos;
        //Subtotal
        $this->art_total_precio = $art_tot_precio;
    }

    public function normalizaFecha($fecha)
    {
        $fecha = trim($fecha ?? '');

        if (empty($fecha) || strlen($fecha) < 10) {
            return $this->fechaNormalizada = date('Y-m-d') . ' 00:00:00.000';
        }

        $anio = substr($fecha, 6, 4);
        $mes = substr($fecha, 3, 2);
        $dia = substr($fecha, 0, 2);

        if (is_numeric($anio) && is_numeric($mes) && is_numeric($dia)) {
            return $this->fechaNormalizada = $anio . '-' . $mes . '-' . $dia . ' 00:00:00.000';
        }

        return $this->fechaNormalizada = date('Y-m-d') . ' 00:00:00.000';
    }

    public $mensaje_api;

    /* Ingreso pedidos por API */
    public $clientesTango;

    /* Ejecuto la lectura del archivo para su posterior ingreso */

    public function procesoCsvClientes()
    {
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
                $nuevo_array_csv[] = array("CUIT" => $valor_csv[4], "COD_CLIENT" => $valor_csv[0], "RAZON_SOCI" => $valor_csv[2], "DOMIC" => $valor_csv[5], "LOCALIDAD" => $valor_csv[9], "CAT_IVA" => $valor_csv[17], "COD_PROVIN" => $valor_csv[16], "NUCUIT" => $valor_csv[18], "NUCUIL" => $valor_csv[19], "COD_VENDED" => $valor_csv[25], "TIP_DOC" => $valor_csv[3], "ALIC_PERC" => $valor_csv[24], "COD_POST" => $valor_csv[14], "E_MAIL" => $valor_csv[26], "NOMBRE_ARCHIVO" => $valor_csv[27]);
            }

            foreach ($nuevo_array_csv as $cliente) {
                $this->maxIdGva14();
                /* Cambio de variable en base a tipdoc en el caso que venga 96 sería CUIT que sería el docnro del CSV */
                if ($cliente['TIP_DOC'] == 96) {
                    $cuit_dni = $cliente['CUIT'];
                }
                if ($cliente['TIP_DOC'] == 90) {
                    $cuit_dni = $cliente['CUIT'];
                }
                if ($cliente['TIP_DOC'] == 80) {
                    $cuit_dni = $cliente['NUCUIT'];
                }

                if ($cliente['TIP_DOC'] == 86) {
                    $cuit_dni = $cliente['NUCUIL'];
                }
                /* Controlo si el cliente existe para su ingreso */
                $this->selectGva14($cliente['COD_CLIENT']);
                /* Controlo si el CUIT que estoy recorriendo es distinto al que está en la base */
                if (empty($this->selectGva14['COD_CLIENT'])) {

                    /*Quito la validación porque verifico si el código de cliente existe directamente ya que podría ser 1 solo
                    if ($this->selectGva14['CUIT'] !== $cuit_dni) {
                     *                      */

                    $maxId = $this->maxIdGva14['COD_CLIENT'] + 1;
                    $this->ingresoCliente($maxId, $cuit_dni, $cliente['RAZON_SOCI'], $cliente['DOMIC'], $cliente['LOCALIDAD'], $cliente['CAT_IVA'], $cliente['COD_PROVIN'], $cliente['COD_CLIENT'], $cliente['NUCUIT'], $cliente['COD_VENDED'], $cliente['TIP_DOC'], $cliente['ALIC_PERC'], $cliente['COD_POST'], $cliente['NUCUIL'], $cliente['E_MAIL']);
                    /* Ingreso el dato de alícuota de cliente para usar en trigger de facturación */
                    //$this->ingresoClieAlic($maxId, $cliente['CAT_IVA'], $cliente['ALIC_PERC']);

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
                        echo "<div style='color: #dc3545; font-family: monospace; font-size: 14px; padding: 6px; border-bottom: 1px solid #444; margin-bottom: 2px;'>✘ Existe un error: " . htmlspecialchars($stringConvertido) . "</div>";
                    } else {
                        $grabo = 1;
                        echo "<div style='color: #4caf50; font-family: monospace; font-size: 14px; padding: 6px; border-bottom: 1px solid #444; margin-bottom: 2px;'>✔ Se grabó correctamente el cliente: " . htmlspecialchars($cliente['RAZON_SOCI']) . " en el archivo: " . htmlspecialchars($cliente['NOMBRE_ARCHIVO']) . "</div>";
                    }

                    $mensaje_api = 'ID_INTERNO: ' . $id . ' Mensaje: ' . $stringConvertido . ' ¿Grabó?: ' . $this->mensaje_api['succeeded'];
                    $this->ingresoMensajesApi($cuit_dni, 'CLIENTES', $mensaje_api, $grabo, $cliente['COD_CLIENT'], $cliente['NOMBRE_ARCHIVO'], 0, $stringConvertido, $maxId, $cliente['RAZON_SOCI']);
                } else {
                    //$stringConvertido = $this->convertirATexto($this->mensaje_api);
                    $this->busco_cliente($cliente['COD_CLIENT']);
                    echo "<div style='color: #ff9800; font-family: monospace; font-size: 14px; padding: 6px; border-bottom: 1px solid #444; margin-bottom: 2px;'>⚠ El cliente con DNI/CUIL: {$cuit_dni} (Tango: {$cliente['COD_CLIENT']}) ya existe.</div>";
                    $this->ingresoMensajesApi($cuit_dni, 'CLIENTES', 'SE PROCESO PERO YA EXISTIA EN LA BASE ', 0, $cliente['COD_CLIENT'], $cliente['NOMBRE_ARCHIVO'], 0, '', '', '');
                    $this->actTipoDocCliente($this->tabla_cliente_cod_cliente['COD_CLIENT'], $cliente['TIP_DOC'], $cuit_dni, $cliente['E_MAIL'], $cliente['COD_CLIENT']);
                    //$this->ingresoClieAlic($this->tabla_cliente_cod_cliente['COD_CLIENT'], $cliente['CAT_IVA'], $cliente['ALIC_PERC']);
                }
            }
            /* Limpio los archivos a procesar */
            $this->actualizoArchivoClientesImpo();
            return;
        }
        echo "<div style='color: #ff9800; font-family: monospace; font-size: 14px; padding: 6px; border-bottom: 1px solid #444; margin-bottom: 2px;'>⚠ No hay archivos de clientes para procesar.</div>";
        return;
    }

    /* Convertir el array de mensaje a texto */

    public function convertirATexto($array, $nivel = 0)
    {
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

    public function ingresoPedido($cliente, $importe, $articulo, $nro_pedido, $cod_zona, $imp_sin_impuestos, $fecha, $bonif_cosme, $practicosas, $gastadmin, $imp_iva, $bonif_adicional)
    {

        /* Busco la configuración del server desde la tabla */
        $this->devuelvoTokens();

        $token = $this->token['API_LOCAL'];
        $id_company = $this->token['ID_EMPRESA'];

        $url = $this->token['RUTA_LOCAL'] . '/Api/Create?process=19845';
        //$url = "http://srvcharly:17000/Api/Create?process=19845";

        //Revisar bien para eliminar
        $this->ingresoPedidoControl($nro_pedido, $cliente);

        /* Busco el COD_CLIENT original */
        $this->busco_cliente($cliente);

        /* Verifico el punto de venta del pedido para apuntarlo al talonario */
        //Extraigo el PV del pedido
        $pv_pedi = substr($nro_pedido, 0, 6);
        //echo $pv_pedi.'<BR>';
        if ($pv_pedi == 'B00003') {
            $talon_pedi = 10;
            $talon_fac = 5;
            $param_numeracion = 'FAC_B';
            $lista = 1;
        }

        if ($pv_pedi == 'B00007') {
            $talon_pedi = 10;
            $talon_fac = 15;
            $param_numeracion = 'FAC_ECOMMERCE';
            $lista = 1;
        }

        if ($pv_pedi == 'A00003') {
            $talon_pedi = 10;
            $talon_fac = 1;
            $lista = 1;
        }

        if ($pv_pedi == 'E00011') {
            $talon_pedi = 10;
            $talon_fac = 22;
            $lista = 2;
            $param_numeracion = 'FAC_E_EXPO';
        }

        $ID_GVA01 = 1; //Condición de venta
        $ID_GVA23 = $this->tabla_cliente_cod_cliente['ID_GVA23']; //Zona
        $ID_GVA10 = $lista;
        $ID_GVA24 = 1;
        $id_perfil = 1;

        /* 
         * Reproducción lógica de Factura: inyección de artículos fijos (bonificaciones y adicionales) 
         * al array de renglones ($articulo).
         * Operando con la matemáticas escalar requerida para la vista de PEDIDO (RENGLON_DTO), 
         * sin procesar lógica impositiva.
         */

        if ($bonif_cosme > 0) {
            $articulo[] = [
                "ID_STA11" => "4", // Código genérico Factura "03" Bonificacion Cosméticos
                "CANTIDAD_PEDIDA" => -1,
                "DESCRIPCION_ARTICULO" => "Bonificacion Cosméticos",
                "DESCRIPCION_ADICIONAL_ARTICULO" => "",
                "CANTIDAD_A_FACTURAR" => -1,
                "CANTIDAD_PENDIENTE_A_FACTURAR" => -1,
                "PRECIO" => $bonif_cosme,
                "PORCENTAJE_BONIFICACION" => 0,
                "OBSERVACIONES" => null
            ];
        }

        if ($practicosas > 0) {
            $articulo[] = [
                "ID_STA11" => "5", // Código genérico Factura "04" Bonificación Practicosas
                "CANTIDAD_PEDIDA" => -1,
                "DESCRIPCION_ARTICULO" => "Bonificación Practicosas",
                "DESCRIPCION_ADICIONAL_ARTICULO" => "",
                "CANTIDAD_A_FACTURAR" => -1,
                "CANTIDAD_PENDIENTE_A_FACTURAR" => -1,
                "PRECIO" => $practicosas,
                "PORCENTAJE_BONIFICACION" => 0,
                "OBSERVACIONES" => null
            ];
        }

        if ($bonif_adicional > 0) {
            $articulo[] = [
                "ID_STA11" => "1300", // Código genérico Factura "06" Bonificación adicional
                "CANTIDAD_PEDIDA" => -1,
                "DESCRIPCION_ARTICULO" => "Bonificación adicional",
                "DESCRIPCION_ADICIONAL_ARTICULO" => "",
                "CANTIDAD_A_FACTURAR" => -1,
                "CANTIDAD_PENDIENTE_A_FACTURAR" => -1,
                "PRECIO" => $bonif_adicional,
                "PORCENTAJE_BONIFICACION" => 0,
                "OBSERVACIONES" => null
            ];
        }

        if ($gastadmin > 0) {
            $articulo[] = [
                "ID_STA11" => "3", // Código genérico Factura "02" Gastos administrativos
                "CANTIDAD_PEDIDA" => 1,
                "DESCRIPCION_ARTICULO" => "Gastos administrativos",
                "DESCRIPCION_ADICIONAL_ARTICULO" => "",
                "CANTIDAD_A_FACTURAR" => 1,
                "CANTIDAD_PENDIENTE_A_FACTURAR" => 1,
                "PRECIO" => $gastadmin,
                "PORCENTAJE_BONIFICACION" => 0,
                "OBSERVACIONES" => null
            ];
        }

        /* Json para pedidos*/
        $data_string = [
            "ID_GVA43_TALON_PED" => 1, //Talonario para el pedido en este caso para la base LADY 1.
            "ESTADO" => 2,
            "ID_GVA14" => $this->tabla_cliente_cod_cliente['ID_GVA14'], //tabla_cliente_cod_cliente
            "ES_CLIENTE_HABITUAL" => true,
            "ID_GVA01" => $ID_GVA01,
            "ID_GVA23" => $ID_GVA23,
            "ID_STA22" => 1, // se deja el deposito fijo por el momento.
            "ID_GVA10" => $ID_GVA10,
            "ID_GVA24" => $ID_GVA24,
            "ID_MONEDA" => 1,
            "FECHA_ENTREGA" => null, // que fecha colocar?se deja fijo por le momento.
            "id_sba01" => null,
            "ID_GVA43_TALONARIO_FACTURA" => 2,
            "COMPROMETE_STOCK" => false,
            "ACTIVIDAD_COMPROBANTE_AFIP" => null,
            "LEYENDA_1" => "",
            "LEYENDA_2" => "",
            "LEYENDA_3" => "",
            "LEYENDA_4" => "",
            "LEYENDA_5" => "",
            "PORCENTAJE_DESCUENTO_GENERAL" => 0,
            "IMPORTE_DESCUENTO_GENERAL" => 0,
            "APLICA_DESCUENTO_CLIENTE" => false,
            "ID_PERFIL_PEDIDO" => $id_perfil,
            "OBSERVACIONES" => "",
            "RENGLON_DTO" => $articulo
        ];

        //echo 'Artículo en encabezado: <br>';
        //print_r($data_string);

        // Se convierte el array $pedido en una cadena JSON para enviarlo a la API
        $json_data = json_encode($data_string);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        //Configuro el encabezado
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "ApiAuthorization: $token",   // verificar si es correcto
            "Company: $id_company",
            "Content-Type: application/json",
            "Content-Length: " . strlen($json_data)
        ]);
        //echo 'Llego hasta acá: <br>';
        $response = curl_exec($ch);

        //obtengo el codigo de estado de la petición
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            error_log('[' . date('Y-m-d H:i:s') . '] Error cURL: ' . $error);
            return null;
        }

        if ($http_code < 200 || $http_code > 299) {
            error_log('[' . date('Y-m-d H:i:s') . '] Error al ingresar pedido. HTTP Code: ' . $http_code . ' Response: ' . $response . ' | Detalle artículo: ' . print_r($articulo, true));
            return null;
        }

        $data2 = json_decode($response, true);
        if (is_array($data2)) {
            $this->mensaje_api = $data2;
        } else {
            $this->mensaje_api = [
                'message' => 'Respuesta inválida API',
                'exceptionInfo' => $response,
                'succeeded' => false
            ];
        }

        $ahora = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
        /* Uno es para ver el formato y el otro es para dejar la comita */
        $formateado = $ahora->format("Y-m-d H:i:s.u");
        $formateado = ',';
        $fecha = "archivos_json/" . date('Y-m-d-H_i_s');
        //
        if (file_exists("fc_json.txt")) {
            $archivo = fopen("archivos_json/fc_json.json", "a");
            //Modififico el valor de la variable formateado con el fin de dejar la comita para el ingreso del json en cliente API
            fwrite($archivo, PHP_EOL . "$json_data");
            fclose($archivo);
        } else {
            $archivo = fopen($fecha . ".json", "w");
            fwrite($archivo, PHP_EOL . "$json_data");
            //en la cabeza 
            fclose($archivo);
        }



        curl_close($ch);
    }

    /* Método para el ingreso de artículos */

    public function ingresoArticulo($cod_articulo, $descripcio, $desc_adic)
    {
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

        //$verbose = fopen('log_curl.txt', 'w+');
        //curl_setopt($ch, CURLOPT_STDERR, $verbose);
        // get stringified data/output. See CURLOPT_RETURNTRANSFER
        $data = curl_exec($ch);

        // get info about the request
        $info = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        /* Hardening Linux: control de error de red en curl */
        if ($data === false) {
            $curl_error = curl_error($ch);
            error_log('[' . date('Y-m-d H:i:s') . '] CURL ERROR en ingresoArticulo | Artículo: ' . $cod_articulo . ' | Error: ' . $curl_error);
            $data2 = null;
        } else {
            $data2 = json_decode($data, true);
        }

        $this->mensaje_api = $data2;

        curl_close($ch);
    }

    /* Método que genera la lectura e ingreso a sistema de los datos del CSV */

    public function procesoCsvArticulos()
    {
        $this->articulosCsv();

        /* Controlo que el array de archivos venga completo para ingresar al proceso */
        if (isset($this->arts_csv)) {
            foreach ($this->arts_csv as $valor_csv) {
                $matriz_ars[] = array("COD_ARTICU" => $valor_csv[0], "DESCRIPCIO" => $valor_csv[2], "NOMBRE_ARCHIVO" => $valor_csv[24]);
            }


            /* Recorro los artículos del array */
            foreach ($matriz_ars as $arts_csv) {
                /* Hago el control del artículo que está ingresar a la base */
                $this->ctrlArtsBase($arts_csv['COD_ARTICU']);

                if ($this->ctrl_articu['COD_ARTICU'] == $arts_csv['COD_ARTICU']) {
                    echo "<div style='color: #ff9800; font-family: monospace; font-size: 14px; padding: 6px; border-bottom: 1px solid #444; margin-bottom: 2px;'>⚠ El artículo {$arts_csv['COD_ARTICU']} ya existe en la base de datos local.</div>";
                    $this->ingresoMensajesApi($arts_csv['COD_ARTICU'], 'ARTICULOS', 'NO-HAY-ARTICULOS-PARA-PROCESAR SIN EMBARGO SE VERIFICA LA EXISTENCIA DEL ARTICULO: ' . $arts_csv['COD_ARTICU'], 0, '', $arts_csv['NOMBRE_ARCHIVO'], 0, '', '', '');
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
                        echo "<div style='color: #dc3545; font-family: monospace; font-size: 14px; padding: 6px; border-bottom: 1px solid #444; margin-bottom: 2px;'>✘ Existe un error de carga: " . htmlspecialchars($stringConvertido) . "</div>";
                        $grabo = 0;
                    } else {
                        $grabo = 1;
                        echo "<div style='color: #4caf50; font-family: monospace; font-size: 14px; padding: 6px; border-bottom: 1px solid #444; margin-bottom: 2px;'>✔ Se grabó correctamente el artículo: " . htmlspecialchars($arts_csv['COD_ARTICU']) . "</div>";
                    }

                    $mensaje_api = 'ID_INTERNO: ' . $id . ' Mensaje: ' . $stringConvertido . ' ¿Grabó?: ' . $this->mensaje_api['succeeded'];


                    $this->ingresoMensajesApi($arts_csv['COD_ARTICU'], 'ARTICULOS', $mensaje_api, $grabo, '', $arts_csv['NOMBRE_ARCHIVO'], 0, $stringConvertido, '', '');
                }
                /* Limpio el excel importado actualizando el archivo importado a procesado P */
                $this->actualizoArchivoArticuloImpo();
            }
        }
    }

    public $arts_csv;

    public function articulosCsv()
    {
        /* Ejecuto el método que guarda el nombre en un array */
        $this->leoArchivosBdArt();
        if (isset($this->nombre_archivo_art)) {
            foreach ($this->nombre_archivo_art as $archivo) {
                /* Leo el nombre de archivo en directorio */
                $archivo2 = fopen($this->leoParametroBd('RUTAXML') . '/' . $archivo['NOMBRE_ARCHIVO'], "r");

                while (($datos = fgetcsv($archivo2, 0, ",")) == true) {
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
                }

                //Cerramos el archivo
                fclose($archivo2);
            }
        } else {
            echo "<div style='color: #ff9800; font-family: monospace; font-size: 14px; padding: 6px; border-bottom: 1px solid #444; margin-bottom: 2px;'>⚠ No hay archivos de artículos para procesar.</div>";
            $this->ingresoMensajesApi('SIN', 'ARTICULOS', 'NO-HAY-ARCHIVOS-PARA-PROCESAR', 0, '', '', 0, '', '', '');
        }
    }

    public function ingresoFactura($cliente, $importe, $articulo, $nro_pedido, $cod_zona, $imp_sin_impuestos, $fecha, $bonif_cosme, $practicosas, $gastadmin, $imp_iva, $bonif_adicional)
    {
        $token = '
        {
        "ApiAuthorization": "' . $this->token_api_local . '";
        "Company: "' . $this->id_empresa . '"
        }
        ';

        //$bonif_adicional = $this->redondearNumeroEspecial($bonif_adicional);
        //$gastadmin = $this->redondearNumeroEspecial($gastadmin);
        //$practicosas = $this->redondearNumeroEspecial($practicosas);
        //$bonif_cosme = $this->redondearNumeroEspecial($bonif_cosme);

        /* Busco la configuración del server desde la tabla */
        $this->devuelvoTokens();

        //setup the request, you can also use CURLOPT_URL
        $ch = curl_init($this->token['RUTA_LOCAL'] . '/FacturadorVenta/registrar');

        //curl_setopt($ch, CURLOPT_TCP_FASTOPEN, true);

        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");

        curl_setopt($ch, CURLOPT_BUFFERSIZE, 10485764);

        curl_setopt($ch, CURLOPT_NOPROGRESS, false);

        //curl_setopt($ch, CURLOPT_POSTREDIR, CURL_REDIR_POST_ALL);

        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);

        curl_setopt($ch, CURLOPT_ENCODING, '');

        $this->ingresoPedidoControl($nro_pedido, $cliente);


        /* Busco el COD_CLIENT original */
        $this->busco_cliente($cliente);

        /* Verifico el punto de venta del pedido para apuntarlo al talonario */
        //Extraigo el PV del pedido
        $pv_pedi = substr($nro_pedido, 0, 6);
        if ($pv_pedi == 'B00003') {
            $talon_pedi = 10;
            $talon_fac = 5;
            $param_numeracion = 'FAC_B';
            $lista = 1;
        }

        if ($pv_pedi == 'B00007') {
            $talon_pedi = 10;
            $talon_fac = 15;
            $param_numeracion = 'FAC_ECOMMERCE';
            $lista = 1;
        }

        if ($pv_pedi == 'A00003') {
            $talon_pedi = 10;
            $talon_fac = 1;
            $lista = 1;
        }

        if ($pv_pedi == 'E00011') {
            $talon_pedi = 10;
            $talon_fac = 22;
            $lista = 2;
            $param_numeracion = 'FAC_E_EXPO';
        }

        $this->param_numeracion = $param_numeracion;

        $this->devuelvoIdPedido($param_numeracion);

        /* Busco cliente */
        $this->busco_cliente($cliente);


        $bonif_cosme_sin_iva = ($bonif_cosme / 1.21);
        $precio_bonif = $bonif_cosme;
        $p_porc_cosme = $this->tabla_cliente_cod_cliente['PORCENTAJE'];
        $p_cosme_base = $bonif_cosme_sin_iva;
        $p_cosme_importe = $bonif_cosme_sin_iva * ($p_porc_cosme / 100);

        /* Completo el IVA en el caso que el cliente sea CF */
        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'CF') {
            $bonif_cosme_iva_formato = $this->formatearNumero((($bonif_cosme / 1.21) * 0.21), 2);
            if ($this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] != '') {
                $percepciones = '
                ,"percepciones":
                                                [{
						"codigoAlicuota": ' . $this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] . ',
						"codigoPercepcion": "",
						"porcentaje": ' . $this->formatearNumero($p_porc_cosme, 2) . ',
						"base": -' . $this->formatearNumero($p_cosme_base, 2) . ',
						"importe": -' . $this->formatearNumero($p_cosme_importe, 2) . '
                                        	}]';
                $this->tot_bonif_ali = $this->formatearNumero($p_cosme_importe, 2);
            }
        }



        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'SNC') {
            $p_snc_cosme = 10.5;
            $p_snc_cosme_base = $bonif_cosme;
            $p_snc_cosme_importe = $this->formatearNumero($precio_bonif * ($p_snc_cosme / 100), 2);
            $bonif_cosme_iva = $this->formatearNumero(($bonif_cosme_sin_iva * 0.21), 2);
            $bonif_cosme_iva_mas_snc = $this->formatearNumero(($bonif_cosme_sin_iva * 0.21) + $p_snc_cosme_importe, 2);
            $bonif_cosme_iva_formato = $bonif_cosme_iva;

            if ($this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] != '') {
                $alic = ',{
						"codigoAlicuota": ' . $this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] . ',
						"codigoPercepcion": "",
						"porcentaje": ' . $this->formatearNumero($p_porc_cosme, 2) . ',
						"base": -' . $this->formatearNumero($p_cosme_base, 2) . ',
						"importe": -' . $this->formatearNumero($p_cosme_importe, 2) . '
                                        	}';
            }
            if (empty($alic)) {
                $alic = '';
            }

            $percepciones = ',"percepciones":
                                        [{
						"codigoAlicuota": 11,
						"codigoPercepcion": "",
						"porcentaje" : ' . $this->formatearNumero($p_snc_cosme, 2) . ',
						"base": -' . $this->formatearNumero($p_snc_cosme_base, 2) . ',
						"importe": -' . $this->formatearNumero($p_snc_cosme_importe, 2) . '
					} ' . $alic . '
                                	]';

            $this->tot_bonif_10_5 = $this->formatearNumero($p_snc_cosme_importe, 2);
            $this->tot_bonif_ali = $this->formatearNumero($p_cosme_importe, 2);
        }

        if (empty($percepciones)) {
            $percepciones = '';
        }

        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'EX') {
            $bonif_cosme_json = '{
	"codigo": "03",
	"descripcion": "Bonificacion Cosméticos",
	"codigoDeposito": "1",
        "codigoTasaIva": "3",
	"cantidad": -1,
	"precio": ' . $this->formatearNumero($precio_bonif, 2) . ',
        "importe": ' . $this->formatearNumero($precio_bonif, 2) . '
        },';
        } else {

            $bonif_cosme_json = '{
	"codigo": "03",
	"descripcion": "Bonificacion Cosméticos",
	"descargaStock" : false,
	"cantidad": -1,
	"codigoDeposito": "1",
        "importeIva": -' . $this->formatearNumero($bonif_cosme_iva_formato, 2) . ',
	"codigoUM": "BON",
	"precio": ' . $this->formatearNumero($precio_bonif, 2) . ',
        "importe": ' . $this->formatearNumero($precio_bonif, 2) . ',
        "importeSinImpuestos": -' . $this->formatearNumero($bonif_cosme_sin_iva, 2) . $percepciones . '
        },';
            //Configuro los totales a sumar.
            if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'SNC') {
                $this->tot_bonificaciones_iva = $this->formatearNumero($bonif_cosme_iva_mas_snc, 2);
            } else {
                $this->tot_bonificaciones_iva = $this->formatearNumero($bonif_cosme_iva_formato, 2);
            }
            $this->tot_bonif_sin_impuestos = $this->formatearNumero($bonif_cosme_sin_iva, 2);
            $this->tot_bonif_subtotal = $this->formatearNumero($precio_bonif, 2);
        }
        $this->tot_bonificaciones = $this->formatearNumero($precio_bonif, 2);

        /* Verifico si la bonificación adicional es mayor que cero y en el caso que se así la agrego en el json */
        if ($bonif_adicional > 0) {

            $bonif_adicional_sin_iva = ($bonif_adicional / 1.21);
            $precio_bonif_adicional = $bonif_adicional;
            $p_porc_bonif_adicional = $this->tabla_cliente_cod_cliente['PORCENTAJE'];
            $p_bonif_adicional_base = $bonif_adicional_sin_iva;
            $p_bonif_adicional_importe = $bonif_adicional_sin_iva * ($p_porc_bonif_adicional / 100);

            /* Completo el IVA en el caso que el cliente sea CF */
            if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'CF') {
                $bonif_adicional_iva_formato = $this->formatearNumero((($bonif_adicional / 1.21) * 0.21), 2);
                //$bonif_bonif_adicional_iva_formato = $this->formatearNumero($bonif_adicional * 0.21,2);
                if ($this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] != '') {
                    $percepciones_bonif_adicional = '
                           ,"percepciones":
                                                [{
						"codigoAlicuota": ' . $this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] . ',
						"codigoPercepcion": "",
						"porcentaje": ' . $this->formatearNumero($p_porc_bonif_adicional, 2) . ',
						"base": -' . $this->formatearNumero($p_bonif_adicional_base, 2) . ',
						"importe": -' . $this->formatearNumero($p_bonif_adicional_importe, 2) . '
                                        	}]';

                    $this->tot_bonif_adicional_ali = $this->formatearNumero($p_bonif_adicional_importe, 2);
                }
            }


            if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'SNC') {
                $p_snc_bonif_adicional = 10.5;
                $p_snc_bonif_adicional_base = $bonif_adicional;
                $p_snc_bonif_adicional_importe = $this->formatearNumero($precio_bonif_adicional * ($p_snc_bonif_adicional / 100), 2);
                $p_snc_bonif_adicional_iva = $this->formatearNumero($bonif_adicional_sin_iva * 0.21, 2);
                $bonif_adicional_iva_mas_snc = $this->formatearNumero(($bonif_adicional_sin_iva * 0.21) + $p_snc_bonif_adicional_importe, 2);
                $bonif_adicional_iva_formato = $p_snc_bonif_adicional_iva;

                if ($this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] != '') {
                    $alic_bonif_adicional = ',{
						"codigoAlicuota": ' . $this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] . ',
						"codigoPercepcion": "",
						"porcentaje": ' . $this->formatearNumero($p_porc_bonif_adicional, 2) . ',
						"base": -' . $this->formatearNumero($p_bonif_adicional_base, 2) . ',
						"importe": -' . $this->formatearNumero($p_bonif_adicional_importe, 2) . '
                                        	}';
                }

                if (empty($alic_bonif_adicional)) {
                    $alic_bonif_adicional = '';
                }

                $percepciones_bonif_adicional = ',"percepciones":
                                        [{
						"codigoAlicuota": 11,
						"codigoPercepcion": "",
						"porcentaje" : ' . $this->formatearNumero($p_snc_bonif_adicional, 2) . ',
						"base": -' . $this->formatearNumero($p_snc_bonif_adicional_base, 2) . ',
						"importe": -' . $this->formatearNumero($p_snc_bonif_adicional_importe, 2) . '
					} ' . $alic_bonif_adicional . '
                                	]';
                $this->tot_bonif_adicional_10_5 = $this->formatearNumero($p_snc_bonif_adicional_importe, 2);
                $this->tot_bonif_adicional_ali = $this->formatearNumero($p_bonif_adicional_importe, 2);
                $this->tot_bonif_adicional_iva = $this->formatearNumero($bonif_adicional_iva_formato, 2);

                if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'SNC') {
                    $this->tot_bonif_adicional_iva = $this->formatearNumero($bonif_adicional_iva_mas_snc, 2);
                }
            }

            if (empty($percepciones_bonif_adicional)) {
                $percepciones_bonif_adicional = 0;
            }

            if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'EX') {
                //if($precio_bonif_adicional == 0){
                //    $precio_bonif_adicional = -.01;
                //}
                $bonif_adicional_json = '{
	"codigo": "06",
	"descripcion": "Bonificación adicional",
	"codigoDeposito": "1",
        "codigoTasaIva": "3",
	"cantidad": -1,
	"precio": ' . $this->formatearNumero($precio_bonif_adicional, 2) . ',
        "importe": ' . $this->formatearNumero($precio_bonif_adicional, 2) . '
        },';
                //$bonif_adicional = '';
                $this->tot_bonif_adicional = $precio_bonif_adicional;
                $this->tot_bonif_adicional_iva = 0;
                $this->tot_bonif_adicional_sin_impuestos = 0;
                $this->tot_bonif_adicional_subtotal = $precio_bonif_adicional;
            } else {
                $bonif_adicional_json = '{
				"codigo": "06",
				"descripcion": "Bonificación adicional",
				"descargaStock" : false,
				"cantidad": -1,
				"codigoDeposito": "1",
                                "importeIva": -' . $this->formatearNumero($bonif_adicional_iva_formato, 2) . ',
				"codigoUM": "BON",
				"precio": ' . $this->formatearNumero($precio_bonif_adicional, 2) . ',
                                "importe": ' . $this->formatearNumero($precio_bonif_adicional, 2) . ',
                                "importeSinImpuestos": -' . $this->formatearNumero($bonif_adicional_sin_iva, 2) . $percepciones_bonif_adicional . '
                        	},
                     ';
            }

            if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'SNC') {
                $this->tot_bonif_adicional_iva = $this->formatearNumero($bonif_adicional_iva_mas_snc, 2);
            } else {
                if (isset($bonif_adicional_iva_formato)) {
                    $this->tot_bonif_adicional_iva = $this->formatearNumero($bonif_adicional_iva_formato, 2);
                }
            }

            //Configuro los totales a sumar.
            $this->tot_bonif_adicional = $this->formatearNumero($precio_bonif_adicional, 2);
            $this->tot_bonif_adicional_sin_impuestos = $this->formatearNumero($bonif_adicional_sin_iva, 2);
            $this->tot_bonif_adicional_subtotal = $this->formatearNumero($precio_bonif_adicional, 2);
        } else {
            $bonif_adicional = '';
            $this->tot_bonif_adicional = 0;
            if (empty($this->tot_bonif_adicional_iva)) {
                $this->tot_bonif_adicional_iva = 0;
            }
            $this->tot_bonif_adicional_sin_impuestos = 0;
            $this->tot_bonif_adicional_subtotal = 0;
        }

        $bonif_practicosas_sin_iva = ($practicosas / 1.21);
        $precio_practicosas = $practicosas;
        $p_porc_practicosas = $this->tabla_cliente_cod_cliente['PORCENTAJE'];
        $p_practicosas_base = $bonif_practicosas_sin_iva;
        $p_practicosas_importe = $bonif_practicosas_sin_iva * ($p_porc_practicosas / 100);

        /* Completo el IVA en el caso que el cliente sea CF */
        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'CF') {
            $bonif_practicosas_iva_formato = $this->formatearNumero((($practicosas / 1.21) * 0.21), 2);
            //$bonif_practicosas_iva_formato = $this->formatearNumero($practicosas * 0.21,2);
            if ($this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] != '') {
                $percepciones_practicosas = '
                           ,"percepciones":
                                                [{
						"codigoAlicuota": ' . $this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] . ',
						"codigoPercepcion": "",
						"porcentaje": ' . $this->formatearNumero($p_porc_practicosas, 2) . ',
						"base": -' . $this->formatearNumero($p_practicosas_base, 2) . ',
						"importe": -' . $this->formatearNumero($p_practicosas_importe, 2) . '
                                        	}]';
                $this->tot_practicosas_ali = $this->formatearNumero($p_practicosas_importe, 2);
            }
        }


        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'SNC') {
            $p_snc_practicosas = 10.5;
            $p_snc_practicosas_base = $practicosas;
            $p_snc_practicosas_importe = $this->formatearNumero($precio_practicosas * ($p_snc_practicosas / 100), 2);
            $bonif_practicosas_iva = $this->formatearNumero($bonif_practicosas_sin_iva * 0.21, 2);
            $bonif_practicosas_iva_mas_snc = $this->formatearNumero($bonif_practicosas_sin_iva * 0.21 + $p_snc_practicosas_importe, 2);
            $bonif_practicosas_iva_formato = $bonif_practicosas_iva;

            if ($this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] != '') {
                $alic_practicosas = ',{
						"codigoAlicuota": ' . $this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] . ',
						"codigoPercepcion": "",
						"porcentaje": ' . $this->formatearNumero($p_porc_practicosas, 2) . ',
						"base": -' . $this->formatearNumero($p_practicosas_base, 2) . ',
						"importe": -' . $this->formatearNumero($p_practicosas_importe, 2) . '
                                        	}';
            }

            if (empty($alic_practicosas)) {
                $alic_practicosas = '';
            }

            $percepciones_practicosas = ',"percepciones":
                                        [{
						"codigoAlicuota": 11,
						"codigoPercepcion": "",
						"porcentaje" : ' . $this->formatearNumero($p_snc_practicosas, 2) . ',
						"base": -' . $this->formatearNumero($p_snc_practicosas_base, 2) . ',
						"importe": -' . $this->formatearNumero($p_snc_practicosas_importe, 2) . '
					} ' . $alic_practicosas . '
                                	]';
            $this->tot_practicosas_10_5 = $this->formatearNumero($p_snc_practicosas_importe, 2);
            $this->tot_practicosas_ali = $this->formatearNumero($p_practicosas_importe, 2);
        }
        if (empty($percepciones_practicosas)) {
            $percepciones_practicosas = '';
        }
        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'EX') {
            $practicosas_json = '{
	"codigo": "04",
	"descripcion": "Bonificación Practicosas",
	"codigoDeposito": "1",
        "codigoTasaIva": "3",
	"cantidad": -1,
	"precio": ' . $this->formatearNumero($precio_practicosas, 2) . ',
        "importe": ' . $this->formatearNumero($precio_practicosas, 2) . '
        },';
            $this->tot_practicosas = $this->formatearNumero($precio_practicosas, 2);
        } else {
            $practicosas_json = '{
				"codigo": "04",
				"descripcion": "Bonificación Practicosas",
				"descargaStock" : false,
				"cantidad": -1,
				"codigoDeposito": "1",
                                "importeIva": -' . $this->formatearNumero($bonif_practicosas_iva_formato, 2) . ',
				"codigoUM": "BON",
				"precio": ' . $this->formatearNumero($precio_practicosas, 2) . ',
                                "importe": ' . $this->formatearNumero($precio_practicosas, 2) . ',
                                "importeSinImpuestos": -' . $this->formatearNumero($bonif_practicosas_sin_iva, 2) . $percepciones_practicosas . '
                        	},
                                
                     ';
            //Configuro los totales a sumar.
            $this->tot_practicosas = $this->formatearNumero($precio_practicosas, 2);
            if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'SNC') {
                $this->tot_practicosas_iva = $this->formatearNumero($bonif_practicosas_iva_mas_snc, 2);
            } else {
                $this->tot_practicosas_iva = $this->formatearNumero($bonif_practicosas_iva_formato, 2);
            }
            $this->tot_practicosas_sin_impuestos = $this->formatearNumero($bonif_practicosas_sin_iva, 2);
            $this->tot_practicosas_subtotal = $this->formatearNumero($precio_practicosas, 2);
        }

        $bonif_gastos_administrativos_sin_iva = ($gastadmin / 1.21);
        $precio_gastos_administrativos = $gastadmin;
        $p_porc_gastos_administrativos = $this->tabla_cliente_cod_cliente['PORCENTAJE'];
        $p_gastos_administrativos_base = $bonif_gastos_administrativos_sin_iva;
        $p_gastos_administrativos_importe = $bonif_gastos_administrativos_sin_iva * ($p_porc_gastos_administrativos / 100);

        /* Completo el IVA en el caso que el cliente sea CF */
        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'CF') {
            $bonif_gastos_administrativos_iva_formato = $this->formatearNumero((($gastadmin / 1.21) * 0.21), 2);
            //$bonif_gastos_administrativos_iva_formato = $this->formatearNumero($gastadmin * 0.21,2);
            if ($this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] != '') {
                $percepciones_gastos_administrativos = '
                           ,"percepciones":
                                                [{
						"codigoAlicuota": ' . $this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] . ',
						"codigoPercepcion": "",
						"porcentaje": ' . $this->formatearNumero($p_porc_gastos_administrativos, 2) . ',
						"base": ' . $this->formatearNumero($p_gastos_administrativos_base, 2) . ',
						"importe": ' . $this->formatearNumero($p_gastos_administrativos_importe, 2) . '
                                        	}]';
                $this->tot_gastos_administrativos_ali = $this->formatearNumero($p_gastos_administrativos_importe, 2);
            }
        }

        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'SNC') {
            $p_snc_gastos_administrativos = 10.5;
            $p_snc_gastos_administrativos_base = $gastadmin;
            $p_snc_gastos_administrativos_importe = $this->formatearNumero($precio_gastos_administrativos * ($p_snc_gastos_administrativos / 100), 2);
            $bonif_gastos_administrativos_iva = $this->formatearNumero($bonif_gastos_administrativos_sin_iva * 0.21, 2);
            $bonif_gastos_administrativos_iva_mas_snc = $this->formatearNumero(($bonif_gastos_administrativos_sin_iva * 0.21) + $p_snc_gastos_administrativos_importe, 2);
            $bonif_gastos_administrativos_iva_formato = $bonif_gastos_administrativos_iva;

            if ($this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] != '') {
                $alic_gastos_administrativos = ',{
						"codigoAlicuota": ' . $this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] . ',
						"codigoPercepcion": "",
						"porcentaje": ' . $this->formatearNumero($p_porc_gastos_administrativos, 2) . ',
						"base": ' . $this->formatearNumero($p_gastos_administrativos_base, 2) . ',
						"importe": ' . $this->formatearNumero($p_gastos_administrativos_importe, 2) . '
                                        	}';
            }
            if (empty($alic_gastos_administrativos)) {
                $alic_gastos_administrativos = '';
            }
            $percepciones_gastos_administrativos = ',"percepciones":
                                        [{
						"codigoAlicuota": 11,
						"codigoPercepcion": "",
						"porcentaje" : ' . $this->formatearNumero($p_snc_gastos_administrativos, 2) . ',
						"base": ' . $this->formatearNumero($p_snc_gastos_administrativos_base, 2) . ',
						"importe": ' . $this->formatearNumero($p_snc_gastos_administrativos_importe, 2) . '
					} ' . $alic_gastos_administrativos . '
                                	]';

            $this->tot_gastos_administrativos_10_5 = $this->formatearNumero($p_snc_gastos_administrativos_importe, 2);
            $this->tot_gastos_administrativos_ali = $this->formatearNumero($p_gastos_administrativos_importe, 2);
        }
        if (empty($percepciones_gastos_administrativos)) {
            $percepciones_gastos_administrativos = '';
        }
        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'EX') {
            $gastadmin_json = '{
	"codigo": "02",
        "descripcion": "Gastos administrativos",
	"codigoDeposito": "1",
        "codigoTasaIva": "3",
	"cantidad": 1,
	"precio": ' . $this->formatearNumero($precio_gastos_administrativos, 2) . ',
        "importe": ' . $this->formatearNumero($precio_gastos_administrativos, 2) . '
        },';
            $total_iva = 0;
            $subtotal_sin_imp = 0;
            $this->tot_gastos_administrativos = $this->formatearNumero($precio_gastos_administrativos, 2);
        } else {
            $gastadmin_json = '{
				"codigo": "02",
				"descripcion": "Gastos administrativos",
				"descargaStock" : false,
				"cantidad": 1,
				"codigoDeposito": "1",
                                "importeIva": ' . $this->formatearNumero($bonif_gastos_administrativos_iva_formato, 2) . ',
				"codigoUM": "UNI",
				"precio": ' . $this->formatearNumero($precio_gastos_administrativos, 2) . ',
                                "importe": ' . $this->formatearNumero($precio_gastos_administrativos, 2) . ',
                                "importeSinImpuestos": ' . $this->formatearNumero($bonif_gastos_administrativos_sin_iva, 2) . $percepciones_gastos_administrativos . '
                        	}
                     ';
            //Configuro los totales a sumar.
            $this->tot_gastos_administrativos = $this->formatearNumero($precio_gastos_administrativos, 2);
            if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'SNC') {
                $this->tot_gastos_administrativos_iva = $this->formatearNumero($bonif_gastos_administrativos_iva_mas_snc, 2);
            } else {
                $this->tot_gastos_administrativos_iva = $this->formatearNumero($bonif_gastos_administrativos_iva_formato, 2);
            }
            $this->tot_gastos_administrativos_sin_impuestos = $this->formatearNumero($bonif_gastos_administrativos_sin_iva, 2);
            $this->tot_gastos_administrativos_subtotal = $this->formatearNumero($precio_gastos_administrativos, 2);
        }


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

        if (!empty($this->art_total_iva)) {
            $total_iva = array_sum($this->art_total_iva);
        }
        if (!empty($this->tot_arts_sin_impuestos)) {
            $subtotal_sin_imp = array_sum($this->tot_arts_sin_impuestos);
        }
        $subtotal = array_sum($this->art_total_precio);

        if (!empty($this->art_total_solo_iva_snc)) {
            $tot_iva_solo_snc = array_sum($this->art_total_solo_iva_snc);
        }

        /* El total de iva en todos los casos es solo el 21% */
        $total_comp = array_sum($this->art_total_precio);
        // + $art_total_10_50 + $art_total_perc

        if ($bonif_cosme > 0) {
            $bonif_cosme_json = $bonif_cosme_json;
        } else {
            $bonif_cosme_json = '';
        }
        if ($practicosas > 0) {
            $practicosas_json = $practicosas_json;
        } else {
            $practicosas_json = '';
        }
        if ($bonif_adicional > 0) {
            $bonif_adicional_json = $bonif_adicional_json;
        } else {
            $bonif_adicional_json = '';
        }
        if ($gastadmin > 0) {
            $gastadmin_json = $gastadmin_json;
        } else {
            $gastadmin_json = '';
        }
        $articulo = $articulo . $bonif_cosme_json . $practicosas_json . $bonif_adicional_json . $gastadmin_json;
        //. $bonif_cosme . $practicosas . $bonif_adicional . $gastadmin

        /* Si las variables de percepción vienen vacías entonces omito la carga en el total */
        if (empty($this->art_total_perc)) {
            $this->tot_bonif_10_5 = 0;
        }
        if (empty($this->tot_bonif_ali)) {
            $this->tot_bonif_ali = 0;
        }
        if (empty($this->tot_practicosas)) {
            $this->tot_practicosas = 0;
        }
        if (empty($this->tot_practicosas_10_5)) {
            $this->tot_practicosas_10_5 = 0;
        }
        if (empty($this->tot_practicosas_ali)) {
            $this->tot_practicosas_ali = 0;
        }
        if (empty($this->tot_gastos_administrativos)) {
            $this->tot_gastos_administrativos = 0;
        }
        if (empty($this->tot_gastos_administrativos_10_5)) {
            $this->tot_gastos_administrativos_10_5 = 0;
        }
        if (empty($this->tot_gastos_administrativos_ali)) {
            $this->tot_gastos_administrativos_ali = 0;
        }

        if (empty($this->tot_bonif_adicional)) {
            $this->tot_bonif_adicional = 0;
        }
        if (empty($this->tot_bonif_adicional_10_5)) {
            $this->tot_bonif_adicional_10_5 = 0;
        }
        if (empty($this->tot_bonif_adicional_ali)) {
            $this->tot_bonif_adicional_ali = 0;
        }

        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'SNC') {
            //Configuro los totales + las bonificaciones que llegaron desde arriba
            $tot_finales_iva = $total_iva - $this->tot_bonificaciones_iva - $this->tot_practicosas_iva + $this->tot_gastos_administrativos_iva - $this->tot_bonif_adicional_iva;
            /* Tomo el iva original del CSV debido a que no registra la diferencia de 1 centavo en ciertos comprobantes a continuación está la original */
            //$tot_finales_iva = $total_iva - $this->tot_bonificaciones_iva - $this->tot_practicosas_iva + $this->tot_gastos_administrativos_iva - $this->tot_bonif_adicional_iva;
            $tot_finales_subtotal = $subtotal - $this->tot_bonif_subtotal - $this->tot_practicosas_subtotal + $this->tot_gastos_administrativos_subtotal - $this->tot_bonif_adicional_subtotal;
            //
            $tot_finales_subtotal_sin_imp = $subtotal_sin_imp - $this->tot_bonif_sin_impuestos - $this->tot_practicosas_sin_impuestos + $this->tot_gastos_administrativos_sin_impuestos - $this->tot_bonif_adicional_sin_impuestos;
            //
            $tot_finales_tot_comp = $subtotal_sin_imp + $total_iva - $this->tot_bonificaciones - $this->tot_bonif_10_5 - $this->tot_bonif_ali - $this->tot_practicosas - $this->tot_practicosas_10_5 - $this->tot_practicosas_ali + $this->tot_gastos_administrativos + $this->tot_gastos_administrativos_10_5 + $this->tot_gastos_administrativos_ali - $this->tot_bonif_adicional - $this->tot_bonif_adicional_10_5 - $this->tot_bonif_adicional_ali + $art_total_perc;
            //$tot_ctrl = $art_total_perc - $this->tot_bonif_10_5 - $this->tot_practicosas_10_5 + $this->tot_gastos_administrativos_10_5 - $this->tot_bonif_adicional_10_5;
            //$total_comp
            $tot_ley = $tot_finales_iva - $art_total_10_50 + $this->tot_bonif_10_5 + $this->tot_practicosas_10_5 + $this->tot_bonif_adicional_10_5 + $this->tot_gastos_administrativos_10_5;
            //- $this->tot_bonif_10_5 - $this->tot_practicosas_10_5 - $this->tot_bonif_adicional_10_5 + $this->tot_gastos_administrativos_10_5

            $tot_exento = 0;
            $xmlTyp = '';
            $leyenda_impresa_1 = number_format($tot_ley, 2, '.', ',');
            $leyenda_impresa_2 = number_format($tot_finales_subtotal_sin_imp, 2, '.', ',');
        }
        /* Cuando el cliente es CF es posible que las variables ALI vengan sin */
        if (empty($this->tot_bonif_ali)) {
            $this->tot_bonif_ali = 0;
        }
        if (empty($this->tot_practicosas_ali)) {
            $this->tot_practicosas_ali = 0;
        }
        if (empty($this->tot_gastos_administrativos_ali)) {
            $this->tot_gastos_administrativos_ali = 0;
        }
        if (empty($this->tot_bonif_adicional_ali)) {
            $this->tot_bonif_adicional_ali = 0;
        }

        /* Sí el cliente es monotributista calculo los totales en base a lo requerido */
        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'CF' and $this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] !== 'SNC') {
            if ($this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] != '') {
                $tot_finales_iva = $total_iva - $this->tot_bonificaciones_iva - $this->tot_practicosas_iva + $this->tot_gastos_administrativos_iva - $art_total_perc - $this->tot_bonif_adicional_iva;

                //- $this->tot_bonif_adicional_iva - $this->tot_bonif_adicional_ali
            } else {
                $tot_finales_iva = $total_iva - $this->tot_bonificaciones_iva - $this->tot_practicosas_iva + $this->tot_gastos_administrativos_iva - $this->tot_bonif_adicional_iva;
            }

            $tot_finales_subtotal = $subtotal - $this->tot_bonif_subtotal - $this->tot_practicosas_subtotal + $this->tot_gastos_administrativos_subtotal - $this->tot_bonif_adicional_subtotal;
            $tot_finales_subtotal_sin_imp = $subtotal_sin_imp - $this->tot_bonif_sin_impuestos - $this->tot_practicosas_sin_impuestos + $this->tot_gastos_administrativos_sin_impuestos - $this->tot_bonif_adicional_sin_impuestos;
            $tot_finales_tot_comp = $total_comp - $this->tot_bonificaciones - $this->tot_practicosas + $this->tot_gastos_administrativos - $this->tot_bonif_adicional - $this->tot_bonif_ali - $this->tot_practicosas_ali + $this->tot_gastos_administrativos_ali;
            //$tot_prueba = $total_comp - $this->tot_bonificaciones - $this->tot_practicosas + $this->tot_gastos_administrativos - $this->tot_bonif_adicional;
            $tot_exento = 0;
            $xmlTyp = '';

            /* Para clientes que son CF + alícuota calculo nuevos totales */
            if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'CF' and $this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] != '') {
                $tot_finales_iva = $total_iva - $this->tot_bonificaciones_iva - $this->tot_practicosas_iva + $this->tot_gastos_administrativos_iva - $this->tot_bonif_adicional_iva;
                $tot_finales_tot_comp = $total_comp - $this->tot_bonificaciones - $this->tot_practicosas + $this->tot_gastos_administrativos - $this->tot_bonif_adicional - $this->tot_bonif_ali - $this->tot_practicosas_ali + $this->tot_gastos_administrativos_ali + $art_total_perc - $this->tot_bonif_adicional_ali;
                /* Debido a un error en la impresión configuro el total de percepciones de artículo para imprimir en TYP */
                //$leyenda_impresa_1 = 'PERCEP. IIBB ' . $this->tabla_cliente_cod_cliente['NOMBRE_PRO'] . ': ' . ($art_total_perc - $this->tot_bonif_adicional_ali - $this->tot_bonif_ali - $this->tot_practicosas_ali + $this->tot_gastos_administrativos_ali);
            }
        }

        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'EX') {
            /* Control de los totales a fin de determinar los valroes erroneos para las FACE */
            $tot_finales_iva = 0;
            $tot_finales_subtotal = $total_comp - $this->tot_bonificaciones - $this->tot_practicosas + $this->tot_gastos_administrativos - $this->tot_bonif_adicional;
            // 
            $tot_finales_subtotal_sin_imp = 0;
            $tot_finales_tot_comp = $total_comp - $this->tot_bonificaciones - $this->tot_practicosas + $this->tot_gastos_administrativos - $this->tot_bonif_adicional;
            //
            $tot_exento = $total_comp - $this->tot_bonificaciones - $this->tot_practicosas + $this->tot_gastos_administrativos - $this->tot_bonif_adicional;
            //
            /* String json para completar si el comprobante es excento */
            $xmlTyp = ',
            "xmlTyp" : "",
            "CuitDestino": "J",
            "TipoExpo": "4",
            "PaisAfip": "200",
            "CodigoIncoterms": "FOB",
            "DescripcionIncoterms": ""  ';
        }

        /* Si viene la leyenda impresa entonces la imprimo */
        if (isset($leyenda_impresa_1)) {
            $leyenda_impresa_1 = $leyenda_impresa_1;
        } else {
            $leyenda_impresa_1 = '';
        }

        if (isset($leyenda_impresa_2)) {
            $leyenda_impresa_2 = $leyenda_impresa_2;
        } else {
            $leyenda_impresa_2 = '';
        }

        $ceros = str_pad($this->id_orden, 8, '0', STR_PAD_LEFT);
        $n_fac = $pv_pedi . $ceros;
        // Returns the data/output as a string instead of raw data
        //"' . $fecha . '",
        /* Utilizado durante el ingreso real */
        $data_string = '
        [
	{
		"codigoTipoComprobante": "FAC",
		"numeroComprobante": "' . $n_fac . '",
		"codigoTalonario": "' . $talon_fac . '",
		"codigoCliente": "' . $this->tabla_cliente_cod_cliente['COD_CLIENT'] . '",
		"codigoCondicionDeVenta": 1,
		"numeroDeProyecto": "",
		"codigoOperacionRG3685": "00001",
		"codigoClasificacion": "",
		"fechaComprobante": "' . $this->normalizaFecha($this->fechaFac) . '",
		"fechaCierreTesoreria": "' . $this->normalizaFecha($this->fechaFac) . '",
		"codigoListaPrecio": ' . $lista . ',
		"cotizacionVentas": null,
		"codigoContracuenta": "20",
		"codigoDeposito": "1",
		"codigoVendedor": "' . $cod_zona . '",
		"idMotivo": "3",
		"codigoAsiento": "1",
		"leyenda1": "' . $leyenda_impresa_1 . '",
		"leyenda2": "' . $leyenda_impresa_2 . '",
		"leyenda3": "",
		"leyenda4": "",
		"leyenda5": "",
		"esMonedaExtranjera": false,
		"total" : ' . $this->formatearNumero($tot_finales_tot_comp, 2) . ',
		"totalExento": ' . $this->formatearNumero($tot_exento, 2) . ',
		"totalIva": ' . $this->formatearNumero($tot_finales_iva, 2) . ',
		"subtotal": ' . $this->formatearNumero($tot_finales_subtotal, 2) . ',
		"totalSinImpuestos": ' . $this->formatearNumero($tot_finales_subtotal_sin_imp, 2) . ',
		"subtotalSinImpuestos": ' . $this->formatearNumero($tot_finales_subtotal_sin_imp, 2) . ',
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
				"fechaVencimiento": "' . $this->normalizaFecha($this->fechaFac) . '",
				"importe" : ' . $this->formatearNumero($tot_finales_tot_comp, 2) . '
			}
                        ]' . $xmlTyp . '
            }
        ]';
        /* Utilizado durante el ingreso preparado para cliente APIRest Insmonia o PostMan */
        //$data_string = '
        //
        //{
        //	"codigoTipoComprobante": "FAC",
        //	"numeroComprobante": "' . $n_fac . '",
        //	"codigoTalonario": "' . $talon_fac . '",
        //	"codigoCliente": "' . $this->tabla_cliente_cod_cliente['COD_CLIENT'] . '",
        //	"codigoCondicionDeVenta": 1,
        //	"numeroDeProyecto": "",
        //	"codigoOperacionRG3685": "00001",
        //	"codigoClasificacion": "",
        //	"fechaComprobante": "' . $this->normalizaFecha($this->fechaFac) . '",
        //	"fechaCierreTesoreria": "' . $this->normalizaFecha($this->fechaFac) . '",
        //	"codigoListaPrecio": ' . $lista . ',
        //	"cotizacionVentas": null,
        //	"codigoContracuenta": "20",
        //	"codigoDeposito": "1",
        //	"codigoVendedor": "' . $cod_zona . '",
        //	"idMotivo": "3",
        //	"codigoAsiento": "1",
        //	"leyenda1": "' . $leyenda_impresa_1 . '",
        //	"leyenda2": "",
        //	"leyenda3": "",
        //	"leyenda4": "",
        //	"leyenda5": "",
        //	"esMonedaExtranjera": false,
        //	"total" : ' . $this->formatearNumero($tot_finales_tot_comp, 2) . ',
        //	"totalExento": ' . $this->formatearNumero($tot_exento, 2) . ',
        //	"totalIva": ' . $this->formatearNumero($tot_finales_iva, 2) . ',
        //	"subtotal": ' . $this->formatearNumero($tot_finales_subtotal, 2) . ',
        //	"totalSinImpuestos": ' . $this->formatearNumero($tot_finales_subtotal_sin_imp, 2) . ',
        //	"subtotalSinImpuestos": ' . $this->formatearNumero($tot_finales_subtotal_sin_imp, 2) . ',
        //	"descuentoPorcentaje": 0,
        //	"descuentoMonto": 0,
        //	"descuentoMontoSinIva": 0,
        //	"recargoPorcentaje": 0,
        //	"recargoMonto": 0,
        //	"recargoMontoSinIva": 0,
        //	"recargoFletePorcentaje": 0,
        //	"recargoFleteMonto": 0,
        //	"recargoFleteMontoSinIva": 0,
        //	"interesesPorcentaje": 0.00,
        //	"interesesMontoSinIva": 0.00,
        //	"observaciones": "",
        //	"rg3668TipoIdentificacionFirmante": null,
        //	"rg3668CaracterDelFirmante": null,
        //	"rg3668CodigoIdentificacionFirmante": "",
        //	"rg3668MotivoDeExcepcion": null,
        //	"rg3668CodigoWeb": "666",
        //	"items": [
        //        ' . $articulo . '
        //	],
        //	"cuotasCuentaCorriente" :
        //	[
        //		{
        //			"fechaVencimiento": "' . $this->normalizaFecha($this->fechaFac) . '",
        //			"importe" : ' . $this->formatearNumero($tot_finales_tot_comp, 2) . '
        //		}
        //                ]' . $xmlTyp . '
        //    }
        //';

        curl_setopt($ch, CURLOPT_POST, false);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //Set your auth headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'ApiAuthorization: ' . $this->token_api_local . '',
            'Company: ' . $this->id_empresa . '',
            'Content-Type: application/json'
        ));

        //curl_setopt($ch, CURLOPT_VERBOSE, true);
        //$verbose = fopen('log_curl.txt', 'w+');
        //curl_setopt($ch, CURLOPT_STDERR, $verbose);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        // get stringified data/output. See CURLOPT_RETURNTRANSFER/
        /* Ejecución. */
        $data = curl_exec($ch);

        // get info about the request
        //$info = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        /* Hardening Linux: control de error de red en curl */
        if ($data === false) {
            $curl_error = curl_error($ch);
            error_log('[' . date('Y-m-d H:i:s') . '] CURL ERROR en ingresoFactura | Pedido: ' . $nro_pedido . ' | Error: ' . $curl_error);
            $data2 = null;
        } else {
            $data2 = json_decode($data, true);
        }

        $this->mensaje_api = $data2;

        $ahora = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
        /* Uno es para ver el formato y el otro es para dejar la comita */
        $formateado = $ahora->format("Y-m-d H:i:s.u");
        $formateado = ',';
        /* Hardening Linux: separador compatible y mkdir preventivo */
        $dir_json = 'archivos_json';
        if (!is_dir($dir_json)) {
            mkdir($dir_json, 0755, true);
        }
        $fecha = $dir_json . '/' . date('Y-m-d-H_i_s');
        //
        if (file_exists("fc_json.txt")) {
            $fh_json = fopen($dir_json . '/fc_json.json', 'a');
            //Modififico el valor de la variable formateado con el fin de dejar la comita para el ingreso del json en cliente API
            if ($fh_json !== false) {
                fwrite($fh_json, PHP_EOL . "$data_string $formateado");
                fclose($fh_json);
            }
        } else {
            $fh_json = fopen($fecha . '.json', 'w');
            if ($fh_json !== false) {
                fwrite($fh_json, PHP_EOL . "$data_string");
                //en la cabeza
                fclose($fh_json);
            }
        }



        curl_close($ch);

        /* Mato los arrays para que no se acumulen al finalizar el ingreso */
        unset($this->art_total_precio);
        $this->art_total_precio = array();

        unset($this->tot_arts_sin_impuestos);
        $this->tot_arts_sin_impuestos = array();

        unset($this->art_total_iva);
        $this->art_total_iva = array();

        unset($this->art_total_10_50);
        $this->art_total_10_50 = array();

        unset($this->art_total_perc);
        $this->art_total_perc = array();

        unset($total_iva);
        $total_iva = array();

        unset($subtotal);
        $subtotal = array();

        unset($subtotal_sin_imp);
        $subtotal_sin_imp = array();

        unset($total_comp);
        $total_comp = array();

        unset($art_total_10_50);
        $art_total_10_50 = array();

        unset($art_total_perc);
        $art_total_perc = array();

        unset($this->articulos);
        $this->articulos = array();

        unset($this->tot_para_ped);
        $this->tot_para_ped = array();

        $this->tot_bonif_ali = null;
        $this->tot_practicosas_ali = null;
        $this->tot_gastos_administrativos_ali = null;
        $this->tot_bonif_adicional_ali = null;
        $this->tot_bonif_10_5 = null;
        $this->tot_practicosas = null;
        $this->tot_practicosas_10_5 = null;
        $this->tot_gastos_administrativos = null;
        $this->tot_gastos_administrativos_10_5 = null;
        $this->tot_bonif_adicional = null;
        $this->tot_bonif_adicional_10_5 = null;
        $this->tot_bonif_adicional_ali = null;
        $art_total_perc = null;
        $this->tot_bonificaciones = null;
        $this->tot_bonif_adicional_iva = null;
        //unset($this->p_imp_importe);
        //$this->$this->p_imp_importe = array();
    }

    /*Método para solo reprocesar las facturas*/
    public function procesoPedidosRXN($menu)
    {
        /* Recorro el */
        $this->encPedidos($menu);

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
                $dato_pedi_enc[] = array("N_COMP" => $valor_cue_csv[1], "COD_CLIENT" => $valor_cue_csv[3], "IMPORTE" => $valor_cue_csv[5], "COD_ZONA" => $valor_cue_csv[18], "BONIFCOSME" => $valor_cue_csv[13], "PRACTICOSAS" => $valor_cue_csv[14], "GASTADMIN" => $valor_cue_csv[11], "IMPORTE_GRAVADO" => $valor_cue_csv[7], "FECHA" => $valor_cue_csv[2], "IMP_IVA" => $valor_cue_csv[9], "ORDEN" => $valor_cue_csv[0], "BONIF_ADIC" => $valor_cue_csv[17], "NOMBRE_ARCHIVO" => $valor_cue_csv[19]);
            }

            $this->devuelvoTokens();
            $numeroFac = $this->token['FAC_B'];

            /* Recorro el encabezado del pedido */
            foreach ($dato_pedi_enc as $pedi_enc) {
                //echo '<br> Veo lo que estoy buscando, nombre archivo: '.$pedi_enc['NOMBRE_ARCHIVO'] . ' Número de comprobante'. $pedi_enc['N_COMP']. ' Menú: ' . $menu;
                /* Busco el archivo en cuestión para poder procesar solo el correspondiente al búcle */
                $this->cuePedidos($pedi_enc['NOMBRE_ARCHIVO'], $pedi_enc['N_COMP'], $menu);

                /* Controlo si existe el pedido en la base, si existe no se ingresará */
                $this->ctrlPedi($pedi_enc['N_COMP'], $pedi_enc['NOMBRE_ARCHIVO']);

                /* Si no existe en la tabla de control entonces se ingresa */
                if ($this->ctrlPediRxnApiCtrl['COD_COMP'] == '') {

                    /* Correspondiente al cuerpo del pedido */
                    $this->buscoPedidoRXN($pedi_enc['N_COMP'], $pedi_enc['COD_CLIENT'], $pedi_enc['ORDEN'], $pedi_enc['NOMBRE_ARCHIVO']);

                    //echo '<br>Antes del ingreso pedido: ';
                    //print_r($this->articulos);
                    /*Metodo que ingresa los valores a la API*/
                    //Renombrado por ahora.
                    $this->ingresoPedido($pedi_enc['COD_CLIENT'], $pedi_enc['IMPORTE'], $this->articulos, $pedi_enc['N_COMP'], $pedi_enc['COD_ZONA'], $pedi_enc['IMPORTE_GRAVADO'], $pedi_enc['FECHA'], $pedi_enc['BONIFCOSME'], $pedi_enc['PRACTICOSAS'], $pedi_enc['GASTADMIN'], $pedi_enc['IMP_IVA'], $pedi_enc['BONIF_ADIC']);

                    /* En el caso de usar el menú masivo de ingreso se aplica el llenado del JSON completo */

                    if (!isset($this->mensaje_api['savedId'])) {
                        $id = 0;
                        $mensaje = 'nulo';
                    } else {
                        $id = $this->mensaje_api['savedId'];
                    }
                    /* Función interna para poder obtener el mensaje de error */
                    $stringConvertido = $this->convertirATexto($this->mensaje_api);

                    if ($this->mensaje_api['Succeeded'] == '') {
                        $grabo = 0;
                    } else {
                        /* Extraigo el número de PV del comprobante para poder apuntar la numeración correspondiente */
                        $num_mas_uno = $this->id_orden + 1;
                        /* Si grabó entonces actualizo el ID de Factura */
                        $this->actIdFac($num_mas_uno, $this->param_numeracion);
                        $grabo = 1;

                        /* Si estoy reprocesando un pedido entonces actualizo el grabó a 1 siempre y cuando se haya grabado */
                        $this->actualizoReproceso($pedi_enc['N_COMP'], $pedi_enc['COD_CLIENT'], $pedi_enc['NOMBRE_ARCHIVO']);
                        /* Acá se debería agregar la actualización del reproceso de la base descargada. */
                    }

                    if (!empty($this->mensaje_api['Comprobantes'][0]['exceptionMessage'])) {
                        $detalle = ' Detalle: ' . $this->mensaje_api['Comprobantes'][0]['exceptionMessage'] . ' en el código de cliente (teléfono 1): ' . $pedi_enc['COD_CLIENT'];
                    } else {
                        $detalle = '';
                    }
                    ///* Elimino el muestro de mensajes para dejarlos en un archivo de texto a fin de que no se cuelgue el navegador */
                    //$mensaje_txt = 'Mensaje: ' . $this->mensaje_api['Message'] . ' Comprobante: ' . $this->mensaje_api['Comprobantes'][0]['numeroComprobante'] . ' Estado: ' . $this->mensaje_api['Comprobantes'][0]['estado'] . $detalle;
                    //if (file_exists("detalle_proceso.txt")) {
                    //    $archivo = fopen("detalle_proceso.txt", "a");
                    //    fwrite($archivo, PHP_EOL . "$mensaje_txt");
                    //    fclose($archivo);
                    //} else {
                    //    $archivo = fopen("detalle_proceso.txt", "w");
                    //    fwrite($archivo, PHP_EOL . "$mensaje_txt");
                    //    fclose($archivo);
                    //}

                    $mensaje_api = 'ID_INTERNO: ' . $id . ' Mensaje: ' . $stringConvertido . ' ¿Grabó?: ' . $this->mensaje_api['Succeeded'];

                    $this->ingresoMensajesApi($pedi_enc['N_COMP'], 'PEDIDOS', $mensaje_api, $grabo, $pedi_enc['COD_CLIENT'], $pedi_enc['NOMBRE_ARCHIVO'], 0, $stringConvertido, '', '');
                } else {
                    /* Si el valor es reproceso no ingreso el registro a la base de datos (para no llenar al pedo en los reprocesos */
                    if ($menu === 'PROCESAR') {
                        $stringConvertido = '';
                        /* Al existir ingreso mensaje en la tabla no ingreso para procesar siempre el mismo pedido  $stringConvertido */
                        $this->ingresoMensajesApi($pedi_enc['N_COMP'], 'PEDIDOS', 'EL Pedido ya existe', 1, $pedi_enc['COD_CLIENT'], $pedi_enc['NOMBRE_ARCHIVO'], 0, $stringConvertido, '', '');
                        echo "<div style='color: #ff9800; font-family: monospace; font-size: 14px; padding: 6px; border-bottom: 1px solid #444; margin-bottom: 2px;'>[PID-{$pedi_enc['N_COMP']}] ⚠ El pedido ya existe para el cliente {$pedi_enc['COD_CLIENT']}.</div>";
                    }
                }
            }

            //$this->actualizaPedidos();
        } else {
            echo "<div style='color: #ff9800; font-family: monospace; font-size: 14px; padding: 6px; border-bottom: 1px solid #444; margin-bottom: 2px;'>⚠ No hay archivos de pedidos para procesar.</div>";
            $this->ingresoMensajesApi('SIN', 'PEDIDOS', 'NO-HAY-ARCHIVOS-PARA-PROCESAR', 0, '', '', 0, '', '', '');
        }
    }


    public $facturas;

    /* Método que ingresa la factura componiendo un json con todo los archivos para pasar un solo valor */

    public function generoJsonCompleto($cliente, $importe, $articulo, $nro_pedido, $cod_zona, $imp_sin_impuestos, $fecha, $bonif_cosme, $practicosas, $gastadmin, $imp_iva, $bonif_adicional, $numeroFac)
    {
        /* Busco la configuración del server desde la tabla */
        $this->devuelvoTokens();

        $this->ingresoPedidoControl($nro_pedido, $cliente);

        /* Busco el COD_CLIENT original */
        $this->busco_cliente($cliente);

        /* Verifico el punto de venta del pedido para apuntarlo al talonario */
        //Extraigo el PV del pedido
        $pv_pedi = substr($nro_pedido, 0, 6);
        if ($pv_pedi == 'B00003') {
            $talon_pedi = 10;
            $talon_fac = 5;
            $param_numeracion = 'FAC_B';
            $lista = 1;
        }

        if ($pv_pedi == 'B00007') {
            $talon_pedi = 10;
            $talon_fac = 15;
            $param_numeracion = 'FAC_ECOMMERCE';
            $lista = 1;
        }

        if ($pv_pedi == 'A00003') {
            $talon_pedi = 10;
            $talon_fac = 1;
            $lista = 1;
        }

        if ($pv_pedi == 'E00011') {
            $talon_pedi = 10;
            $talon_fac = 22;
            $lista = 2;
            $param_numeracion = 'FAC_E_EXPO';
        }

        $this->param_numeracion = $param_numeracion;

        $this->devuelvoIdPedido($param_numeracion);

        /* Busco cliente */
        $this->busco_cliente($cliente);

        $bonif_cosme_sin_iva = ($bonif_cosme / 1.21);
        $precio_bonif = $bonif_cosme;
        $p_porc_cosme = $this->tabla_cliente_cod_cliente['PORCENTAJE'];
        $p_cosme_base = $bonif_cosme_sin_iva;
        $p_cosme_importe = $bonif_cosme_sin_iva * ($p_porc_cosme / 100);

        /* Completo el IVA en el caso que el cliente sea CF */
        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'CF') {
            $bonif_cosme_iva_formato = $this->formatearNumero((($bonif_cosme / 1.21) * 0.21), 2);
            if ($this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] != '') {
                $percepciones = '
                ,"percepciones":
                                                [{
						"codigoAlicuota": ' . $this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] . ',
						"codigoPercepcion": "",
						"porcentaje": ' . $this->formatearNumero($p_porc_cosme, 2) . ',
						"base": -' . $this->formatearNumero($p_cosme_base, 2) . ',
						"importe": -' . $this->formatearNumero($p_cosme_importe, 2) . '
                                        	}]';
                $this->tot_bonif_ali = $this->formatearNumero($p_cosme_importe, 2);
            }
        }



        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'SNC') {
            $p_snc_cosme = 10.5;
            $p_snc_cosme_base = $bonif_cosme;
            $p_snc_cosme_importe = $this->formatearNumero($precio_bonif * ($p_snc_cosme / 100), 2);
            $bonif_cosme_iva = $this->formatearNumero(($bonif_cosme_sin_iva * 0.21), 2);
            $bonif_cosme_iva_mas_snc = $this->formatearNumero(($bonif_cosme_sin_iva * 0.21) + $p_snc_cosme_importe, 2);
            $bonif_cosme_iva_formato = $bonif_cosme_iva;

            if ($this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] != '') {
                $alic = ',{
						"codigoAlicuota": ' . $this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] . ',
						"codigoPercepcion": "",
						"porcentaje": ' . $this->formatearNumero($p_porc_cosme, 2) . ',
						"base": -' . $this->formatearNumero($p_cosme_base, 2) . ',
						"importe": -' . $this->formatearNumero($p_cosme_importe, 2) . '
                                        	}';
            }
            if (empty($alic)) {
                $alic = '';
            }

            $percepciones = ',"percepciones":
                                        [{
						"codigoAlicuota": 11,
						"codigoPercepcion": "",
						"porcentaje" : ' . $this->formatearNumero($p_snc_cosme, 2) . ',
						"base": -' . $this->formatearNumero($p_snc_cosme_base, 2) . ',
						"importe": -' . $this->formatearNumero($p_snc_cosme_importe, 2) . '
					} ' . $alic . '
                                	]';

            $this->tot_bonif_10_5 = $this->formatearNumero($p_snc_cosme_importe, 2);
            $this->tot_bonif_ali = $this->formatearNumero($p_cosme_importe, 2);
        }

        if (empty($percepciones)) {
            $percepciones = '';
        }

        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'EX') {
            $bonif_cosme_json = '{
	"codigo": "03",
	"descripcion": "Bonificacion Cosméticos",
	"codigoDeposito": "1",
        "codigoTasaIva": "1",
	"cantidad": -1,
	"precio": ' . $this->formatearNumero($precio_bonif, 2) . ',
        "importe": ' . $this->formatearNumero($precio_bonif, 2) . '
        },';
        } else {

            $bonif_cosme_json = '{
	"codigo": "03",
	"descripcion": "Bonificacion Cosméticos",
	"descargaStock" : false,
	"cantidad": -1,
	"codigoDeposito": "1",
        "importeIva": -' . $this->formatearNumero($bonif_cosme_iva_formato, 2) . ',
	"codigoUM": "BON",
	"precio": ' . $this->formatearNumero($precio_bonif, 2) . ',
        "importe": ' . $this->formatearNumero($precio_bonif, 2) . ',
        "importeSinImpuestos": -' . $this->formatearNumero($bonif_cosme_sin_iva, 2) . $percepciones . '
        },';
            //Configuro los totales a sumar.
            if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'SNC') {
                $this->tot_bonificaciones_iva = $this->formatearNumero($bonif_cosme_iva_mas_snc, 2);
            } else {
                $this->tot_bonificaciones_iva = $this->formatearNumero($bonif_cosme_iva_formato, 2);
            }
            $this->tot_bonif_sin_impuestos = $this->formatearNumero($bonif_cosme_sin_iva, 2);
            $this->tot_bonif_subtotal = $this->formatearNumero($precio_bonif, 2);
        }
        $this->tot_bonificaciones = $this->formatearNumero($precio_bonif, 2);

        /* Verifico si la bonificación adicional es mayor que cero y en el caso que se así la agrego en el json */
        if ($bonif_adicional > 0) {

            $bonif_adicional_sin_iva = ($bonif_adicional / 1.21);
            $precio_bonif_adicional = $bonif_adicional;
            $p_porc_bonif_adicional = $this->tabla_cliente_cod_cliente['PORCENTAJE'];
            $p_bonif_adicional_base = $bonif_adicional_sin_iva;
            $p_bonif_adicional_importe = $bonif_adicional_sin_iva * ($p_porc_bonif_adicional / 100);

            /* Completo el IVA en el caso que el cliente sea CF */
            if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'CF') {
                $bonif_adicional_iva_formato = $this->formatearNumero((($bonif_adicional / 1.21) * 0.21), 2);
                //$bonif_bonif_adicional_iva_formato = $this->formatearNumero($bonif_adicional * 0.21,2);
                if ($this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] != '') {
                    $percepciones_bonif_adicional = '
                           ,"percepciones":
                                                [{
						"codigoAlicuota": ' . $this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] . ',
						"codigoPercepcion": "",
						"porcentaje": ' . $this->formatearNumero($p_porc_bonif_adicional, 2) . ',
						"base": -' . $this->formatearNumero($p_bonif_adicional_base, 2) . ',
						"importe": -' . $this->formatearNumero($p_bonif_adicional_importe, 2) . '
                                        	}]';

                    $this->tot_bonif_adicional_ali = $this->formatearNumero($p_bonif_adicional_importe, 2);
                }
            }


            if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'SNC') {
                $p_snc_bonif_adicional = 10.5;
                $p_snc_bonif_adicional_base = $bonif_adicional;
                $p_snc_bonif_adicional_importe = $this->formatearNumero($precio_bonif_adicional * ($p_snc_bonif_adicional / 100), 2);
                $p_snc_bonif_adicional_iva = $this->formatearNumero($bonif_adicional_sin_iva * 0.21, 2);
                $bonif_adicional_iva_mas_snc = $this->formatearNumero(($bonif_adicional_sin_iva * 0.21) + $p_snc_bonif_adicional_importe, 2);
                $bonif_adicional_iva_formato = $p_snc_bonif_adicional_iva;

                if ($this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] != '') {
                    $alic_bonif_adicional = ',{
						"codigoAlicuota": ' . $this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] . ',
						"codigoPercepcion": "",
						"porcentaje": ' . $this->formatearNumero($p_porc_bonif_adicional, 2) . ',
						"base": -' . $this->formatearNumero($p_bonif_adicional_base, 2) . ',
						"importe": -' . $this->formatearNumero($p_bonif_adicional_importe, 2) . '
                                        	}';
                }

                if (empty($alic_bonif_adicional)) {
                    $alic_bonif_adicional = '';
                }

                $percepciones_bonif_adicional = ',"percepciones":
                                        [{
						"codigoAlicuota": 11,
						"codigoPercepcion": "",
						"porcentaje" : ' . $this->formatearNumero($p_snc_bonif_adicional, 2) . ',
						"base": -' . $this->formatearNumero($p_snc_bonif_adicional_base, 2) . ',
						"importe": -' . $this->formatearNumero($p_snc_bonif_adicional_importe, 2) . '
					} ' . $alic_bonif_adicional . '
                                	]';
                $this->tot_bonif_adicional_10_5 = $this->formatearNumero($p_snc_bonif_adicional_importe, 2);
                $this->tot_bonif_adicional_ali = $this->formatearNumero($p_bonif_adicional_importe, 2);
                $this->tot_bonif_adicional_iva = $this->formatearNumero($bonif_adicional_iva_formato, 2);

                if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'SNC') {
                    $this->tot_bonif_adicional_iva = $this->formatearNumero($bonif_adicional_iva_mas_snc, 2);
                }
            }

            if (empty($percepciones_bonif_adicional)) {
                $percepciones_bonif_adicional = 0;
            }

            if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'EX') {
                $bonif_adicional_json = '{
	"codigo": "06",
	"descripcion": "Bonificación adicional",
	"codigoDeposito": "1",
        "codigoTasaIva": "1",
	"cantidad": -1,
	"precio": ' . $this->formatearNumero($precio_bonif_adicional, 2) . ',
        "importe": ' . $this->formatearNumero($precio_bonif_adicional, 2) . '
        },';
                //$bonif_adicional = '';
                $this->tot_bonif_adicional = $precio_bonif_adicional;
                $this->tot_bonif_adicional_iva = 0;
                $this->tot_bonif_adicional_sin_impuestos = 0;
                $this->tot_bonif_adicional_subtotal = $precio_bonif_adicional;
            } else {
                $bonif_adicional_json = '{
				"codigo": "06",
				"descripcion": "Bonificación adicional",
				"descargaStock" : false,
				"cantidad": -1,
				"codigoDeposito": "1",
                                "importeIva": -' . $this->formatearNumero($bonif_adicional_iva_formato, 2) . ',
				"codigoUM": "BON",
				"precio": ' . $this->formatearNumero($precio_bonif_adicional, 2) . ',
                                "importe": ' . $this->formatearNumero($precio_bonif_adicional, 2) . ',
                                "importeSinImpuestos": -' . $this->formatearNumero($bonif_adicional_sin_iva, 2) . $percepciones_bonif_adicional . '
                        	},
                     ';
            }

            if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'SNC') {
                $this->tot_bonif_adicional_iva = $this->formatearNumero($bonif_adicional_iva_mas_snc, 2);
            } else {
                $this->tot_bonif_adicional_iva = $this->formatearNumero($bonif_adicional_iva_formato, 2);
            }

            //Configuro los totales a sumar.
            $this->tot_bonif_adicional = $this->formatearNumero($precio_bonif_adicional, 2);
            $this->tot_bonif_adicional_sin_impuestos = $this->formatearNumero($bonif_adicional_sin_iva, 2);
            $this->tot_bonif_adicional_subtotal = $this->formatearNumero($precio_bonif_adicional, 2);
        } else {
            $bonif_adicional = '';
            $this->tot_bonif_adicional = 0;
            if (empty($this->tot_bonif_adicional_iva)) {
                $this->tot_bonif_adicional_iva = 0;
            }
            $this->tot_bonif_adicional_sin_impuestos = 0;
            $this->tot_bonif_adicional_subtotal = 0;
        }

        $bonif_practicosas_sin_iva = ($practicosas / 1.21);
        $precio_practicosas = $practicosas;
        $p_porc_practicosas = $this->tabla_cliente_cod_cliente['PORCENTAJE'];
        $p_practicosas_base = $bonif_practicosas_sin_iva;
        $p_practicosas_importe = $bonif_practicosas_sin_iva * ($p_porc_practicosas / 100);

        /* Completo el IVA en el caso que el cliente sea CF */
        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'CF') {
            $bonif_practicosas_iva_formato = $this->formatearNumero((($practicosas / 1.21) * 0.21), 2);
            //$bonif_practicosas_iva_formato = $this->formatearNumero($practicosas * 0.21,2);
            if ($this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] != '') {
                $percepciones_practicosas = '
                           ,"percepciones":
                                                [{
						"codigoAlicuota": ' . $this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] . ',
						"codigoPercepcion": "",
						"porcentaje": ' . $this->formatearNumero($p_porc_practicosas, 2) . ',
						"base": -' . $this->formatearNumero($p_practicosas_base, 2) . ',
						"importe": -' . $this->formatearNumero($p_practicosas_importe, 2) . '
                                        	}]';
                $this->tot_practicosas_ali = $this->formatearNumero($p_practicosas_importe, 2);
            }
        }


        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'SNC') {
            $p_snc_practicosas = 10.5;
            $p_snc_practicosas_base = $practicosas;
            $p_snc_practicosas_importe = $this->formatearNumero($precio_practicosas * ($p_snc_practicosas / 100), 2);
            $bonif_practicosas_iva = $this->formatearNumero($bonif_practicosas_sin_iva * 0.21, 2);
            $bonif_practicosas_iva_mas_snc = $this->formatearNumero($bonif_practicosas_sin_iva * 0.21 + $p_snc_practicosas_importe, 2);
            $bonif_practicosas_iva_formato = $bonif_practicosas_iva;

            if ($this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] != '') {
                $alic_practicosas = ',{
						"codigoAlicuota": ' . $this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] . ',
						"codigoPercepcion": "",
						"porcentaje": ' . $this->formatearNumero($p_porc_practicosas, 2) . ',
						"base": -' . $this->formatearNumero($p_practicosas_base, 2) . ',
						"importe": -' . $this->formatearNumero($p_practicosas_importe, 2) . '
                                        	}';
            }

            if (empty($alic_practicosas)) {
                $alic_practicosas = '';
            }

            $percepciones_practicosas = ',"percepciones":
                                        [{
						"codigoAlicuota": 11,
						"codigoPercepcion": "",
						"porcentaje" : ' . $this->formatearNumero($p_snc_practicosas, 2) . ',
						"base": -' . $this->formatearNumero($p_snc_practicosas_base, 2) . ',
						"importe": -' . $this->formatearNumero($p_snc_practicosas_importe, 2) . '
					} ' . $alic_practicosas . '
                                	]';
            $this->tot_practicosas_10_5 = $this->formatearNumero($p_snc_practicosas_importe, 2);
            $this->tot_practicosas_ali = $this->formatearNumero($p_practicosas_importe, 2);
        }
        if (empty($percepciones_practicosas)) {
            $percepciones_practicosas = '';
        }
        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'EX') {
            $practicosas_json = '{
	"codigo": "04",
	"descripcion": "Bonificación Practicosas",
	"codigoDeposito": "1",
        "codigoTasaIva": "1",
	"cantidad": -1,
	"precio": ' . $this->formatearNumero($precio_practicosas, 2) . ',
        "importe": ' . $this->formatearNumero($precio_practicosas, 2) . '
        },';
            $this->tot_practicosas = $this->formatearNumero($precio_practicosas, 2);
        } else {
            $practicosas_json = '{
				"codigo": "04",
				"descripcion": "Bonificación Practicosas",
				"descargaStock" : false,
				"cantidad": -1,
				"codigoDeposito": "1",
                                "importeIva": -' . $this->formatearNumero($bonif_practicosas_iva_formato, 2) . ',
				"codigoUM": "BON",
				"precio": ' . $this->formatearNumero($precio_practicosas, 2) . ',
                                "importe": ' . $this->formatearNumero($precio_practicosas, 2) . ',
                                "importeSinImpuestos": -' . $this->formatearNumero($bonif_practicosas_sin_iva, 2) . $percepciones_practicosas . '
                        	},
                                
                     ';
            //Configuro los totales a sumar.
            $this->tot_practicosas = $this->formatearNumero($precio_practicosas, 2);
            if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'SNC') {
                $this->tot_practicosas_iva = $this->formatearNumero($bonif_practicosas_iva_mas_snc, 2);
            } else {
                $this->tot_practicosas_iva = $this->formatearNumero($bonif_practicosas_iva_formato, 2);
            }
            $this->tot_practicosas_sin_impuestos = $this->formatearNumero($bonif_practicosas_sin_iva, 2);
            $this->tot_practicosas_subtotal = $this->formatearNumero($precio_practicosas, 2);
        }

        $bonif_gastos_administrativos_sin_iva = ($gastadmin / 1.21);
        $precio_gastos_administrativos = $gastadmin;
        $p_porc_gastos_administrativos = $this->tabla_cliente_cod_cliente['PORCENTAJE'];
        $p_gastos_administrativos_base = $bonif_gastos_administrativos_sin_iva;
        $p_gastos_administrativos_importe = $bonif_gastos_administrativos_sin_iva * ($p_porc_gastos_administrativos / 100);

        /* Completo el IVA en el caso que el cliente sea CF */
        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'CF') {
            $bonif_gastos_administrativos_iva_formato = $this->formatearNumero((($gastadmin / 1.21) * 0.21), 2);
            //$bonif_gastos_administrativos_iva_formato = $this->formatearNumero($gastadmin * 0.21,2);
            if ($this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] != '') {
                $percepciones_gastos_administrativos = '
                           ,"percepciones":
                                                [{
						"codigoAlicuota": ' . $this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] . ',
						"codigoPercepcion": "",
						"porcentaje": ' . $this->formatearNumero($p_porc_gastos_administrativos, 2) . ',
						"base": ' . $this->formatearNumero($p_gastos_administrativos_base, 2) . ',
						"importe": ' . $this->formatearNumero($p_gastos_administrativos_importe, 2) . '
                                        	}]';
                $this->tot_gastos_administrativos_ali = $this->formatearNumero($p_gastos_administrativos_importe, 2);
            }
        }

        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'SNC') {
            $p_snc_gastos_administrativos = 10.5;
            $p_snc_gastos_administrativos_base = $gastadmin;
            $p_snc_gastos_administrativos_importe = $this->formatearNumero($precio_gastos_administrativos * ($p_snc_gastos_administrativos / 100), 2);
            $bonif_gastos_administrativos_iva = $this->formatearNumero($bonif_gastos_administrativos_sin_iva * 0.21, 2);
            $bonif_gastos_administrativos_iva_mas_snc = $this->formatearNumero(($bonif_gastos_administrativos_sin_iva * 0.21) + $p_snc_gastos_administrativos_importe, 2);
            $bonif_gastos_administrativos_iva_formato = $bonif_gastos_administrativos_iva;

            if ($this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] != '') {
                $alic_gastos_administrativos = ',{
						"codigoAlicuota": ' . $this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] . ',
						"codigoPercepcion": "",
						"porcentaje": ' . $this->formatearNumero($p_porc_gastos_administrativos, 2) . ',
						"base": ' . $this->formatearNumero($p_gastos_administrativos_base, 2) . ',
						"importe": ' . $this->formatearNumero($p_gastos_administrativos_importe, 2) . '
                                        	}';
            }
            if (empty($alic_gastos_administrativos)) {
                $alic_gastos_administrativos = '';
            }
            $percepciones_gastos_administrativos = ',"percepciones":
                                        [{
						"codigoAlicuota": 11,
						"codigoPercepcion": "",
						"porcentaje" : ' . $this->formatearNumero($p_snc_gastos_administrativos, 2) . ',
						"base": ' . $this->formatearNumero($p_snc_gastos_administrativos_base, 2) . ',
						"importe": ' . $this->formatearNumero($p_snc_gastos_administrativos_importe, 2) . '
					} ' . $alic_gastos_administrativos . '
                                	]';

            $this->tot_gastos_administrativos_10_5 = $this->formatearNumero($p_snc_gastos_administrativos_importe, 2);
            $this->tot_gastos_administrativos_ali = $this->formatearNumero($p_gastos_administrativos_importe, 2);
        }
        if (empty($percepciones_gastos_administrativos)) {
            $percepciones_gastos_administrativos = '';
        }
        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'EX') {
            $gastadmin_json = '{
	"codigo": "02",
        "descripcion": "Gastos administrativos",
	"codigoDeposito": "1",
        "codigoTasaIva": "1",
	"cantidad": 1,
	"precio": ' . $this->formatearNumero($precio_gastos_administrativos, 2) . ',
        "importe": ' . $this->formatearNumero($precio_gastos_administrativos, 2) . '
        },';
            $total_iva = 0;
            $subtotal_sin_imp = 0;
            $this->tot_gastos_administrativos = $this->formatearNumero($precio_gastos_administrativos, 2);
        } else {
            $gastadmin_json = '{
				"codigo": "02",
				"descripcion": "Gastos administrativos",
				"descargaStock" : false,
				"cantidad": 1,
				"codigoDeposito": "1",
                                "importeIva": ' . $this->formatearNumero($bonif_gastos_administrativos_iva_formato, 2) . ',
				"codigoUM": "UNI",
				"precio": ' . $this->formatearNumero($precio_gastos_administrativos, 2) . ',
                                "importe": ' . $this->formatearNumero($precio_gastos_administrativos, 2) . ',
                                "importeSinImpuestos": ' . $this->formatearNumero($bonif_gastos_administrativos_sin_iva, 2) . $percepciones_gastos_administrativos . '
                        	}
                     ';
            //Configuro los totales a sumar.
            $this->tot_gastos_administrativos = $this->formatearNumero($precio_gastos_administrativos, 2);
            if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'SNC') {
                $this->tot_gastos_administrativos_iva = $this->formatearNumero($bonif_gastos_administrativos_iva_mas_snc, 2);
            } else {
                $this->tot_gastos_administrativos_iva = $this->formatearNumero($bonif_gastos_administrativos_iva_formato, 2);
            }
            $this->tot_gastos_administrativos_sin_impuestos = $this->formatearNumero($bonif_gastos_administrativos_sin_iva, 2);
            $this->tot_gastos_administrativos_subtotal = $this->formatearNumero($precio_gastos_administrativos, 2);
        }


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

        if (!empty($this->art_total_iva)) {
            $total_iva = array_sum($this->art_total_iva);
        }
        if (!empty($this->tot_arts_sin_impuestos)) {
            $subtotal_sin_imp = array_sum($this->tot_arts_sin_impuestos);
        }
        $subtotal = array_sum($this->art_total_precio);

        if (!empty($this->art_total_solo_iva_snc)) {
            $tot_iva_solo_snc = array_sum($this->art_total_solo_iva_snc);
        }

        /* El total de iva en todos los casos es solo el 21% */
        $total_comp = array_sum($this->art_total_precio);
        // + $art_total_10_50 + $art_total_perc

        if ($bonif_cosme > 0) {
            $bonif_cosme_json = $bonif_cosme_json;
        } else {
            $bonif_cosme_json = '';
        }
        if ($practicosas > 0) {
            $practicosas_json = $practicosas_json;
        } else {
            $practicosas_json = '';
        }
        if ($bonif_adicional > 0) {
            $bonif_adicional_json = $bonif_adicional_json;
        } else {
            $bonif_adicional_json = '';
        }
        if ($gastadmin > 0) {
            $gastadmin_json = $gastadmin_json;
        } else {
            $gastadmin_json = '';
        }
        $articulo = $articulo . $bonif_cosme_json . $practicosas_json . $bonif_adicional_json . $gastadmin_json;
        //. $bonif_cosme . $practicosas . $bonif_adicional . $gastadmin

        /* Si las variables de percepción vienen vacías entonces omito la carga en el total */
        if (empty($this->art_total_perc)) {
            $this->tot_bonif_10_5 = 0;
        }
        if (empty($this->tot_bonif_ali)) {
            $this->tot_bonif_ali = 0;
        }
        if (empty($this->tot_practicosas)) {
            $this->tot_practicosas = 0;
        }
        if (empty($this->tot_practicosas_10_5)) {
            $this->tot_practicosas_10_5 = 0;
        }
        if (empty($this->tot_practicosas_ali)) {
            $this->tot_practicosas_ali = 0;
        }
        if (empty($this->tot_gastos_administrativos)) {
            $this->tot_gastos_administrativos = 0;
        }
        if (empty($this->tot_gastos_administrativos_10_5)) {
            $this->tot_gastos_administrativos_10_5 = 0;
        }
        if (empty($this->tot_gastos_administrativos_ali)) {
            $this->tot_gastos_administrativos_ali = 0;
        }

        if (empty($this->tot_bonif_adicional)) {
            $this->tot_bonif_adicional = 0;
        }
        if (empty($this->tot_bonif_adicional_10_5)) {
            $this->tot_bonif_adicional_10_5 = 0;
        }
        if (empty($this->tot_bonif_adicional_ali)) {
            $this->tot_bonif_adicional_ali = 0;
        }

        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'SNC') {
            //Configuro los totales + las bonificaciones que llegaron desde arriba
            $tot_finales_iva = $total_iva - $this->tot_bonificaciones_iva - $this->tot_practicosas_iva + $this->tot_gastos_administrativos_iva - $this->tot_bonif_adicional_iva;
            /* Tomo el iva original del CSV debido a que no registra la diferencia de 1 centavo en ciertos comprobantes a continuación está la original */
            //$tot_finales_iva = $total_iva - $this->tot_bonificaciones_iva - $this->tot_practicosas_iva + $this->tot_gastos_administrativos_iva - $this->tot_bonif_adicional_iva;
            $tot_finales_subtotal = $subtotal - $this->tot_bonif_subtotal - $this->tot_practicosas_subtotal + $this->tot_gastos_administrativos_subtotal - $this->tot_bonif_adicional_subtotal;
            //
            $tot_finales_subtotal_sin_imp = $subtotal_sin_imp - $this->tot_bonif_sin_impuestos - $this->tot_practicosas_sin_impuestos + $this->tot_gastos_administrativos_sin_impuestos - $this->tot_bonif_adicional_sin_impuestos;
            //
            $tot_finales_tot_comp = $subtotal_sin_imp + $total_iva - $this->tot_bonificaciones - $this->tot_bonif_10_5 - $this->tot_bonif_ali - $this->tot_practicosas - $this->tot_practicosas_10_5 - $this->tot_practicosas_ali + $this->tot_gastos_administrativos + $this->tot_gastos_administrativos_10_5 + $this->tot_gastos_administrativos_ali - $this->tot_bonif_adicional - $this->tot_bonif_adicional_10_5 - $this->tot_bonif_adicional_ali + $art_total_perc;
            //$tot_ctrl = $art_total_perc - $this->tot_bonif_10_5 - $this->tot_practicosas_10_5 + $this->tot_gastos_administrativos_10_5 - $this->tot_bonif_adicional_10_5;
            //$total_comp
            $tot_exento = 0;
            $xmlTyp = '';
        }
        /* Cuando el cliente es CF es posible que las variables ALI vengan sin */
        if (empty($this->tot_bonif_ali)) {
            $this->tot_bonif_ali = 0;
        }
        if (empty($this->tot_practicosas_ali)) {
            $this->tot_practicosas_ali = 0;
        }
        if (empty($this->tot_gastos_administrativos_ali)) {
            $this->tot_gastos_administrativos_ali = 0;
        }
        if (empty($this->tot_bonif_adicional_ali)) {
            $this->tot_bonif_adicional_ali = 0;
        }

        /* Sí el cliente es monotributista calculo los totales en base a lo requerido */
        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'CF' and $this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] !== 'SNC') {
            if ($this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] != '') {
                $tot_finales_iva = $total_iva - $this->tot_bonificaciones_iva - $this->tot_practicosas_iva + $this->tot_gastos_administrativos_iva - $art_total_perc - $this->tot_bonif_adicional_iva;

                //- $this->tot_bonif_adicional_iva - $this->tot_bonif_adicional_ali
            } else {
                $tot_finales_iva = $total_iva - $this->tot_bonificaciones_iva - $this->tot_practicosas_iva + $this->tot_gastos_administrativos_iva - $this->tot_bonif_adicional_iva;
            }

            $tot_finales_subtotal = $subtotal - $this->tot_bonif_subtotal - $this->tot_practicosas_subtotal + $this->tot_gastos_administrativos_subtotal - $this->tot_bonif_adicional_subtotal;
            $tot_finales_subtotal_sin_imp = $subtotal_sin_imp - $this->tot_bonif_sin_impuestos - $this->tot_practicosas_sin_impuestos + $this->tot_gastos_administrativos_sin_impuestos - $this->tot_bonif_adicional_sin_impuestos;
            $tot_finales_tot_comp = $total_comp - $this->tot_bonificaciones - $this->tot_practicosas + $this->tot_gastos_administrativos - $this->tot_bonif_adicional - $this->tot_bonif_ali - $this->tot_practicosas_ali + $this->tot_gastos_administrativos_ali;
            //$tot_prueba = $total_comp - $this->tot_bonificaciones - $this->tot_practicosas + $this->tot_gastos_administrativos - $this->tot_bonif_adicional;
            $tot_exento = 0;
            $xmlTyp = '';

            /* Para clientes que son CF + alícuota calculo nuevos totales */
            if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'CF' and $this->tabla_cliente_cod_cliente['ALI_FIJ_IB'] != '') {
                $tot_finales_iva = $total_iva - $this->tot_bonificaciones_iva - $this->tot_practicosas_iva + $this->tot_gastos_administrativos_iva - $this->tot_bonif_adicional_iva;
                $tot_finales_tot_comp = $total_comp - $this->tot_bonificaciones - $this->tot_practicosas + $this->tot_gastos_administrativos - $this->tot_bonif_adicional - $this->tot_bonif_ali - $this->tot_practicosas_ali + $this->tot_gastos_administrativos_ali + $art_total_perc - $this->tot_bonif_adicional_ali;
            }
        }

        if ($this->tabla_cliente_cod_cliente['COD_CATEGORIA_IVA'] == 'EX') {
            $tot_finales_iva = 0;
            $tot_finales_subtotal = $total_comp - $this->tot_bonificaciones - $this->tot_practicosas + $this->tot_gastos_administrativos - $this->tot_bonif_adicional;
            $tot_finales_subtotal_sin_imp = 0;
            $tot_finales_tot_comp = $total_comp - $this->tot_bonificaciones - $this->tot_practicosas + $this->tot_gastos_administrativos - $this->tot_bonif_adicional;
            $tot_exento = $total_comp - $this->tot_bonificaciones - $this->tot_practicosas + $this->tot_gastos_administrativos - $this->tot_bonif_adicional;
            /* String json para completar si el comprobante es excento */
            $xmlTyp = ',
            "xmlTyp" : "",
            "CuitDestino": "J",
            "TipoExpo": "4",
            "PaisAfip": "200",
            "CodigoIncoterms": "FOB",
            "DescripcionIncoterms": ""  ';
        }

        $ceros = str_pad($numeroFac, 8, '0', STR_PAD_LEFT);
        $n_fac = $pv_pedi . $ceros;
        // Returns the data/output as a string instead of raw data
        $this->facturas[] = '
	{
		"codigoTipoComprobante": "FAC",
		"numeroComprobante": "' . $n_fac . '",
		"codigoTalonario": "' . $talon_fac . '",
		"codigoCliente": "' . $this->tabla_cliente_cod_cliente['COD_CLIENT'] . '",
		"codigoCondicionDeVenta": 1,
		"numeroDeProyecto": "",
		"codigoOperacionRG3685": "00001",
		"codigoClasificacion": "",
		"fechaComprobante": "' . $this->normalizaFecha($this->fechaFac) . '",
		"fechaCierreTesoreria": "' . $this->normalizaFecha($this->fechaFac) . '",
		"codigoListaPrecio": ' . $lista . ',
		"cotizacionVentas": null,
		"codigoContracuenta": "20",
		"codigoDeposito": "1",
		"codigoVendedor": "' . $cod_zona . '",
		"idMotivo": "3",
		"codigoAsiento": "1",
		"leyenda1": "",
		"leyenda2": "",
		"leyenda3": "",
		"leyenda4": "",
		"leyenda5": "",
		"esMonedaExtranjera": false,
		"total" : ' . $this->formatearNumero($tot_finales_tot_comp, 2) . ',
		"totalExento": ' . $this->formatearNumero($tot_exento, 2) . ',
		"totalIva": ' . $this->formatearNumero($tot_finales_iva, 2) . ',
		"subtotal": ' . $this->formatearNumero($tot_finales_subtotal, 2) . ',
		"totalSinImpuestos": ' . $this->formatearNumero($tot_finales_subtotal_sin_imp, 2) . ',
		"subtotalSinImpuestos": ' . $this->formatearNumero($tot_finales_subtotal_sin_imp, 2) . ',
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
				"fechaVencimiento": "' . $this->normalizaFecha($this->fechaFac) . '",
				"importe" : ' . $this->formatearNumero($tot_finales_tot_comp, 2) . '
			}
                        ]' . $xmlTyp . '
            }
        ';

        /* Mato los arrays para que no se acumulen al finalizar el ingreso */
        unset($this->art_total_precio);
        $this->art_total_precio = array();

        unset($this->tot_arts_sin_impuestos);
        $this->tot_arts_sin_impuestos = array();

        unset($this->art_total_iva);
        $this->art_total_iva = array();

        unset($this->art_total_10_50);
        $this->art_total_10_50 = array();

        unset($this->art_total_perc);
        $this->art_total_perc = array();

        unset($total_iva);
        $total_iva = array();

        unset($subtotal);
        $subtotal = array();

        unset($subtotal_sin_imp);
        $subtotal_sin_imp = array();

        unset($total_comp);
        $total_comp = array();

        unset($art_total_10_50);
        $art_total_10_50 = array();

        unset($art_total_perc);
        $art_total_perc = array();

        unset($this->articulos);
        $this->articulos = array();

        unset($this->tot_para_ped);
        $this->tot_para_ped = array();

        $this->tot_bonif_ali = null;
        $this->tot_practicosas_ali = null;
        $this->tot_gastos_administrativos_ali = null;
        $this->tot_bonif_adicional_ali = null;
        $this->tot_bonif_10_5 = null;
        $this->tot_practicosas = null;
        $this->tot_practicosas_10_5 = null;
        $this->tot_gastos_administrativos = null;
        $this->tot_gastos_administrativos_10_5 = null;
        $this->tot_bonif_adicional = null;
        $this->tot_bonif_adicional_10_5 = null;
        $this->tot_bonif_adicional_ali = null;
        $art_total_perc = null;
        $this->tot_bonif_adicional_iva = null;
        //unset($this->p_imp_importe);
        //$this->$this->p_imp_importe = array();
    }

    public function curlJsonCompleto()
    {
        $token = '
        {
        "ApiAuthorization": "' . $this->token_api_local . '";
        "Company: "' . $this->id_empresa . '"
        }
        ';

        //setup the request, you can also use CURLOPT_URL
        $ch = curl_init($this->token['RUTA_LOCAL'] . '/FacturadorVenta/registrar');

        curl_setopt($ch, CURLOPT_TCP_FASTOPEN, true);

        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");

        curl_setopt($ch, CURLOPT_BUFFERSIZE, 10485764);

        curl_setopt($ch, CURLOPT_NOPROGRESS, false);

        curl_setopt($ch, CURLOPT_POSTREDIR, CURL_REDIR_POST_ALL);

        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);

        curl_setopt($ch, CURLOPT_ENCODING, '');

        curl_setopt($ch, CURLOPT_POST, false);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //Set your auth headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'ApiAuthorization: ' . $this->token_api_local . '',
            'Company: ' . $this->id_empresa . '',
            'Content-Type: application/json'
        ));

        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $data_string = implode(',', $this->facturas);
        /* Extraigo el último caracter (para evitar la coma) */
        //$data_string_formato = substr($data_string, 0 , -10);
        /* Le agrego los corchetes */
        $data_final = '[' . $data_string . ']';



        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_final);
        // get stringified data/output. See CURLOPT_RETURNTRANSFER
        $data = curl_exec($ch);

        $data2 = json_decode($data, true);

        $this->mensaje_api = $data2;



        curl_close($ch);
    }

    public function ingresoCliente($codcliente, $cuit, $razon_social, $domicilio, $localidad, $cat_iva, $provincia, $cliente_ori, $nucuit, $cod_vended, $tip_doc, $iibb, $cod_post, $nucuil, $e_mail)
    {
        $token = '
        {
        "ApiAuthorization": "' . $this->token_api_local . '";
        "Company: ' . $this->id_empresa . '"
        }
        ';


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
        } else {
            $ali_no_cat = 12;
            $id_gva41 = null;
        }

        /*Busco el tip_doc para que devuelva el valor según la tabla*/


        if ($tip_doc == 96) {
            $cuit_tabla = $cuit;
            $tipo_doc_gv = 40; //Corresponde a la info que existe en la tabla TIPO_DOCUMENTO_GV 
        } else if ($tip_doc == 80) {
            $cuit_tabla = $nucuit;
            $tipo_doc_gv = 26; //Corresponde a la info que existe en la tabla TIPO_DOCUMENTO_GV 
        } else if ($tip_doc == 86) {
            $cuit_tabla = $nucuil;
            $tipo_doc_gv = 30; //Corresponde a la info que existe en la tabla TIPO_DOCUMENTO_GV 
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
    "E_MAIL": "' . $e_mail . '",
    "WEB": null,
    "NOM_COM": "' . $razon_social . '",
    "DIR_COM": "' . $domicilio . '",
    "ID_GVA151": null,
    "ID_GVA62": null,
    "ID_GVA23": ' . $this->id_gva23 . ',
    "ID_GVA24": null,
    "FECHA_ALTA": "' . date('Y-m-d H:i:s') . '",
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
    "MAIL_DE": "' . $e_mail . '",
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

        //$verbose = fopen('log_$verbose = fopen('log_curl.txt', 'w+');curl.txt', 'w+');
        //curl_setopt($ch, CURLOPT_STDERR, $verbose);
        // get stringified data/output. See CURLOPT_RETURNTRANSFER
        $data = curl_exec($ch);

        // get info about the request
        $info = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $data2 = json_decode($data, true);

        $this->mensaje_api = $data2;

        // close curl resource to free up system resources
        curl_close($ch);
    }

    public $clientesApiLocal;

    /* Método que devuelve Array para devolver los clientes consultados por API */

    public function consultoClientes()
    {
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

        //$verbose = fopen('log_curl.txt', 'w+');
        //curl_setopt($ch, CURLOPT_STDERR, $verbose);
        // get stringified data/output. See CURLOPT_RETURNTRANSFER
        $data = curl_exec($ch);

        // get info about the request
        $info = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $data2 = json_decode($data, true);


        $this->clientesApiLocal = $data2;

        // close curl resource to free up system resources
        curl_close($ch);
    }

    /* Busco el valor de GVA18 para grabar el ID_GVA18 correspondiente */

    public function busco_provincia($provincia)
    {
        $consulta = $this->db_sql->query("SELECT * FROM GVA18 WHERE COD_PROVIN = '$provincia'");
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        return $this->tabla_provin = $fila['ID_GVA18'];
        $consulta->closeCursor();
    }

    /* Busco ID_GVA23 */

    public function busco_vendedor($vendedor)
    {
        $consulta = $this->db_sql->query("SELECT * FROM GVA23 WHERE COD_VENDED = '$vendedor'");
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        return $this->id_gva23 = $fila['ID_GVA23'];
        $consulta->closeCursor();
    }

    /* Busco el valor de GVA23 para dejar el vendedor asignado */

    public $tabla_cliente_cod_cliente = array();

    /* Busco el cliente original para pasarlo a la API */

    public function busco_cliente($cliente)
    {
        $consulta = $this->db_sql->query("SELECT GVA14.ID_GVA14, GVA14.COD_CLIENT, GVA14.CUIT, GVA14.ID_GVA23, GVA14.ID_GVA18, DIRECCION_ENTREGA.DIRECCION, COD_CATEGORIA_IVA, ALI_FIJ_IB, GVA41.PORCENTAJE, GVA41.COD_ALICUO, GVA18.NOMBRE_PRO FROM GVA14 INNER JOIN DIRECCION_ENTREGA ON DIRECCION_ENTREGA.COD_CLIENTE collate Modern_Spanish_CI_AI = GVA14.COD_CLIENT LEFT JOIN CATEGORIA_IVA ON CATEGORIA_IVA.ID_CATEGORIA_IVA = GVA14.ID_CATEGORIA_IVA LEFT JOIN GVA41 ON GVA41.COD_ALICUO = DIRECCION_ENTREGA.ALI_FIJ_IB INNER JOIN GVA18 ON GVA18.ID_GVA18 = GVA14.ID_GVA18 WHERE TELEFONO_1 = '$cliente' AND DIRECCION_ENTREGA.HABITUAL = 'S' ");
        //$consulta = $this->db_sql->query("SELECT GVA14.COD_CLIENT, GVA14.CUIT, GVA14.ID_GVA18, DIRECCION_ENTREGA.DIRECCION FROM GVA14 INNER JOIN DIRECCION_ENTREGA ON DIRECCION_ENTREGA.COD_CLIENTE collate Modern_Spanish_CI_AI = GVA14.COD_CLIENT WHERE TELEFONO_1 = '$cliente' AND DIRECCION_ENTREGA.HABITUAL = 'S'");
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        return $this->tabla_cliente_cod_cliente = $fila;
        $consulta->closeCursor();
    }

    /* Busco alícuota */

    public function busco_alicuota($ali)
    {
        $consulta = $this->db_sql->query("SELECT * FROM GVA41 WHERE COD_ALICUO = '$ali'");
        //$consulta = $this->db_sql->query("SELECT GVA14.COD_CLIENT, GVA14.CUIT, GVA14.ID_GVA18, DIRECCION_ENTREGA.DIRECCION FROM GVA14 INNER JOIN DIRECCION_ENTREGA ON DIRECCION_ENTREGA.COD_CLIENTE collate Modern_Spanish_CI_AI = GVA14.COD_CLIENT WHERE TELEFONO_1 = '$cliente' AND DIRECCION_ENTREGA.HABITUAL = 'S'");
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        return $this->tabla_alicuo = $fila;
        $consulta->closeCursor();
    }

    /* Método dedicado a ingresar los valores en GVA38 */

    public function ingreso_cliente_ocasionalGva38($razon_social)
    {
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

    public function ingreso_encabezadoGva21()
    {

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

    public function actualizoTotalDescuentoGva21($total, $porc_descuento)
    {
        //Conecto con la base de datos
        $query = $this->db_sql;

        /* Creo la query que va a generar el ingreso de los nuevos archivos para ser leídos */
        $ingreso_valor_bd = "UPDATE GVA21 SET TOTAL_PEDI = $total, PORC_DESC = $porc_descuento WHERE NRO_PEDIDO = (SELECT MAX(NRO_PEDIDO) AS NRO_PEDIDO FROM GVA21)";
        $ingreso_valor = $query->prepare($ingreso_valor_bd);
        $ingreso_valor->execute();
        $ingreso_valor->closeCursor();
    }

    /* Actualizo la RutaXML */

    public function actualizoRutaXml($ruta_xml)
    {
        //Conecto con la base de datos
        $query = $this->db_sql;

        /* Creo la query que va a generar el ingreso de los nuevos archivos para ser leídos */
        $ingreso_valor_bd = "UPDATE RXN_PARAMETROS SET RUTAXML = '$ruta_xml'";
        $ingreso_valor = $query->prepare($ingreso_valor_bd);
        $ingreso_valor->execute();
        $ingreso_valor->closeCursor();
    }

    /* Actualizo el XML ingresado */

    public function actualizoXmlIngresado($nombre_archivo)
    {


        $query = $this->db_sql;

        /* Creo la query que va a generar el ingreso de los nuevos archivos para ser leídos */
        $ingreso_valor_bd = "UPDATE RXN_XML SET ESTADO  = 'P' WHERE NOMBRE_ARCHIVO = '$nombre_archivo'";
        $ingreso_valor = $query->prepare($ingreso_valor_bd);
        $ingreso_valor->execute();
        $ingreso_valor->closeCursor();
    }

    public function actualizoReproceso($pedido, $cliente, $nombre_archivo)
    {

        $query = $this->db_sql;

        /* Creo la query que va a generar el ingreso de los nuevos archivos para ser leídos */
        $ingreso_valor_bd = "UPDATE RXN_API_CTRL SET GRABO  = 1 WHERE NOMBRE_ARCHIVO = '$nombre_archivo' AND COD_COMP = '$pedido' AND COD_CLIENT = '$cliente'";
        $ingreso_valor = $query->prepare($ingreso_valor_bd);
        $ingreso_valor->execute();
        $ingreso_valor->closeCursor();
    }

    /* Método que deja todos los comprobantes como pendientes de obtener CAE */

    public function cambioEstadoRechazados()
    {

        $query = $this->db_sql;
        /* Creo la query que va a generar el ingreso de los nuevos archivos para ser leídos */
        $ingreso_valor_bd = "UPDATE GVA12DE SET ESTADO_DE = 'R' WHERE ESTADO_DE IN ('P')";
        $ingreso_valor = $query->prepare($ingreso_valor_bd);
        $ingreso_valor->execute();
        $ingreso_valor->closeCursor();
        /* Cambio el estado de los comprobantes emitidos a Grabó 0 para que se puedan reprocesar desde el menú */
    }

    /* Actualizo los comprobantes que hayan sido reprocesados */

    /* Genero el ingreso del cuerpo para GVA03 */

    public function ingreso_cuerpoGva03($cantidad, $precio)
    {
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

    public function ingreso_cuerpoGva45($descrip, $codigo)
    {
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
