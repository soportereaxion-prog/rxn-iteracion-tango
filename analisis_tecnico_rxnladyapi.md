# Mapa Técnico — rxnLadyApiLinuxDockerizada

> **Análisis de solo lectura. Ningún archivo fue modificado.**

---

## 1. ESTRUCTURA GENERAL

### Árbol de carpetas

```
rxnLadyApiLinuxDockerizada/
├── index.php                  ← Menú principal (HTML + Bootstrap 5)
├── Conectar.php               ← Clase Conectar_SQL (BD auxiliar DiccionarioCharly)
├── ConectarBase.php           ← Clase Conectar_SQL_static (BD principal LADY_WAY_SRL)
├── ConectarDinamico.php       ← Conexión dinámica (no analizada en detalle)
├── ConectarM.php              ← Conexión alternativa (comentada en uso)
│
├── csv/                       ← Módulo principal de procesamiento
│   ├── index.php              ← Punto de entrada: "Procesar datos"
│   ├── index_reprocesos.php   ← Reprocesar pedidos rechazados
│   ├── index_rechazar_pendientes.php
│   ├── controlador.php        ← Instancia del modelo
│   ├── modelo.php             ← NÚCLEO del sistema (4417 líneas, ~198 KB)
│   ├── vista.php              ← Vista base
│   ├── comboBoxs.php          ← UI de combos y listas
│   └── archivos_json/         ← JSONs generados por facturas
│
├── configuraciones/
│   ├── index.php              ← UI de parámetros del sistema
│   ├── modelo.php             ← Getters/Setters de RXN_PARAMETROS
│   └── vista.php
│
├── copiaFacturas/             ← Módulo de copia de facturas
├── limpiarArchivos/           ← Módulo de limpieza de archivos pendientes
├── ladyarchivos/              ← Directorio de CSVs de ejemplo
├── Ayudas/                    ← Documentación HTML del sistema
├── tmp/
└── log_curl.txt               ← Log de CURL (debug)
```

### Archivos clave

| Archivo | Rol |
|---|---|
| [csv/modelo.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php) | Toda la lógica de negocio central |
| [ConectarBase.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/ConectarBase.php) | Conexión principal SQL Server vía PDO |
| [configuraciones/modelo.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/configuraciones/modelo.php) | Acceso a `RXN_PARAMETROS` |
| [csv/index.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/index.php) | Disparador manual del proceso |

---

## 2. FLUJO DE PROCESAMIENTO

### Paso a paso

```
csv/index.php
  │
  ├─ [al cargar] modelo->leo_ingreso_directorio_csv()
  │       Lee el directorio RUTAXML con '/'
  │       Registra en RXN_CSV los archivos .csv con ESTADO='I'
  │
  └─ [POST Procesar]
        │
        ├─ 1. vacioClientes()
        │       TRUNCATE TABLE RXN_IMP_CLI
        │
        ├─ 2. procesoCsvClientes()
        │       Lee archivos CLI*.csv desde RUTAXML
        │       Llama ingresoCliente() → API Tango
        │       Si ya existe → actTipoDocCliente() → UPDATE directo en GVA14
        │       Marca RXN_CSV.ESTADO='P' para archivos CLI%
        │
        ├─ 3. procesoCsvArticulos()
        │       Lee archivos ARTS*.csv
        │       Llama ingresoArticulo() → API Tango (/Api/Create?process=87)
        │       Marca RXN_CSV.ESTADO='P' para archivos ARTS%
        │
        └─ 4. procesoPedidos('PROCESAR')
                │
                ├─ encPedidos()  → lee CSV C20*.csv o CABE20*.csv
                ├─ cuePedidos()  → lee CSV D20*.csv o DETA20*.csv
                ├─ Por cada pedido:
                │       ctrlPedi() → verifica en RXN_API_CTRL si ya procesó
                │       buscoPedido() → construye el array de artículos JSON
                │       ingresoFactura() → POST /FacturadorVenta/registrar
                │       ingresoMensajesApi() → INSERT en RXN_API_CTRL
                └─ actualizaPedidos() → RXN_CSV.ESTADO='P'
```

### Convención de nombres de archivos CSV

| Prefijo | Contenido |
|---|---|
| `CLI` | Maestro de clientes |
| `ARTS` | Maestro de artículos |
| `C20` / `CABE20` | Encabezados de pedidos (cabecera) |
| `D20` / `DETA20` | Cuerpos de pedidos (detalle/artículos) |

La relación entre cabecera y detalle se establece por nombre: el detalle tiene el mismo sufijo que la cabecera (ej: [C2025111600174.csv](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/ladyarchivos/C2025111600174.csv) ↔ [D2025111600174.csv](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/ladyarchivos/D2025111600174.csv)).

---

## 3. MÉTODO [ingresoFactura](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#1758-2703)

**Ubicación:** [csv/modelo.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php), línea 1758  
**Firma:**
```php
public function ingresoFactura(
    $cliente,           // Código interno del cliente (TELEFONO_1 en GVA14)
    $importe,           // Importe total con IVA
    $articulo,          // String JSON de artículos (pre-construido en buscoPedido)
    $nro_pedido,        // Número de comprobante. Ej: "B0000300001234"
    $cod_zona,          // Código de vendedor/zona
    $imp_sin_impuestos, // Importe sin IVA
    $fecha,             // Fecha del pedido dd/mm/yyyy
    $bonif_cosme,       // Bonificación cosméticos (con IVA)
    $practicosas,       // Bonificación Practicosas (con IVA)
    $gastadmin,         // Gastos administrativos (con IVA)
    $imp_iva,           // IVA total
    $bonif_adicional    // Bonificación adicional (con IVA)
)
```

### Flujo interno de [ingresoFactura](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#1758-2703)

```
1. devuelvoTokens()               → lee RXN_PARAMETROS (URL API, empresa, etc.)
2. curl_init(RUTA_LOCAL/FacturadorVenta/registrar)
3. ingresoPedidoControl()         → INSERT en RXN_PEDIDOS_INGRESADOS
4. busco_cliente($cliente)        → recupera datos del cliente desde BD
5. Determina talonario según PV del comprobante:
      B00003 → talon_fac=5,  FAC_B
      B00007 → talon_fac=15, FAC_ECOMMERCE
      A00003 → talon_fac=1
      E00011 → talon_fac=22, FAC_E_EXPO (EXPO)
6. devuelvoIdPedido()             → lee el número actual de factura desde RXN_PARAMETROS
7. Construye JSON de ítems adicionales:
      - bonif_cosme_json      (código "03")
      - bonif_adicional_json  (código "06")
      - practicosas_json      (código "04")
      - gastadmin_json        (código "02")
   → Cada ítem se discrimina por COD_CATEGORIA_IVA del cliente
8. Calcula totales finales (iva, subtotal, total, exento)
9. Construye $data_string (JSON completo de la factura)
10. curl_exec() → POST a Tango API
11. Graba JSON en archivo: archivos_json\fc_json.json  ← separador Windows
12. Libera arrays acumuladores (unset)
```

### Discriminación impositiva por tipo de cliente

La variable `tabla_cliente_cod_cliente['COD_CATEGORIA_IVA']` es la clave que ramifica toda la lógica:

#### 🔵 CF (Consumidor Final)
- IVA artículo = [(precio / 1.21) * 0.21](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/configuraciones/modelo.php#155-166)
- **Si tiene `ALI_FIJ_IB`** (IIBB): agrega sección `"percepciones"` con:
  - `codigoAlicuota` = `ALI_FIJ_IB` del cliente
  - `porcentaje` = `PORCENTAJE` del cliente
  - `base` = precio sin IVA
  - `importe` = base × porcentaje/100
- Los totales finales descuentan percepciones del IVA calculado

#### 🟡 SNC (Sujeto No Categorizado)
- IVA base 21% se calcula igual que CF
- **Adicionalmente** agrega percepción SNC del **10.5%**:
  - `codigoAlicuota: 11` (código fijo de Tango para SNC)
  - `base` = importe total del renglón (con IVA)
  - `importe` = base × 10.5%
- **Si también tiene `ALI_FIJ_IB`**: agrega un segundo elemento al array `percepciones`
- En totales: `tot_iva` = IVA_21% + SNC_10.5%

#### 🟢 EX (Exento / Exportación)
- Sin cálculo de IVA
- Los artículos usan `codigoTasaIva: "3"` (exento en Tango)
- El JSON agrega bloque `xmlTyp` con datos AFIP de exportación (TipoExpo, PaisAfip, CodigoIncoterms)
- `totalExento` = total del comprobante

---

## 4. INTEGRACIÓN CON TANGO

### Vía API REST (CURL)

| Endpoint | Método | Uso |
|---|---|---|
| `{RUTA_LOCAL}/Api/Create?process=87` | POST | Ingreso de artículos |
| `{RUTA_LOCAL}/FacturadorVenta/registrar` | POST | Ingreso de facturas |
| `{RUTA_LOCAL}` + ruta cliente | POST | Ingreso de clientes |

**Headers en todas las llamadas:**
```
ApiAuthorization: {token_api_local}
Company: {id_empresa}
Content-Type: application/json
```

**Configuración origen:** Todo proviene de `RXN_PARAMETROS`:
- `API_LOCAL` → token de la API local de Tango
- `RUTA_LOCAL` → URL base (ej: `http://192.168.10.10:8080`)
- `ID_EMPRESA` → ID de empresa en Tango

### Vía PDO directo (SQL Server)

Se usa PDO directo para operaciones que la API no expone o son más eficientes por volumen:

| Operación | Tabla | Motivo aparente |
|---|---|---|
| [actTipoDocCliente()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#560-580) | `GVA14` | Actualización de CUIT/tipo doc en cliente existente |
| [vacioClientes()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#396-402) | `RXN_IMP_CLI` | TRUNCATE antes del proceso |
| `leoArchivosBd*()` | `RXN_CSV` | Control de estado de archivos |
| [ctrlPedi()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#405-420) | `RXN_API_CTRL` | Evita re-procesar facturas |
| [ingresoMensajesApi()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#512-546) | `RXN_API_CTRL` | Log de resultados |
| [actIdFac()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#645-656) | `RXN_PARAMETROS` | Actualizar numerador de factura |
| [ingresoPedidoControl()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#465-484) | `RXN_PEDIDOS_INGRESADOS` | Registro de control |
| [buscoTipoDoc()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#369-379) | `TIPO_DOCUMENTO_GV` | Lookup de tipo doc |
| [maxIdGva14()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#355-366) | `GVA14` | Obtiene el MAX de código de cliente |
| [ctrlArtsBase()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#617-628) | `STA11` | Verifica existencia de artículo |

---

## 5. MANEJO DE ARCHIVOS

### Construcción de rutas

```php
// Separador original Windows (configuraciones/modelo.php):
$ruta = $this->leoParametroBd('RUTAXML') . '\\';    // ← doble backslash

// Separador ya corregido en csv/modelo.php (lectura de CSV):
$archivo2 = fopen($this->leoParametroBd('RUTAXML') . '/' . $archivo['NOMBRE_ARCHIVO'], "r");
```

> ⚠️ Hay **inconsistencia**: el módulo de `configuraciones` aún usa `\\` pero `csv/modelo.php` ya usa `/`.

### Archivos abiertos con fopen

| Lugar | Archivo | Modo |
|---|---|---|
| `clientesCsv()` | `{RUTAXML}/CLI*.csv` | `"r"` |
| `encPedidos()` | `{RUTAXML}/C20*.csv` | `"r"` |
| `cuePedidos()` | `{RUTAXML}/D20*.csv` | `"r"` |
| `articulosCsv()` | `{RUTAXML}/ARTS*.csv` | `"r"` |
| `procesoPedidos()` | `detalle_proceso.txt` | `"a"/"w"` (log relativo) |
| `ingresoFactura()` | `archivos_json\\fc_json.json` | `"a"` ← **backslash Windows** |
| `ingresoFactura()` | `archivos_json\\....json` | `"w"` |

### Dependencias de SO

- `ConectarBase.php` y `Conectar.php`: rutas `E:\plataformasWeb\...` hardcodeadas (variables estáticas sin uso activo)
- `ingresoFactura()` línea 2627: `"archivos_json\\\" . date(...)` → backslash hardcodeado
- `ingresoFactura()` línea 2630: `fopen("archivos_json\\fc_json.json", "a")` → backslash hardcodeado

---

## 6. ACCESO A CONFIGURACIÓN — `leoParametroBd()`

**Definición:**
```php
public function leoParametroBd($nombre_col)
{
    $consulta = $this->db_sql->query("SELECT $nombre_col FROM RXN_PARAMETROS");
    $filas = $consulta->fetch(PDO::FETCH_ASSOC);
    return $this->param = $filas[$nombre_col];
    $consulta->closeCursor(); // ← código muerto, nunca se ejecuta
}
```

> ⚠️ El `closeCursor()` está **después del `return`**. Es código muerto que no causa error pero no libera el cursor.

### Parámetros utilizados desde `RXN_PARAMETROS`

| Columna | Uso |
|---|---|
| `RUTAXML` | Ruta base donde se leen los CSV |
| `RUTA_LOCAL` | URL base de la API de Tango |
| `API_LOCAL` | Token de autenticación API local |
| `API_TIENDAS` | Token de API de tiendas |
| `ID_EMPRESA` | ID empresa en Tango |
| `FAC_B` | Numerador de facturas B (local) |
| `FAC_ECOMMERCE` | Numerador de facturas E-Commerce |
| `FAC_E_EXPO` | Numerador de facturas de exportación |
| `TALON_PED` | Talonario de pedidos |
| `BASE_DE_DATOS` | Nombre de la base de datos origen |

---

## 7. PUNTOS CRÍTICOS

### A. Código repetido (alto riesgo)

El método `generoJsonCompleto()` (línea 2708) es una **copia casi exacta** de `ingresoFactura()`. Ambos tienen:
- Misma lógica de PV/talonario
- Misma discriminación CF/SNC/EX para cada tipo de ítem
- Mismos cálculos de totales

Si se corrige un bug de cálculo en uno, **no se propaga al otro automáticamente**.

### B. Lógica acoplada en un único método

`ingresoFactura()` abarca ~950 líneas y mezcla:
1. Configuración de conexión CURL
2. Lógica impositiva por tipo de cliente (×4 tipos de ítems × 3 categorías = 12 ramas)
3. Cálculo de totales cruzados
4. Construcción del string JSON
5. Ejecución HTTP
6. Escritura en archivo de log
7. Limpieza de variables de estado

### C. Riesgos en migración a Linux/Docker

| Riesgo | Ubicación | Descripción |
|---|---|---|
| **Backslash en rutas** | `ingresoFactura()` líneas 2627-2635 | `archivos_json\\fc_json.json` falla en Linux |
| **Backslash en configuraciones** | `configuraciones/modelo.php` línea 81 | `$ruta . '\\\\'` falla en Linux |
| **Rutas hardcodeadas** | `ConectarBase.php` y `Conectar.php` | Rutas `E:\plataformasWeb\...` (estáticas, aparentemente sin uso activo) |
| **Usuario-agente Windows** | `ingresoFactura()` línea 1781 | `CURLOPT_USERAGENT: "MSIE 6.0; Windows NT 5.0"` (funcional pero engañoso) |
| **Driver SQLSRV** | `ConectarBase.php` | Requiere `pdo_sqlsrv` en Linux (extensión oficial de Microsoft, compatible) |
| **Ruta relativa de logs** | `procesoPedidos()` línea 898 | `fopen("detalle_proceso.txt", "a")` → path relativo al CWD del servidor |

### D. Mezcla de responsabilidades

- `csv/modelo.php` combina: acceso a BD, lectura de CSV, llamadas HTTP, cálculos impositivos, escritura de logs y gestión de estado interno con propiedades públicas.
- No existe separación entre capa de datos, lógica de negocio y capa de integración.

### E. Variables de instancia como acumuladores

Las propiedades `art_total_iva`, `art_total_perc`, `art_total_10_50`, etc. se acumulan iteración a iteración dentro de `buscoPedido()` (una por artículo) y se consumen en `ingresoFactura()`. Si el `unset`/reset al final de `ingresoFactura()` no se ejecuta correctamente (ej. por error), los totales del siguiente pedido serán incorrectos.

### F. Consultas SQL con valores interpolados directamente

```php
// ej: leoParametroBd()
$consulta = $this->db_sql->query("SELECT $nombre_col FROM RXN_PARAMETROS");
// ej: selectGva14()
$consulta = $this->db_sql->query("SELECT TOP(1)* FROM GVA14 WHERE TELEFONO_1 = '$cod_client'");
```
Variables de usuario interpoladas en queries → riesgo de SQL Injection si los valores no son confiables.

### G. `closeCursor()` después de `return` (código muerto)

En casi todos los métodos de consulta (`leoArchivosBd`, `devuelvoTokens`, `maxIdGva14`, etc.), el `$consulta->closeCursor()` está ubicado **después del `return`** y nunca se ejecuta. Esto puede dejar cursores abiertos dependiendo del driver.

---

## 8. RESUMEN EJECUTIVO

### ¿Qué hace la aplicación de punta a punta?

**rxnLadyApi** es un sistema de integración entre un negocio de distribución (Lady Way) y el ERP **Tango Gestión**. Funciona como puente entre archivos CSV (generados por otro sistema) y la API REST de Tango, más operaciones SQL directas donde la API no alcanza.

El operador accede a un menú web, selecciona una fecha y presiona "Procesar". El sistema:
1. Detecta automáticamente todos los CSVs nuevos en el directorio configurado
2. Registra sus nombres en SQL Server (`RXN_CSV`)
3. Procesa primero los **clientes** (crea en Tango si no existen, actualiza datos si ya están)
4. Procesa los **artículos** (crea en Tango si no existen)
5. Procesa las **facturas** (lee cabecera+detalle de pedidos, construye el JSON con impuestos discriminados y lo envía a Tango)
6. Registra el resultado de cada operación en `RXN_API_CTRL`

### Módulos principales

| Módulo | Función |
|---|---|
| `csv/` | **Core**: detecta, procesa y factura |
| `configuraciones/` | Gestión de parámetros del sistema |
| `limpiarArchivos/` | Limpieza de CSVs pendientes |
| `copiaFacturas/` | Copia de facturas ya procesadas |

### Partes más delicadas

1. **`ingresoFactura()`** (~950 líneas): es el corazón del sistema. Cualquier error en el cálculo impositivo o en la construcción del JSON provoca el rechazo de la factura por Tango. Combina tres categorías de clientes × cuatro tipos de ítems = lógica muy ramificada y sin tests.

2. **Acumuladores de estado**: las propiedades `art_total_iva`, `art_total_perc`, etc. son estado mutable compartido entre pedidos. Si el reset falla, los datos se contaminan.

3. **Rutas de archivo**: el directorio `archivos_json\\` con backslash hardcodeado es el punto de falla más inmediato al ejecutar en Linux. Ya fue parcialmente corregido en los `fopen` de lectura de CSV, pero **no en los de escritura**.

4. **Numeración de facturas**: el contador en `RXN_PARAMETROS` (columnas `FAC_B`, `FAC_ECOMMERCE`, `FAC_E_EXPO`) se incrementa solo si `ingresoFactura()` recibe confirmación de éxito (`Succeeded`). Si hay error de red o CURL falla silenciosamente, la numeración no avanza pero Tango podría haber grabado la factura.
