<?php
// /auth/guard.php
require_once __DIR__ . '/session.php';

// Si la sesión nativa sobrevivió, pasa directo.
if (isset($_SESSION['id_usuario']) && !empty($_SESSION['id_usuario'])) {
    return;
}

// Evitamos un bucle infinito si login.php incluye a guard para interceptar su propia restauración.
$is_login_page = basename($_SERVER['SCRIPT_FILENAME']) === 'login.php';

// Si se depuró la sesión de Docker nativa pero el usuario tiene Remembering Token (30 days)
if (isset($_COOKIE['rxn_remember'])) {
    require_once __DIR__ . '/../ConectarBase.php';
    $token_str = $_COOKIE['rxn_remember'];
    $token_hash = hash('sha256', $token_str);

    try {
        $db = Conectar_SQL_static::conexion_origen();
        // Buscamos colision del hash persistente
        $stmt = $db->prepare("SELECT ID_USUARIO, USUARIO, NOMBRE, ACTIVO FROM RXN_USUARIOS WHERE TOKEN_PERSISTENCIA = ?");
        $stmt->execute([$token_hash]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['ACTIVO'] == 1) {
            session_regenerate_id(true);
            $_SESSION['id_usuario'] = $user['ID_USUARIO'];
            $_SESSION['usuario'] = $user['USUARIO'];
            $_SESSION['nombre'] = $user['NOMBRE'];

            // Rotacion de Token por seguridad contra secuestros temporales
            $nuevo_token_str = bin2hex(random_bytes(32));
            $nuevo_token_hash = hash('sha256', $nuevo_token_str);
            
            $update = $db->prepare("UPDATE RXN_USUARIOS SET TOKEN_PERSISTENCIA = ?, ULTIMO_LOGIN = GETDATE() WHERE ID_USUARIO = ?");
            $update->execute([$nuevo_token_hash, $user['ID_USUARIO']]);
            
            $cookie_options = [
                'expires' => time() + (86400 * 30),
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'
            ];
            setcookie('rxn_remember', $nuevo_token_str, $cookie_options);

            return; // Sesión reconstruida y activa
        }
    } catch (Exception $e) {
        error_log("Error guard restore: " . $e->getMessage());
    }
}

if ($is_login_page) {
    return; // Dejamos que el flujo caiga al formulario HTML nativo
}

// Resolver ruta calculando si el script ejecutado está en la raíz de la app o en un subdirectorio
$script_dir = dirname(realpath($_SERVER['SCRIPT_FILENAME']));
$app_root = dirname(__DIR__); // __DIR__ es auth/, por lo que dirname(__DIR__) es la raíz de la app
$login_path = ($script_dir === $app_root) ? "auth/login.php" : "../auth/login.php";
header("Location: " . $login_path);
exit;
