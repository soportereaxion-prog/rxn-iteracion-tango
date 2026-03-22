<?php
// /auth/login_process.php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../ConectarBase.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}

$usuario = trim($_POST['usuario'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($usuario) || empty($password)) {
    header("Location: login.php?error=1");
    exit;
}

try {
    $db = Conectar_SQL_static::conexion_origen();
    
    $stmt = $db->prepare("SELECT ID_USUARIO, USUARIO, NOMBRE, PASSWORD_HASH, ACTIVO FROM RXN_USUARIOS WHERE USUARIO = ?");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['ACTIVO'] == 1 && password_verify($password, $user['PASSWORD_HASH'])) {
        session_regenerate_id(true);
        
        $_SESSION['id_usuario'] = $user['ID_USUARIO'];
        $_SESSION['usuario'] = $user['USUARIO'];
        $_SESSION['nombre'] = $user['NOMBRE'];

        $token_str = bin2hex(random_bytes(32));
        $token_hash = hash('sha256', $token_str);
        
        $update = $db->prepare("UPDATE RXN_USUARIOS SET TOKEN_PERSISTENCIA = ?, ULTIMO_LOGIN = GETDATE() WHERE ID_USUARIO = ?");
        $update->execute([$token_hash, $user['ID_USUARIO']]);
        
        $cookie_options = [
            'expires' => time() + (86400 * 30),
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'
        ];
        setcookie('rxn_remember', $token_str, $cookie_options);

        header("Location: ../index.php");
        exit;
    } else {
        header("Location: login.php?error=1");
        exit;
    }

} catch (Exception $e) {
    error_log("Error login: " . $e->getMessage());
    header("Location: login.php?error=1");
    exit;
}
