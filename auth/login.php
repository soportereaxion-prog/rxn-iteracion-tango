<?php
// /auth/login.php
require_once __DIR__ . '/session.php';

// Si no hay sesion nativa pero hay cookie, derivar el intento de regeneracion a guard
if (!isset($_SESSION['id_usuario']) && isset($_COOKIE['rxn_remember'])) {
    require_once __DIR__ . '/guard.php';
}

if (isset($_SESSION['id_usuario']) && !empty($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - RXN Lady API</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../icon1.ico" />
    <style>
        body { 
            background: #f0f2f5; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            height: 100vh;
            color: #212529;
            transition: background 0.3s ease, color 0.3s ease;
        }
        @media (prefers-color-scheme: dark) {
            body { background-color: #121212; color: #f1f1f1; }
            .login-card { background-color: #1e1e1e !important; }
        }
        .login-card { 
            width: 100%; 
            max-width: 400px; 
            padding: 2.5rem; 
            border-radius: 1rem; 
            box-shadow: 0 10px 20px rgba(0,0,0,0.1); 
            border: none; 
            background-color: white;
        }
        .login-logo { max-width: 250px; margin-bottom: 2rem; }
    </style>
</head>
<body>
<div class="card login-card text-center">
    <img src="../logo-reaxion-v3.png" alt="Logo Reaxion" class="login-logo mx-auto d-block">
    <h4 class="mb-4">Ingreso al Sistema</h4>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger text-start" role="alert">Usuario o contraseña incorrectos, o acceso inactivo.</div>
    <?php endif; ?>
    <?php if (isset($_GET['logout'])): ?>
        <div class="alert alert-success text-start" role="alert">Sesión finalizada correctamente.</div>
    <?php endif; ?>
    <form action="login_process.php" method="POST">
        <div class="mb-3 text-start">
            <label class="form-label text-muted">Usuario</label>
            <input type="text" name="usuario" class="form-control form-control-lg" required autofocus>
        </div>
        <div class="mb-4 text-start">
            <label class="form-label text-muted">Contraseña</label>
            <input type="password" name="password" class="form-control form-control-lg" required>
        </div>
        <button type="submit" class="btn btn-primary btn-lg w-100">Iniciar Sesión</button>
    </form>
</div>
</body>
</html>
