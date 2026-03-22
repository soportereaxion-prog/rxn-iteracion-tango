<?php
require_once __DIR__ . '/../auth/guard.php';
require_once __DIR__ . '/../ConectarBase.php';

$id = $_GET['id'] ?? null;
$usuario = [
    'USUARIO' => '',
    'NOMBRE' => '',
    'ACTIVO' => 1
];

if ($id) {
    try {
        $db = Conectar_SQL_static::conexion_origen();
        $stmt = $db->prepare("SELECT * FROM RXN_USUARIOS WHERE ID_USUARIO = ?");
        $stmt->execute([$id]);
        $fetched = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($fetched) {
            $usuario = $fetched;
        } else {
            die("Usuario no encontrado.");
        }
    } catch (Exception $e) {
        die("Error DB: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $id ? 'Editar' : 'Nuevo' ?> Usuario - RXN Lady API</title>
    <link href="../rxn-ui.css" rel="stylesheet" type="text/css" />
</head>
<body style="background-color: #f8f9fa;">
<div class="rxn-container" style="max-width: 600px; margin-top: 40px;">
    <div class="rxn-card">
        <div class="rxn-card-header">
            <?= $id ? 'Editar Usuario' : 'Nuevo Usuario' ?>
        </div>
        <div class="rxn-card-body">
                    <?php if (isset($_GET['error'])): ?>
                        <div class="rxn-alert rxn-alert-danger">Error al guardar. Es posible que el nombre de usuario ya esté ocupado.</div>
                    <?php endif; ?>
                    <form action="save.php" method="POST" style="font-family: Arial, sans-serif;">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($id ?? '') ?>">
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-size: 14px; font-weight: 600; margin-bottom: 5px;">Usuario (Login)</label>
                            <input type="text" name="usuario" class="rxn-input" value="<?= htmlspecialchars($usuario['USUARIO']) ?>" required <?= $id ? 'readonly style="background-color: #e9ecef;"' : '' ?>>
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-size: 14px; font-weight: 600; margin-bottom: 5px;">Nombre Completo</label>
                            <input type="text" name="nombre" class="rxn-input" value="<?= htmlspecialchars($usuario['NOMBRE']) ?>" required>
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-size: 14px; font-weight: 600; margin-bottom: 5px;">Contraseña <?= $id ? "(dejar en blanco para no cambiar)" : "" ?></label>
                            <input type="password" name="password" class="rxn-input" <?= $id ? '' : 'required' ?>>
                        </div>
                        
                        <div style="margin-bottom: 25px;">
                            <label class="rxn-switch">
                                <input type="checkbox" name="activo" id="activoCheck" value="1" class="rxn-switch-checkbox" <?= $usuario['ACTIVO'] ? 'checked' : '' ?>>
                                <span class="rxn-switch-label">Usuario Activo en el Sistema</span>
                            </label>
                        </div>
                        
                        <div class="rxn-flex-between" style="margin-top: 15px;">
                            <a href="index.php" class="rxn-btn rxn-btn-secondary" style="text-decoration:none;">Volver al listado</a>
                            <button type="submit" class="rxn-btn rxn-btn-primary">Guardar DB</button>
                        </div>
                    </form>
                </div>
            </div>
</div>
</body>
</html>
