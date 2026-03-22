# Etapa 4 — Switch de Configuración: Pedidos vs Facturas

## Descripción

El objetivo de esta etapa es integrar el nuevo flujo de [ingresoPedido](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#1400-1554) dentro del circuito principal de procesamiento ([procesoPedidos](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#785-916)), seleccionando el destino de los datos de forma dinámica mediante un parámetro de configuración persistente (`MODO_PROCESO`) administrado desde "Configuración de directorio". Se evitará la duplicación de código y se preservará intacta la lógica histórica de [ingresoFactura](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#1874-2813).

**Archivos afectados:**
- [configuraciones/index.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/configuraciones/index.php) (Frontend)
- [configuraciones/modelo.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/configuraciones/modelo.php) (Backend de Configuración)
- [csv/modelo.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php) (Núcleo de Procesamiento)

---

## Cambios propuestos

### 1. Base de Datos (Requisito Previo)
> [!IMPORTANT]
> Se requiere que la tabla `RXN_PARAMETROS` tenga la columna `MODO_PROCESO`.
Se deberá ejecutar el siguiente SQL en producción antes de desplegar:
```sql
ALTER TABLE RXN_PARAMETROS ADD MODO_PROCESO VARCHAR(20) DEFAULT 'FACTURA';
```

### 2. Frontend de Configuración: [configuraciones/index.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/configuraciones/index.php)
Se añadirá un control seleccionable (Dropdown) en la tabla visual de parámetros, junto a "Base de datos" o en una nueva celda.
*   Leerá el valor actual usando `$modelo->leoParametroBd('MODO_PROCESO')`.
*   Mostrará dos `<option>`: `FACTURA` y `PEDIDO`.
*   El botón "Editar" incluirá `$_POST['modo_proceso']` en su envío.
*   Se actualizará la invocación a `$modelo->actualizoRutaXml(...)` agregándole este nuevo parámetro.

### 3. Backend de Configuración: [configuraciones/modelo.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/configuraciones/modelo.php)
*   Modificar la firma de [actualizoRutaXml(...)](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#4417-4428) para recibir `$modo_proceso`.
*   Actualizar la query interna de [actualizoRutaXml](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#4417-4428):
```sql
UPDATE RXN_PARAMETROS SET RUTAXML = ..., MODO_PROCESO = '$modo_proceso'
```

### 4. Flujo Principal: [csv/modelo.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php)
*   Al comienzo de [procesoPedidos($menu)](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#785-916), leer la configuración local:
```php
$this->devuelvoTokens();
$modo_proceso = $this->leoParametroBd('MODO_PROCESO') ?? 'FACTURA';
```
*(Nota: [devuelvoTokens()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#338-349) y los métodos existentes ya leen de parámetros, o usaremos directamente `$this->leoParametroBd('MODO_PROCESO')`)*.

*   Dentro del bucle principal `foreach ($dato_pedi_enc as $pedi_enc)`, reemplazar el tramo que hoy es lineal hacia [buscoPedido](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#1033-1270) e [ingresoFactura](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#1874-2813) por una bifurcación:

```php
if ($this->ctrlPediRxnApiCtrl['COD_COMP'] == '') {
    
    // SWITCH PRINCIPAL 
    if ($modo_proceso === 'PEDIDO') {
        // --- FLUJO NUEVO ---
        $this->buscoPedidoRXN($pedi_enc['N_COMP'], $pedi_enc['COD_CLIENT'], $pedi_enc['ORDEN'], $pedi_enc['NOMBRE_ARCHIVO']);
        $this->ingresoPedido($pedi_enc['COD_CLIENT'], $pedi_enc['IMPORTE'], $this->articulos, $pedi_enc['N_COMP'], $pedi_enc['COD_ZONA'], $pedi_enc['IMPORTE_GRAVADO'], $pedi_enc['FECHA'], $pedi_enc['BONIFCOSME'], $pedi_enc['PRACTICOSAS'], $pedi_enc['GASTADMIN'], $pedi_enc['IMP_IVA'], $pedi_enc['BONIF_ADIC']);
        
        $grabo = $this->mensaje_api['Succeeded'] ? 1 : 0;
        // Mapeo id y stringConvertido usando lógica homóloga
    } else {
        // --- FLUJO HISTÓRICO (FACTURA) ---
        $this->buscoPedido($pedi_enc['N_COMP'], $pedi_enc['COD_CLIENT'], $pedi_enc['ORDEN'], $pedi_enc['NOMBRE_ARCHIVO']);
        $this->ingresoFactura($pedi_enc['COD_CLIENT'], $pedi_enc['IMPORTE'], implode($this->articulos), $pedi_enc['N_COMP'], $pedi_enc['COD_ZONA'], $pedi_enc['IMPORTE_GRAVADO'], $pedi_enc['FECHA'], $pedi_enc['BONIFCOSME'], $pedi_enc['PRACTICOSAS'], $pedi_enc['GASTADMIN'], $pedi_enc['IMP_IVA'], $pedi_enc['BONIF_ADIC']);
        
        // Bloque original: actIdFac, repoceso, $grabo = 1, generoJsonCompleto...
    }

    // --- FLUJO COMÚN DE CIERRE ---
    // if(!empty($this->mensaje_api['Comprobantes'][0]['exceptionMessage']))...
    // $this->ingresoMensajesApi(...)
}
```

*   **[procesoPedidosRXN()](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#2814-2921)**: Este método quedará **obsoleto** en el código. No se eliminará físicamente aún por pedido del usuario, pero dejará de ser invocado.

---

## Beneficios de este diseño
1. **Máximo reaprovechamiento:** Se usa el 100% del motor de iteración y validación de CSV original.
2. **Cero impacto en facturas:** La rama ELSE contiene línea por línea exactamente lo que hoy se ejecuta para ingresoFactura.
3. **Persistencia limpia:** El sistema adopta una bandera de configuración robusta sin hardcodeo y manejable por el End User desde UI.

## Verificación a realizar
*   Cambiar a modo FACTURA desde la UI y ver que la vista gráfica se refresque bien.
*   Lanzar procesamiento y ver que fluye la factura original.
*   Cambiar a PEDIDO, lanzar procesamiento y comprobar que detona los métodos "RXN".
