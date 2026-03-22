<?php
require_once __DIR__ . "/ConectarDinamico.php";

try {
    // Usamos la misma base de datos por defecto instanciada en el modelo
    $db = Conectar_SQL::conexion("TANGO_LADY");

    $sql = "IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='RXN_USUARIOS' and xtype='U')
            BEGIN
                CREATE TABLE RXN_USUARIOS (
                    ID_USUARIO INT IDENTITY(1,1) PRIMARY KEY,
                    USUARIO VARCHAR(50) NOT NULL UNIQUE,
                    NOMBRE VARCHAR(100) NOT NULL,
                    PASSWORD_HASH VARCHAR(255) NOT NULL,
                    ACTIVO BIT DEFAULT 1,
                    FECHA_ALTA DATETIME DEFAULT GETDATE(),
                    ULTIMO_LOGIN DATETIME NULL,
                    TOKEN_PERSISTENCIA VARCHAR(128) NULL
                )
            END";

    $db->exec($sql);
    echo "Tabla RXN_USUARIOS creada exitosamente.";

} catch (PDOException $e) {
    echo "Error PDO: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
