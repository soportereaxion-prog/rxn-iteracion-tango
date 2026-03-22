<?php
// /auth/logout.php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../ConectarBase.php';

if (isset($_SESSION['id_usuario'])) {
    try {
        $db = Conectar_SQL_static::conexion_origen();
        $stmt = $db->prepare("UPDATE RXN_USUARIOS SET TOKEN_PERSISTENCIA = NULL WHERE ID_USUARIO = ?");
        $stmt->execute([$_SESSION['id_usuario']]);
    } catch (Exception $e) {
        error_log("Error en logout: " . $e->getMessage());
    }
}

// Borrar cookie nativa rescribiéndola al pasado
$cookie_options = [
    'expires' => time() - 3600,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'
];
setcookie('rxn_remember', '', $cookie_options);

session_unset();
session_destroy();

header("Location: login.php?logout=1");
exit;
