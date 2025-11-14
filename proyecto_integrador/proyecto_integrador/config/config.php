<?php

// Incluir configuración de base de datos
require_once __DIR__ . '/database.php';

// Configuración de la aplicación
if (!defined('APP_NAME')) {
    define('APP_NAME', 'Plataforma Académica');
    define('APP_VERSION', '1.0.0');
    define('SITE_NAME', 'Plataforma Académica');
}

// Configuración de URL base - DEFINIR MANUALMENTE
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/programacion-web/proyecto_integrador');
}

// Configuración de sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración de contraseñas
if (!defined('PASSWORD_MIN_LENGTH')) {
    define('PASSWORD_MIN_LENGTH', 8);
}

// Timezone
date_default_timezone_set('America/Mexico_City');

// Error reporting (desactivar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);