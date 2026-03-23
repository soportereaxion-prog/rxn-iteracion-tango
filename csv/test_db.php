<?php
require 'controlador.php';
try {
    $query = $modelo->db_sql->query("SELECT NOMBRE_ARCHIVO, ESTADO FROM RXN_CSV");
    $result = $query->fetchAll(PDO::FETCH_ASSOC);
    file_put_contents('test_db_output.txt', "RESULTADO TOTAL:\n" . print_r($result, true));
    
    $query2 = $modelo->db_sql->query("SELECT NOMBRE_ARCHIVO FROM RXN_CSV WHERE NOMBRE_ARCHIVO NOT IN ('.','..') AND NOMBRE_ARCHIVO LIKE 'C20%' AND ESTADO = 'I' OR NOMBRE_ARCHIVO LIKE 'CABE20%' AND NOMBRE_ARCHIVO LIKE '%.csv' AND ESTADO = 'I'");
    $result2 = $query2->fetchAll(PDO::FETCH_ASSOC);
    file_put_contents('test_db_output.txt', "\nRESULTADO FILTRADO (leoArchivosBdEncPed):\n" . print_r($result2, true), FILE_APPEND);
    
    echo "OK";
} catch (Exception $e) {
    file_put_contents('test_db_output.txt', "ERROR:\n" . $e->getMessage());
    echo "ERROR";
}
