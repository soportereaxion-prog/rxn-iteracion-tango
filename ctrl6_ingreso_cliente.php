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

$token = '
{
"ApiAuthorization": "c894f9cf-9078-4cce-b296-888b7439e390";
"Company: 283"
}
';
//setup the request, you can also use CURLOPT_URL
$ch = curl_init('http://svrrxn:17001/Api/Create?process=2117');

// Returns the data/output as a string instead of raw data

$data_string = '
{
    "COD_GVA14": "B39999",
    "COD_CLIENT": "B39999",
    "ID_TIPO_DOCUMENTO_GV": 30,
    "CUIT": null,
    "RAZON_SOCI": "UNA RAZON SOCI",
    "DOMICILIO": null,
    "LOCALIDAD": null,
    "C_POSTAL": null,
    "ID_GVA05": 1,
    "ID_GVA18": 1,
    "TELEFONO_1": null,
    "TELEFONO_2": null,
    "TELEFONO_MOVIL": null,
    "E_MAIL": null,
    "WEB": null,
    "NOM_COM": null,
    "DIR_COM": null,
    "ID_GVA151": null,
    "ID_GVA62": null,
    "ID_GVA23": null,
    "ID_GVA24": null,
    "FECHA_ALTA": "2023-12-29T00:00:00",
    "CUMPLEANIO": "2023-12-29T00:00:00",
    "FECHA_INHA": null,
    "SEXO": null,
    "OBSERVACIO": null,
    "ID_CATEGORIA_IVA": 1,
    "ID_GVA41_NO_CAT": null,
    "SOBRE_IVA": "S",
    "PORC_EXCL": 0,
    "II_L": "S",
    "II_D": "S",
    "SOBRE_II": "S",
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
    "MON_CTE": false,
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
    "APLICA_MORA": "S",
    "ID_INTERES_POR_MORA": 1,
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
    "DET_ARTIC": "A",
    "INC_COMENT": "N",
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
    "COD_GVA18": null,
    "ALI_NO_CAT": 1,
    "RG_3572_TIPO_OPERACION_HABITUAL_VENTAS": null,
    "COD_GVA05": null,
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
    "COD_TRANSP": null,
    "COD_VENDED": null,
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
            "DIRECCION": null,
            "LOCALIDAD": null,
            "CODIGO_POSTAL": null,
            "ID_GVA18": 2,
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
            "LIB": "S",
            "PORC_L": 0,
            "IB_L": true,
            "ID_ALI_FIJ_IB": null,
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
//curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);

//curl_setopt($ch, CURLOPT_POSTFIELDS, array('{
//"TokenCS": "Data Source=ESCRITORIO-AGUI;Initial Catalog=CALIFA;
//Integrated Security=False;User ID=Axoft;Password=Axoft"
//}'));

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

//Set your auth headers
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'ApiAuthorization: c894f9cf-9078-4cce-b296-888b7439e390',
    'Company: 283',
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

print_r($data2);

//print_r($data2);
echo $data;
print_r($info);

// close curl resource to free up system resources
curl_close($ch);

?>