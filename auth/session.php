<?php
// /auth/session.php

if (session_status() !== PHP_SESSION_ACTIVE) {
    // Forzar el Garbage Collector a 30 días si el entorno lo permite
    ini_set('session.gc_maxlifetime', 86400 * 30);
    
    // Parámetros de la cookie de sesión PHP
    session_set_cookie_params([
        'lifetime' => 86400 * 30,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'
    ]);
    
    session_start();
}
