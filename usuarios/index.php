<?php
require_once __DIR__ . '/../auth/guard.php';
require_once __DIR__ . '/../ConectarBase.php';

try {
    $db = Conectar_SQL_static::conexion_origen();
    $stmt = $db->query("SELECT ID_USUARIO, USUARIO, NOMBRE, ACTIVO, ULTIMO_LOGIN FROM RXN_USUARIOS ORDER BY NOMBRE ASC");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error obteniendo usuarios: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios - RXN Lady API</title>
    <link href="../rxn-ui.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body style="background-color: #f8f9fa;">
<div class="rxn-container">
    <div class="rxn-flex-between" style="margin-bottom: 20px;">
        <h2 style="margin: 0; font-family: Arial, sans-serif;">Gestión de Usuarios</h2>
        <div class="rxn-flex-center">
            <a href="../index.php" class="rxn-btn rxn-btn-secondary" style="text-decoration:none;">Volver al Menú</a>
            <a href="form.php" class="rxn-btn rxn-btn-primary" style="text-decoration:none;">Nuevo Usuario</a>
        </div>
    </div>
    
    <div class="rxn-card">
        <div class="rxn-card-body">
            <div style="overflow-x: auto;">
            <table class="rxn-table">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th>Último Login</th>
                        <th style="text-align:center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['USUARIO']) ?></td>
                        <td><?= htmlspecialchars($u['NOMBRE']) ?></td>
                        <td>
                            <?php if ($u['ACTIVO']): ?>
                                <span class="rxn-badge rxn-badge-success">Activo</span>
                            <?php else: ?>
                                <span class="rxn-badge rxn-badge-danger">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $u['ULTIMO_LOGIN'] ? date('d/m/Y H:i', strtotime($u['ULTIMO_LOGIN'])) : '-' ?></td>
                        <td style="text-align:center;">
                            <a href="form.php?id=<?= $u['ID_USUARIO'] ?>" class="rxn-btn rxn-btn-primary" title="Editar" style="padding: 4px 8px; text-decoration:none;">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($usuarios)): ?>
                    <tr><td colspan="5" style="text-align:center; color:#6c757d; padding:20px;">No hay usuarios dados de alta</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div> <!-- End overflow-x -->
        </div>
    </div>
</div>
</body>
</html>
