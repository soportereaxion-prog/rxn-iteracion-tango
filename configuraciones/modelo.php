<?php



require("vista.php");


class modelo extends vista
{

    private $db;
    private $db_sql;

    public function __construct()
    {
        /* String de conexion con la base de datos */
        //require_once("../ConectarM.php");
        //$this->db = Conectar::conexion();

        require_once("../ConectarBase.php");

        $this->db_sql = Conectar_SQL_static::conexion_origen();
    }

    /* Selecciono el talonario para filtrar */

public function traerBase()
{
    try {
        // Si se presionó el boton 'Editar', actualizamos el valor de la tabla BASE_DE_DATOS
        if (isset($_POST['Editar'])) {
            $nuevoValor = $_POST['Nombre_base'];

          
            $sql = "UPDATE RXN_PARAMETROS SET BASE_DE_DATOS = '" . $nuevoValor . "'";
            $this->db_sql->exec($sql);//exec ejecuta la consulta del update.
        }

        // Luego siempre traemos el valor actualizado para mostrarlo en el input,esto se ejecuta siempre
        $consulta = $this->db_sql->query("SELECT BASE_DE_DATOS FROM RXN_PARAMETROS");
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        $consulta->closeCursor();
        
            return $fila['BASE_DE_DATOS']; // se muestra en el input
       
    } catch (PDOException $e) {
        return 'Error '; // retorno vacío si hay error
    }
}



    








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

    public function leo_directorio_xml()
    {

        //Conecto con la base de datos
        $query = $this->db_sql;

        //Leo los archivos del directorio
        /* Hardening Linux: separador compatible con Linux y Windows */
        $ruta_de_la_carpeta = rtrim($this->leoParametroBd('RUTAXML'), '/\\') . '/';
        //echo 'Ruta:'.$this->leoParametroBd('RUTAXML').'\\';
        if ($handler = opendir($ruta_de_la_carpeta)) {
            echo "Archivos procesados: <br>";
            while (false !== ($file = readdir($handler))) {

                /* Creo la query que va a generar el ingreso de los nuevos archivos para ser leídos */
                $ingreso_nombre_archivo = "IF NOT EXISTS (SELECT NOMBRE_ARCHIVO FROM [RXN_XML] WHERE NOMBRE_ARCHIVO = '$file')
                                BEGIN INSERT INTO [dbo].[RXN_XML]
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

                echo "$file.<br>";
            }
            closedir($handler);
        }
    }

    /* Busco todos los archivos leídos en la base de datos para luego recorrerlos */

    public function leoArchivosBd()
    {

        $consulta = $this->db_sql->query("SELECT NOMBRE_ARCHIVO FROM RXN_XML WHERE NOMBRE_ARCHIVO NOT IN ('.','..') AND NOMBRE_ARCHIVO LIKE '%.PRA%' AND ESTADO = 'I'");

        while ($filas = $consulta->fetch(PDO::FETCH_ASSOC)) {

            $this->nombre_archivo[] = $filas;
        }

        $resultado = $this->nombre_archivo;
        $consulta->closeCursor();
        return $resultado;
    }

    /* Devuelvo el valor del talonario seleccionado */

    public function devuelvoValorTalonSelec()
    {
        $consulta = $this->db_sql->query("SELECT TALON_PED FROM RXN_PARAMETROS");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        $resultado = $filas['TALON_PED'] ?? null;
        $consulta->closeCursor();
        return $this->talon_ped = $resultado;
    }

    /* Llamo a la ruta XML configurada */
    private $ruta_xml;
    public function rutaXmlConfigurada()
    {
        $consulta = $this->db_sql->query("SELECT RUTAXML FROM RXN_PARAMETROS");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        $resultado = $filas['RUTAXML'] ?? null;
        $consulta->closeCursor();
        return $this->ruta_xml = $resultado;
    }

    /* Valor entero para la generación del número de factura */

    public function facB()
    {
        $consulta = $this->db_sql->query("SELECT FAC_B FROM RXN_PARAMETROS");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        $resultado = $filas['FAC_B'] ?? null;
        $consulta->closeCursor();
        return $this->ruta_xml = $resultado;
    }

    /* Valor entero para la generación del número de factura */

    public function facEcommerce()
    {
        $consulta = $this->db_sql->query("SELECT FAC_ECOMMERCE FROM RXN_PARAMETROS");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        $resultado = $filas['FAC_ECOMMERCE'] ?? null;
        $consulta->closeCursor();
        return $this->ruta_xml = $resultado;
    }

    /* Valor entero para la generación del número de factura */

    public function facExpo()
    {
        $consulta = $this->db_sql->query("SELECT FAC_E_EXPO FROM RXN_PARAMETROS");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        $resultado = $filas['FAC_E_EXPO'] ?? null;
        $consulta->closeCursor();
        return $this->ruta_xml = $resultado;
    }

    /* Llamo al valor de tiendas para el input */

    public function selectRxnParametrosApiTiendas()
    {
        $consulta = $this->db_sql->query("SELECT API_TIENDAS FROM RXN_PARAMETROS");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        $resultado = $filas['API_TIENDAS'] ?? null;
        $consulta->closeCursor();
        return $this->ruta_xml = $resultado;
    }

    /* Llamo al valor de la ruta */

    public function selectRxnParametrosRutaLocal()
    {
        $consulta = $this->db_sql->query("SELECT RUTA_LOCAL FROM RXN_PARAMETROS");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        $resultado = $filas['RUTA_LOCAL'] ?? null;
        $consulta->closeCursor();
        return $this->ruta_xml = $resultado;
    }

    /* LLamo al valor del token de la api local para el input */

    public function selectRxnParametrosApiLocal()
    {
        $consulta = $this->db_sql->query("SELECT API_LOCAL FROM RXN_PARAMETROS");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        $resultado = $filas['API_LOCAL'] ?? null;
        $consulta->closeCursor();
        return $this->ruta_xml = $resultado;
    }

    /* Llamo al valor de la ID de la empresa */

    public function selectRxnParametrosIdEmpresa()
    {
        $consulta = $this->db_sql->query("SELECT ID_EMPRESA FROM RXN_PARAMETROS");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        $resultado = $filas['ID_EMPRESA'] ?? null;
        $consulta->closeCursor();
        return $this->ruta_xml = $resultado;
    }

    /* Devuelvo el parámetro buscado según corresponda */

    public function leoParametroBd($nombre_col)
    {
        $consulta = $this->db_sql->query("SELECT $nombre_col FROM RXN_PARAMETROS");

        $filas = $consulta->fetch(PDO::FETCH_ASSOC);

        $resultado = $filas[$nombre_col] ?? null;
        $consulta->closeCursor();
        return $resultado;
    }

    /* Ejecuto la lectura del archivo para su posterior ingreso */

    public function procesoCsv()
    {
        /* Ejecuto el método que guarda el nombre en un array */
        $this->leoArchivosBd();
        foreach ($this->nombre_archivo as $archivo) {
            /* Recorro las iteraciones para ingresar a pantalla */
            //echo $archivo['NOMBRE_ARCHIVO'];
            $archivo = fopen($this->leoParametroBd('RUTAXML') . '\\' . $archivo['NOMBRE_ARCHIVO'], "r");
            echo 'Ruta:' . $this->leoParametroBd('RUTAXML') . '\\' . $archivo['NOMBRE_ARCHIVO'] . '<br>';

            /* Recorro el XML de acuerdo a sus etiquetas */
            //foreach ($csv->cabecera as $nodo) {
            //    echo $nodo->cliente . '<br>';
            //    /* Paso por parámetro los valores que van a dar ingreso al método de encabezado para el cliente ocasional */
            //    $this->ingreso_cliente_ocasionalGva38($nodo->cliente);
            //    /* Ingreso el encabezado para GVA21 */
            //    $this->ingreso_encabezadoGva21();
            //}
            while (($datos = fgetcsv($archivo, ",")) == true) {
                $num = count($datos);
                //Recorremos las columnas de esa linea
                for ($columna = 0; $columna < $num; $columna++) {
                    echo $datos[$columna] . "\n";
                }
            }
            //Cerramos el archivo
            fclose($archivo);
        }
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

    /* Actualizo el Modo de Proceso */

    public function actualizarModoProceso($modo_proceso)
    {
        //Conecto con la base de datos
        $query = $this->db_sql;

        $ingreso_valor_bd = "UPDATE RXN_PARAMETROS SET MODO_PROCESO = '$modo_proceso'";
        $ingreso_valor = $query->prepare($ingreso_valor_bd);
        $ingreso_valor->execute();
        $ingreso_valor->closeCursor();
    }

    /* Actualizo el XML ingresado */

    public function actualizoXmlIngresado($nombre_archivo)
    {

        //Conecto con la base de datos
        $query = $this->db_sql;

        /* Creo la query que va a generar el ingreso de los nuevos archivos para ser leídos */
        $ingreso_valor_bd = "UPDATE RXN_XML SET ESTADO  = 'P' WHERE NOMBRE_ARCHIVO = '$nombre_archivo'";
        $ingreso_valor = $query->prepare($ingreso_valor_bd);
        $ingreso_valor->execute();
        $ingreso_valor->closeCursor();
    }

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
