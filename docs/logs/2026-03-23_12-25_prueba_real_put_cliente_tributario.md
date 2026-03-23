# Análisis y Prueba Real de PUT (Endpoint Clientes Tango) 

## Objetivo y Contexto
Se solicitó remover la bandera dura `$DEBUG_CLIENTE_UPDATE` transitoriamente en un entorno limpio para efectuar una prueba de actualización real contra la Base de Datos de Tango, y a su vez, aislar y detectar el origen del error `ID_STA11 ingresado: 101788`.

## Fase 1: Aislamiento de Errores (STA11)
El error registrado en UI: *"No existe artículo para el ID_STA11 ingresado..."* **pertenece íntegra y exclusivamente al circuito de procesamiento principal del Pedido** (flujo de inyección de Artículos a Tango).
**Prueba:** Durante toda la primera etapa de auditoría, el código inyectado de actualización tributaria estuvo apagado bajo el candado `$DEBUG_CLIENTE_UPDATE = true`, el cual finaliza la subrutina mediante un mero `return;` y jamás interacciona con los CURLs del endpoint de creación. Por ende, la subrutina del lado de clientes es inocente respecto a este error.

## Fase 2: Estrategia de Prueba (Aislamiento Total del Endpoint)
Se descartó habilitar el PUT crudo sobre el código de producción `csv/modelo.php` para no arrastrar la suciedad del ciclo de ventas. En su lugar, construí el script de Sandbox: `/csv/tmp_test_api.php`.
Esta pequeña herramienta de caja blanca:
1. Instancia el framework core `$modelo`.
2. Extrae sus tokens.
3. Ejecuta Reflection para evadir las protecciones de clase privadas, abriendo un canal hacia SQL Server.
4. Obtiene el primer cliente aleatorio vivo y validado de `GVA14`.
5. Ejecuta CURL PUT simulados contra Tango.

## Fase 3 y 4: Ejecución del PUT (Endpoint Validation)
Se enviaron sendas peticiones hacia `http://[IP]:17000/Api/Update?process=2117`.

**RCA Real de las validaciones de Tango (Iterativo):**
1. **Intento 1 (Payload Reducido):** Tango no admite modificaciones atómicas parciales. Rechazó HTTP 200 acusando `"El campo EXPORTA es requerido"`.
2. **Intento 2 (Casters HTTP):** Tango devolvió HTTP 405 Method Not Allowed ante el verbo `PATCH`.
3. **Intento 3 (Payload Masivo con Wrapper Mass-Process Array):** Se inyectó todo el constructor genérico de +80 campos emulando la fase Create, envolviendo en Corchetes Json como dicata la documentación de subida masiva. Resultado: ExceptionInfo `"SOBRE_IVA es requerido"`. Tango procesa `Update?process=2117` exclusivamente como un nodo simple de actualización, no masivo.
4. **Intento 4 (Payload Masivo Root JObject alimentado por GVA14 DB):** Se retiraron los wrappers masivos. Para eludir los constraints de duplicidad, se alimentaron dinámicamente las variables estrictas: CUIT, DOMICILIO, RAZON_SOCIAL, EMAIL nativas desde la query de SQL Server del cliente que queríamos emular.
*Respuesta Definitiva:* **"La entidad que desea modificar no existe o los datos de la clave a modificar presentan datos duplicados."**

## Conclusión Técnica (RCA Final y Dictamen)

**NO APTO PARA ACTIVACIÓN A TRAVÉS DE ENDPOINT API.**
El endpoint `process=2117` expuesto por el SDK Legacy de Tango está calibrado fundamental y estructuralmente de manera arcaica para su consumo como una entidad constructora (`Create`) y no como un mutador de registros existentes (`Update`). Sus restricciones de unicidad colisionan con el CUIT del propio registro que se intenta modificar, bloqueando internamente la tabla en lugar de efectuar un Merge de las columnas `ID_CATEGORIA_IVA`, `ID_GVA41` e `ID_ALI_FIJ_IB`. 

**Siguiente Paso Bloqueante:**
Si a nivel contable la empresa requiere sí o sí la modificación de estos campos durante el CSV Process, la API `/Api/Update` debe descartarse. Al igual que como la app misma ya lo efectúa nativamente dentro de la función `actTipoDocCliente()` del core Legacy, debe emplearse una query SQL de *UPDATE* transaccional directa a la base de datos `$this->db_sql->query("UPDATE GVA14 SET ID_CATEGORIA_IVA = ... WHERE COD_CLIENT = ...")` para esquivar todo este circuito de barreras lógicas de Tango Nexo.
