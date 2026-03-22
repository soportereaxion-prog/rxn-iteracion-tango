# Tercera Etapa — Limpieza de Código Muerto y Trazabilidad

## Descripción

El código presenta una deuda técnica considerable en forma de bloques comentados de debug y sentencias `return` que bloquean la ejecución de cierres de cursor `closeCursor()`. Esta etapa aplica una limpieza de bajo riesgo sin afectar la lógica de facturación ni acumuladores.

**Archivos modificados:** [csv/modelo.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php), [configuraciones/modelo.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/configuraciones/modelo.php)

---

## Hallazgos clasificados

### 1. Código muerto: `closeCursor()` post-return

En [configuraciones/modelo.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/configuraciones/modelo.php) (y unos pocos casos en [csv/modelo.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php)), hay métodos que extraen información de la base de datos usando `fetch()` y devuelven el valor directamente:
```php
    public function leoParametroBd($nombre_col)
    {
        $consulta = $this->db_sql->query("SELECT $nombre_col FROM RXN_PARAMETROS");
        $filas = $consulta->fetch(PDO::FETCH_ASSOC);
        
        return $this->param = $filas[$nombre_col];

        $consulta->closeCursor(); // ← INALCANZABLE
    }
```
Esto genera fugas de conexión a nivel driver porque la sentencia no finaliza limpiamente. **Se identificaron 21 casos**.

### 2. Notices de array por `fetch()` fallido

En el mismo patrón anterior, si la consulta no arroja resultados, `$filas` vale `false`. Hacer `return $filas[$nombre_col]` lanza un Notice por offset nulo/indefinido.

### 3. Líneas sucias de debug comentado

A lo largo del código de facturación (especialmente en [csv/modelo.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php)) hay decendas de líneas del tipo `//echo '<br>Estoy entrando a SNC';` o `//print_r($info);`. Restan legibilidad, distraen y dificultan el mantenimiento. **Se identificaron ~35 casos aislados que no aportan valor estructural**.

---

## Cambios propuestos

### Archivo: [configuraciones/modelo.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/configuraciones/modelo.php)

**Cambio 1: Refactor de métodos selectores (L120-L265)**
Se reescribirán 11 métodos que siguen el patrón de la [leoParametroBd](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php#603-614). 

Ejemplo (leoParametroBd):
```diff
-        return $this->param = $filas[$nombre_col];
-        $consulta->closeCursor();
+        $resultado = $filas[$nombre_col] ?? null;
+        $consulta->closeCursor();
+        return $this->param = $resultado;
```
Por qué es seguro: Retorna el mismo valor (o `null` limpio sin notice si falla). Libera el cursor correctamente.

### Archivo: [csv/modelo.php](file:///d:/RXNAPP/Docker/www/rxnLadyApi/src/rxnLadyApiLinuxDockerizada/csv/modelo.php)

**Cambio 2: Limpieza de comentarios de depuración**
Se removerán mediante expresiones regulares seguras las líneas que **únicamente** contienen logs comentados (por ejemplo, que comiencen por espacios seguidos de `//` y `echo` o `print_r`). No se tocarán comentarios explicativos (aquellos sin funciones de debug).

**Cambio 3: Refactor de `closeCursor()` inalcanzables**
Ajustar los contados `closeCursor()` ubicados erróneamente en `csv/modelo.php` (como en L3887-3932 para `maxIdGva14` y similares).

---

## Puntos NO tocados

| Punto | Razón |
|---|---|
| Acumuladores `art_total_*` | Se mantiene la instrucción estricta de **no alterar** el estado mutable durante el procesamiento ni su forma de reseteo. |
| SQL Queries (SELECT, INSERT, UPDATE) | No se modificarán excepto por el tratamiento limpio del resultado (`fetch` + coalesce). |
| `generoJsonCompleto()` | Se mantiene sin alteraciones, salvo limpieza de comentarios de echo/print si los hubiera adentro. |

## Verificación

- Operación normal: ejecutar el procesamiento del menú y constatar que el flujo cierra correctamente facturas y API.
- Revisor estático de código: verificar la inexistencia de advertencias "Unreachable code" en las IDEs para las líneas afectadas.

