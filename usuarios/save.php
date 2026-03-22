<?php
require_once __DIR__ . '/../auth/guard.php';
require_once __DIR__ . '/../ConectarBase.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$id = $_POST['id'] ?? '';
$usuario = trim($_POST['usuario'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$password = $_POST['password'] ?? '';
$activo = isset($_POST['activo']) && $_POST['activo'] == '1' ? 1 : 0;

if (empty($usuario) || empty($nombre)) {
    header("Location: form.php" . ($id ? "?id=$id&error=1" : "?error=1"));
    exit;
}

try {
    $db = Conectar_SQL_static::conexion_origen();

    if ($id) {
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE RXN_USUARIOS SET NOMBRE = ?, PASSWORD_HASH = ?, ACTIVO = ? WHERE ID_USUARIO = ?");
            $stmt->execute([$nombre, $hash, $activo, $id]);
            
            // Si cambia la clave o desactiva, matamos token preventivamente
            $update = $db->prepare("UPDATE RXN_USUARIOS SET TOKEN_PERSISTENCIA = NULL WHERE ID_USUARIO = ?");
            $update->execute([$id]);

        } else {
            $stmt = $db->prepare("UPDATE RXN_USUARIOS SET NOMBRE = ?, ACTIVO = ? WHERE ID_USUARIO = ?");
            $stmt->execute([$nombre, $activo, $id]);
            
            if (!$activo) {
                // Si la desactiva obligamos a que expulse el eventual secuestro 
                $update = $db->prepare("UPDATE RXN_USUARIOS SET TOKEN_PERSISTENCIA = NULL WHERE ID_USUARIO = ?");
                $update->execute([$id]);
            }
        }
    } else {
        if (empty($password)) {
            header("Location: form.php?error=1");
            exit;
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO RXN_USUARIOS (USUARIO, NOMBRE, PASSWORD_HASH, ACTIVO) VALUES (?, ?, ?, ?)");
        $stmt->execute([$usuario, $nombre, $hash, $activo]);
    }

    header("Location: index.php");
    exit;

} catch (Exception $e) {
    error_log("Error guardando ABM user: " . $e->getMessage());
    header("Location: form.php" . ($id ? "?id=$id&error=1" : "?error=1"));
    exit;
}
