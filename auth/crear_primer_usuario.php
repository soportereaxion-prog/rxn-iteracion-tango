<?php
// /auth/crear_primer_usuario.php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../ConectarBase.php';

try {
    $db = Conectar_SQL_static::conexion_origen();

    // Verificar si ya existen usuarios
    $stmt = $db->query("SELECT COUNT(*) as total FROM RXN_USUARIOS");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row['total'] > 0) {
        die("Error: El sistema ya se encuentra inicializado con usuarios. Contacte a un administrador vigente para altas nuevas.");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $usuario = trim($_POST['usuario'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!empty($usuario) && !empty($nombre) && !empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO RXN_USUARIOS (USUARIO, NOMBRE, PASSWORD_HASH, ACTIVO) VALUES (?, ?, ?, 1)");
            $stmt->execute([$usuario, $nombre, $hash]);

            $id_usuario = $db->lastInsertId(); // Recuperar ID real provisto por IDENTITY
            if (!$id_usuario) {
                // Failsafe por si el driver PDO/SQLServer omite lastInsertId bajo ciertas condiciones
                $stmt2 = $db->prepare("SELECT ID_USUARIO FROM RXN_USUARIOS WHERE USUARIO = ?");
                $stmt2->execute([$usuario]);
                $id_usuario = $stmt2->fetchColumn();
            }

            // Iniciar sesión y asignar vars reales
            session_regenerate_id(true);
            $_SESSION['id_usuario'] = $id_usuario;
            $_SESSION['usuario'] = $usuario;
            $_SESSION['nombre'] = $nombre;

            // Generar token persistencia inyectivo automático 
            // Esto asimila el comportamiento seguro del bootstrap idéntico al login genérico
            $token_str = bin2hex(random_bytes(32));
            $token_hash = hash('sha256', $token_str);
            $update = $db->prepare("UPDATE RXN_USUARIOS SET TOKEN_PERSISTENCIA = ?, ULTIMO_LOGIN = GETDATE() WHERE ID_USUARIO = ?");
            $update->execute([$token_hash, $id_usuario]);
            
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
            $error = "Todos los campos son obligatorios.";
        }
    }
} catch (Exception $e) {
    die("Error crítico de base de datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Inicialización del Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Crear Primer Administrador</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                        <form method="post">
                            <div class="mb-3">
                                <label>Nombre Completo</label>
                                <input type="text" name="nombre" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Usuario</label>
                                <input type="text" name="usuario" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Contraseña</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Inicializar y Entrar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>