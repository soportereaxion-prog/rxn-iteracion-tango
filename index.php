<?php
require_once __DIR__ . '/auth/guard.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú Principal - RXN Lady API</title>
    <!-- Prevención FOUC Tema -->
    <script>
        if (localStorage.getItem('rxn-theme') === 'light') {
            document.documentElement.setAttribute('data-theme', 'light');
        }
    </script>
    <link href="rxn-ui.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

<div class="rxn-container rxn-mt-5">
    <div class="rxn-flex-between rxn-mb-4">
        <div style="display: flex; align-items: center;">
            <img src="logo.png" alt="Re@xion Logo" class="rxn-logo-inline" onerror="this.style.display='none'">
            <div>
                <h1 style="margin-top: 0; margin-bottom: 5px;">Re@xion - Lady API</h1>
                <p class="rxn-text-muted" style="font-size: 1.25rem; margin-top: 0; margin-bottom: 0;">Panel de Control Principal</p>
            </div>
        </div>
        <div style="display: flex; gap: 10px; align-items: stretch;">
            <!-- Theme Toggle -->
            <button id="rxn-theme-btn" class="rxn-btn rxn-btn-secondary" title="Alternar Tema" style="padding: 6px 12px;">
                <i class="bi bi-moon-stars"></i>
            </button>
            <a href="auth/logout.php" class="rxn-btn" style="background-color: transparent; color: #dc3545; border: 1px solid #dc3545; padding: 6px 12px; text-decoration: none; display: flex; align-items: center; gap: 5px;">
                <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
            </a>
        </div>
    </div>

    <div class="rxn-grid">
        <!-- 1. Módulo CSV -->
        <div class="rxn-card">
            <div class="rxn-card-body rxn-text-center">
                <i class="bi bi-file-earmark-spreadsheet rxn-card-icon"></i>
                <h3 class="rxn-card-title">Procesar CSV</h3>
                <p class="rxn-card-text">Lectura, procesamiento y carga de comprobantes masivos hacia el sistema Tango.</p>
            </div>
            <div class="rxn-card-footer">
                <a href="csv/index.php" class="rxn-btn rxn-btn-primary rxn-btn-block">
                    Ir a Procesar CSV <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- 2. Módulo Copia Facturador -->
        <div class="rxn-card">
            <div class="rxn-card-body rxn-text-center">
                <i class="bi bi-files rxn-card-icon"></i>
                <h3 class="rxn-card-title">Copia Facturador</h3>
                <p class="rxn-card-text">Gestión de duplicado y asignación de facturación según perfiles definidos.</p>
            </div>
            <div class="rxn-card-footer">
                <a href="copiaFacturas/index.php" class="rxn-btn rxn-btn-secondary rxn-btn-block">
                    Ir a Copias <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- 3. Módulo Gestión de Usuarios -->
        <div class="rxn-card">
            <div class="rxn-card-body rxn-text-center">
                <i class="bi bi-people rxn-card-icon"></i>
                <h3 class="rxn-card-title">Gestión de Usuarios</h3>
                <p class="rxn-card-text">Alta, baja y modificación de accesos al sistema. Control de roles.</p>
            </div>
            <div class="rxn-card-footer">
                <a href="usuarios/index.php" class="rxn-btn rxn-btn-secondary rxn-btn-block">
                    Ir a Usuarios <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- 4. Módulo Configuración -->
        <div class="rxn-card">
            <div class="rxn-card-body rxn-text-center">
                <i class="bi bi-gear-fill rxn-card-icon"></i>
                <h3 class="rxn-card-title">Configuración</h3>
                <p class="rxn-card-text">Ajustes generales, rutas de XML locales, tokens TiendaNube y facturadores preferidos.</p>
            </div>
            <div class="rxn-card-footer">
                <a href="configuraciones/index.php" class="rxn-btn rxn-btn-secondary rxn-btn-block">
                    Ir a Configuración <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- 5. Módulo Limpieza -->
        <div class="rxn-card">
            <div class="rxn-card-body rxn-text-center">
                <i class="bi bi-trash rxn-card-icon"></i>
                <h3 class="rxn-card-title">Limpieza de Archivos</h3>
                <p class="rxn-card-text">Herramienta para purgado y borrado de registros pendientes o estancados.</p>
            </div>
            <div class="rxn-card-footer">
                <a href="limpiarArchivos/index.php" class="rxn-btn rxn-btn-secondary rxn-btn-block">
                    Ir a Limpieza <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
        
    </div>

    <div class="rxn-text-center rxn-text-muted" style="margin-top: 50px; margin-bottom: 20px; font-size: 14px;">
        <small>&copy; 2026 Re@xion - Desarrollo de soluciones para Tango</small>
    </div>

</div>

<script>
    const themeBtn = document.getElementById('rxn-theme-btn');
    const themeIcon = themeBtn.querySelector('i');
    
    // Sincronizar icono inicial
    if (document.documentElement.getAttribute('data-theme') === 'light') {
        themeIcon.classList.replace('bi-moon-stars', 'bi-sun');
    }

    themeBtn.addEventListener('click', () => {
        let theme = document.documentElement.getAttribute('data-theme');
        if (theme === 'light') {
            document.documentElement.removeAttribute('data-theme');
            localStorage.setItem('rxn-theme', 'dark');
            themeIcon.classList.replace('bi-sun', 'bi-moon-stars');
        } else {
            document.documentElement.setAttribute('data-theme', 'light');
            localStorage.setItem('rxn-theme', 'light');
            themeIcon.classList.replace('bi-moon-stars', 'bi-sun');
        }
    });
</script>
</body>
</html>
