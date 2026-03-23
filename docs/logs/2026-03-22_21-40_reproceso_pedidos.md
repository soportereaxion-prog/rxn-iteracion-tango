# Reparación del Ciclo de Vida de Reprocesos (Pedidos) y Ampliación del Menú

## Contexto
Durante la Fase 15 de auditoría al sistema de reprocesos, se identificó que la pantalla principal carecía de los accesos directos operacionales correspondientes a `Módulo de Reproceso` y `Descartar Pendientes (Rechazos)`. Además, se auditó el flujo lógico en `csv/index_reprocesos.php` para investigar por qué funcionaba correctamente con la inyección de **Facturas** a Tango, pero los **Pedidos** reprocesados en estado fallido permanecían un listado infinito (Loop Fantasma de Reintento Visual).

## Problema Detectado (RCA)
1. **Faltante de UI**: Accesos huérfanos sin linkear en `index.php`.
2. **Loop de Pedidos Fail (RXN_API_CTRL):**
   - Cuando un pedido falla en la API, se invoca a `ingresoMensajesApi` y se marca `GRABO = 0` exitosamente.
   - Estos archivos se listaban gracias a que el motor lee "Todos los archivos con `GRABO = 0`"
   - Pero al dar "Procesar", el código asimilaba la carga correctamente, emitía el success hacia `RXN_API_CTRL` (`GRABO = 1`) y... no invalidaba los registros históricos de falla. Al existir registros viejos con `GRABO = 0`, el motor volvía a listar al Pedido como "Pendiente" infinitamente.
   - En **Facturación** esto no pasaba porque la vieja lógica mandaba a invocar explícitamente a `$this->actualizoReproceso()` tras el éxito, matando a todos los `0` por `1` en ese ID.

## Archivos Afectados
- `index.php` (Frontend)
- `csv/modelo.php` (Backend Core)
- `/Ayudas/ Menu y Buscador` (Revisados, los manuales existentes eran aptos).

## Implementación Ejecutada
1. **Frontend**: Se inyectaron dos `.rxn-card` en `index.php` replicando el framework de iconos `bootstrap-icons` con links a `csv/index_reprocesos.php` y `csv/index_rechazar_pendientes.php` respetando estética oscura.
2. **Backend**: Se aplicó una inyección quirúrgica en `modelo.php` `(Línea 852 - Método procesoPedidos())` que hereda la limpieza del scope histórico:
   ```php
   // --- FIX: Cierre del ciclo de reprocesamiento en Pedidos ---
   $this->actualizoReproceso($pedi_enc['N_COMP'], $pedi_enc['COD_CLIENT'], $pedi_enc['NOMBRE_ARCHIVO']);
   ```
3. El Descartador de Pendientes (`index_rechazar_pendientes.php`) opera sobre `GVA12DE` modificando los flags `P` a `R` para autorizaciones AFIP (CAE). Como los pedidos no requieren CAE, se determinó mediante análisis estático que **no requiere alteraciones**. 

## Impacto
Cierre definitivo del lazo de reprocesos de Pedidos. Ahora, un ticket de venta que cae a reproceso y tiene éxito, desaparecerá de la cola visual permitiendo un dashboard de monitoreo fidedigno.

## Notas Adicionales
Queda disponible para revisión en Github por el equipo de control de calidad bajo el commit `"fix: reproceso de pedidos y accesos faltantes en panel"`.
