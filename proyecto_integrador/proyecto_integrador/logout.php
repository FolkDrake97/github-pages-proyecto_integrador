<?php
define('ROOT_PATH', __DIR__);
require_once ROOT_PATH . '/includes/helpers.php';

iniciarSesionSegura();

$_SESSION = [];

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

session_destroy();

header('Location: ' . BASE_URL . '/login.php');
exit;