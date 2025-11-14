<?php
// Bootstrap para includes automáticos
define('ROOT_DIR', __DIR__);

// Incluir configuraciones
require_once ROOT_DIR . '/config/config.php';
require_once ROOT_DIR . '/config/database.php';

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>