# DOCUMENTO DE DISEÑO — SISTEMA DE LOGIN Y SESIÓN PERSISTENTE

## 1. Diagnóstico breve
El proyecto RXN Lady API opera correctamente como middleware de integración entre archivos CSV y el ERP Tango Gestión. Actualmente, la interfaz gráfica administrativa (UI) carece de barreras de acceso, lo cual expone a cualquier usuario de la red local o puerto Docker a disparar reprocesos, procesar facturas o alterar la configuración del directorio. Se requiere un cerrojo de seguridad básico, focalizado en pantallas humanas, absolutamente respetuoso de las automatizaciones legacy y capaz de sobrevivir a la volatilidad de los contenedores Docker mediante persistencia prolongada.

## 2. Suposiciones detectadas
- **División UI / Machine:** Existen scripts como `csv/index.php` (UI Humana) y otros como `ctrl6_ingreso_cliente.php` o inyecciones crudas a `csv/modelo.php` que son de uso machine-to-machine, técnico o crons.
- **Transitoriedad de Docker:** El `garbage collector` (GC) de sesiones nativo de PHP limpiará `$_SESSION` de forma impredecible en los volúmenes no montados o al reiniciar el contenedor, haciendo obligatoria una estrategia de cookies apoyada en base de datos para la persistencia de largo plazo.
- **Acceso a Datos:** Ya se cuenta con la clase o script `ConectarDinamico.php` u homólogos para instanciar PDO. No se requerirán abstracciones complejas nuevas, se reutilizará la conexión nativa existente, al igual que los estilos visuales nativos (Bootstrap).

## 3. Diseño propuesto
Un esquema de autenticación clásico basado en:
1. Una pantalla de login simple (PHP puro + Bootstrap UI actual).
2. Un protector de rutas (`auth/guard.php`) que actuará de barrera al principio de cada UI humana.
3. Un mecanismo de "Remember Me" silencioso y obligatorio, respaldado por un token criptográfico validado contra BD, garantizando la persistencia operativa de la sesión ante reinicios de contenedor.
4. Un minúsculo modulo ABM integrado visualmente para listar, dar de alta y cambiar claves de la plantilla de usuarios sin roles complejos.
5. Un mecanismo de auto-deploy para el primer administrador (`crear_primer_usuario.php`).

## 4. Estructura final de archivos
```text
/auth/
  ├── login.php
  ├── login_process.php
  ├── guard.php
  ├── logout.php
  └── crear_primer_usuario.php

/usuarios/
  ├── index.php    (Listado ABM)
  ├── form.php     (Pantalla Alta/Edición)
  └── save.php     (Procesador de guardado)
```

## 5. Archivos nuevos a crear
Todos los enunciados en el bloque de estructura anterior. Son scripts aislados que no interfieren transversalmente con la carpeta `csv` o el core del facturador.

## 6. Archivos existentes a modificar
Requieren únicamente la inyección de `require_once __DIR__ . '/../auth/guard.php';` en la **línea 1** (antes del renderizado HTML):
- `index.php` (Pantalla global del menú)
- `csv/index.php` (Procesador)
- `csv/index_reprocesos.php`
- `csv/index_rechazar_pendientes.php`
- `limpiarArchivos/index.php`
- `copiaFacturas/index.php`
- `configuraciones/index.php`

**Adición menor:** Incorporar un botón "Usuarios" y "Cerrar sesión" en la grilla visual de `index.php`.

## 7. Rutas humanas a proteger
- Toda interfaz gráfica que dibuje HTML y contenga botones operativos que exijan supervisión manual. Específicamente, los `index.php` de los módulos listados en el Punto 6.

## 8. Rutas o procesos que no deben tocarse
Bajo **ningún concepto** requerirán autenticación de interfaz humana:
- `csv/modelo.php` (Procesador lógico core)
- `ConectarDinamico.php` y `Conectar*.php`
- Webhooks o endpoints receptivos (por ejemplo si Tango emite payloads de respuesta hacia la API de forma desatendida).
- Scripts técnicos heredados (ej: `ctrl6_ingreso_cliente.php`, `ctrl6_leo_clientes.php`).
- Procesos batch, pruebas unitarias automatizadas o llamadas machine-to-machine.

## 9. Accesos públicos / whitelist
Deberán ser de libre acceso (sin redirección de `guard.php`):
- `/auth/login.php`
- `/auth/login_process.php`
- `/auth/crear_primer_usuario.php` *(Posee auto-bloqueo lógico interno, no requiere barrera de ruteo)*.
- Recursos estáticos: Hojas de estilo `.css`, íconos `.ico` e imágenes (`logo-reaxion-v3.png`).

## 10. SQL completo propuesto para RXN_USUARIOS
```sql
CREATE TABLE RXN_USUARIOS (
    ID_USUARIO INT IDENTITY(1,1) PRIMARY KEY,
    USUARIO VARCHAR(50) NOT NULL UNIQUE,
    NOMBRE VARCHAR(100) NOT NULL,
    PASSWORD_HASH VARCHAR(255) NOT NULL,
    ACTIVO BIT DEFAULT 1,
    FECHA_ALTA DATETIME DEFAULT CURRENT_TIMESTAMP,
    ULTIMO_LOGIN DATETIME NULL,
    TOKEN_PERSISTENCIA VARCHAR(128) NULL
);
```
*Justificación:* `TOKEN_PERSISTENCIA` permite almacenar un hash criptográfico para reconectar al usuario automáticamente (recuperando su sesión nativa caída) y revocar esta confianza explícitamente durante el *logout manual*.

## 11. Estrategia de persistencia operativa de sesión
1. En el Login (`login_process.php`), si el usuario y contraseña (`password_verify()`) coinciden, se regenera el ID de sesión PHP.
2. Se inyectan `$_SESSION['id_usuario']`, `$_SESSION['usuario']` y `$_SESSION['nombre']`. No se usan booleanos vacíos como flag único de acceso.
3. Al mismo tiempo, se genera un string aleatorio (`bin2hex(random_bytes(32))`), se almacena su formato hasheado (`hash('sha256', $token)`) en el campo `TOKEN_PERSISTENCIA` de la base de datos, y se envía en texto plano como cookie segura `rxn_remember` al navegador con vencimiento a **30 días**.

## 12. Flujo completo de autenticación
- **Navegación normal:** El script `/auth/guard.php` evalúa `isset($_SESSION['id_usuario'])`. Si existe, deja seguir inmediatamente (sin latencia de BD).
- **Pérdida de sesión nativa (Restart de Docker o limpieza OS):** `$_SESSION` viene vacía. `guard.php` atrapa esto y evalúa la presencia de la cookie `$_COOKIE['rxn_remember']`. Si existe, consulta `SELECT ID_USUARIO... WHERE TOKEN_PERSISTENCIA = hash_cookie`. Si coincide, restaura íntegramente `$_SESSION` del usuario sin pedir pantalla de login manual, genera un nuevo token rotativo, y actualiza `ULTIMO_LOGIN`.
- **Logout:** Un click hacia `/auth/logout.php`. Ese script hace `UPDATE RXN_USUARIOS SET TOKEN_PERSISTENCIA = NULL WHERE ID_USUARIO = ?`, borra la cookie expirándola en el navegador (`time() - 3600`), y aplica `session_destroy()`. El cierre es hermético y no regenerable.

## 13. Diseño del ABM de usuarios
Ubicado en la carpeta reservada `/usuarios/`.
1. **Listado (`index.php`):** Grilla HTML idéntica al framework actual con los campos `USUARIO`, `NOMBRE`, `ACTIVO`, `ULTIMO_LOGIN` y botones de "Editar" / "Nuevo".
2. **Formulario (`form.php`):** Pantalla simple para Carga de Nuevo Usuario o Edición. Permite asignar Password (únicamente procesada tras bambalinas hacia `PASSWORD_HASH`) o alternar remotamente el flag `ACTIVO`.
3. **Guardado (`save.php`):** Script receptor del `POST` pre-protegido por `guard.php`. Ejecuta INSERT o UPDATE validando colisiones de usuarios para evitar duplicidad o fallos SQL.

## 14. Estrategia de creación del primer usuario
Ubicado en `/auth/crear_primer_usuario.php`.
- **Comportamiento:**
  1. Conecta nativamente a la Base de Datos reutilizando `ConectarDinamico.php`.
  2. Ejecuta: `SELECT COUNT(*) AS total FROM RXN_USUARIOS`.
  3. Si `total > 0`, se inhibe y finaliza automáticamente mostrando el mensaje: _"El sistema ya se encuentra inicializado. Contacte a un administrador vigente para altas nuevas."_ (`exit`).
  4. Si `total == 0`, muestra un formulario HTML muy rudimentario.
  5. Al recibir un `POST` válido, inserta con `password_hash()` la contraseña del primer sysadmin.
  6. Finaliza inyectando las credenciales fundacionales a la Sesión y redirige a la raíz del ruteo `/index.php`.
- **Mitigación del Riesgo:** Este enfoque descarta fallos por vulnerabilidad expuesta; anula la dependencia a que el instalador inyecte scripts manuales SQL y elimina el pánico de "olvidar borrar un script de setup inicial".

## 15. Riesgos técnicos y mitigaciones
- **Riesgo:** Un cronjob del servidor intenta ejecutar `csv/modelo.php` pero rebota o es bloqueado.
  **Mitigación:** Ni `<modelo>` ni los conectores SQL ni el motor CURL alojarán requerimientos de autenticación (`guard.php`). El firewall funciona meramente en las terminales operativas visuales (los `/index.php` que emiten las botoneras).
- **Riesgo:** Claves operativas legibles en la BD en caso de un leak.
  **Mitigación:** Adopción excluyente de la función de encriptación one-way nativa de PHP `password_hash()`. Un SQL injection jamás revelará la contraseña cruda.
- **Riesgo:** Robo del token de largo plazo (Token Theft).
  **Mitigación:** El diseño de la rotación prevé que el `TOKEN_PERSISTENCIA` almacenado en Base de Datos quedará nulo (`NULL`) irremediablemente ante el primer uso legítimo de `/auth/logout.php`.

## 16. Plan de implementación paso a paso
1. Revalidar y ejecutar el script `CREATE TABLE RXN_USUARIOS` en el servidor SQL actualizando el esquema del proyecto.
2. Desarrollar el script llave de bootstrap `auth/crear_primer_usuario.php`.
3. Desarrollar las interfaces `auth/login.php` y su motor seguro `auth/login_process.php`.
4. Codificar la barrera de vigilancia e inicializador persistente en `auth/guard.php`.
5. Proteger inyectando un `require_once __DIR__ . '/../auth/guard.php';` ciego en los encabezados principales de las 7 vistas actuales HTML requeridas.
6. Armar el submódulo `usuarios/` (Index, form, save).
7. Simular un cierre forzado del contenedor o vaciado de `\tmp` para someter a QA la recuperación automática del motor *Remember Me*.
