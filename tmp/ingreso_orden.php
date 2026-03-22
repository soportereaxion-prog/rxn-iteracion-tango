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

$token = "330b3a3a-aa60-42d7-acbb-ff9676e523c1_14396";
//setup the request, you can also use CURLOPT_URL
$ch = curl_init('https://tiendas.axoft.com/api/Aperture/order');



/*
IMPORTANTES LÍNEAS PARA DESACTIVAR EL USO DE CERTIFICADOS DEL SERVER
Desactivar el uso de certificados online en el server local*/
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
/*Desactivar el uso de certificados online en el server local*/

$orden = 
'{
  "Date": "2022-10-10T00:00:00",
  "Total": 30.0,
  "PaidTotal": 30.0,
  "FinancialSurcharge": 0.0,
  "WarehouseCode": "1",
  "SellerCode": "1",
  "TransportCode": "01",
  "SaleCondition": "1",
  "OrderID": "1000",    
  "OrderNumber": "1000",
  "OrderCounterfoil": 10, // Informa el número de Talonario de Pedidos
  "ValidateTotalWithPaidTotal": false,
  "ValidateTotalWithItems": false,  //Para el caso de DUM donde se informe unidad de Ventas y la equivalencia sea distinto de 1 
  "Customer": {
    "CustomerID": 1000,
    "Code": "",      
    "DocumentType": "96",
    "DocumentNumber": "99999999",
    "IVACategoryCode": "CF",
    "User": "Test",
    "Email": "test@axoft.com",
    "FirstName": "Test",
    "LastName": "Test",
    "BusinessName": "",        
    "Street": "Cerrito",
    "HouseNumber": "1000",
    "Floor": "",
    "Apartment": "",
    "City": "CABA",
    "ProvinceCode": "1",
    "PostalCode": "1000",
    "PhoneNumber1": "9999-9999",
    "PhoneNumber2": "99-9999-9999",
    "BusinessAddress": "Dirección negocio",
    "NumberListPrice": 10
  },
  "OrderItems": [
    {
      "ProductCode": "1000",
      "SKUCode": "ART_DOBLEUNIDAD",    
      "VariantCode": null,        
      "Description": "Artículo de doble unidad de medida",
      "VariantDescription": null,
      "Quantity": 1.0,
      "UnitPrice": 30.0,  
      "DiscountPercentage": 0.0,
      "MeasureCode":"UNI",  //Código de medida con el cual se generará el pedido
      "SelectMeasureUnit": "V" //Unidad de medida seleccionada (P: Stock 1 - Precios y Costos;  S: Stock 2 ;  V: Ventas) con la cual se generará el pedido
    }
  ],
 "CashPayment": {
    "PaymentID": 1000,
    "PaymentMethod": "MPA",
    "PaymentTotal": 30.0
  }
}';

curl_setopt($ch, CURLOPT_POST, true);

curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");

curl_setopt($ch, CURLOPT_POSTFIELDS, $orden);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

//Set your auth headers
curl_setopt($ch, CURLOPT_HTTPHEADER, 
        
    array(
   'Content-Type: application/json',
   'Expect:',
   'Content-Length: '. strlen($orden),
   'Authorization: Bearer ' . $token,
   'accesstoken: 330b3a3a-aa60-42d7-acbb-ff9676e523c1_14396'
   ));

curl_setopt($ch, CURLOPT_VERBOSE, true);

$verbose = fopen('log_curl.txt', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

$data = curl_exec($ch);

$info = curl_getinfo($ch, CURLINFO_HTTP_CODE);

$data2 = json_decode($data, true);

//El siguiente script da los valors de los Json definidos
//$data2 = var_dump(json_decode($data, true));
//var_dump(json_decode($data,false,512,JSON_BIGINT_AS_STRING));

print_r($data2);

curl_close($ch);




?>