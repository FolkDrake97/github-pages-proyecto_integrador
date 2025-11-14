<?php

define('ROOT_PATH', __DIR__);
require_once ROOT_PATH . '/includes/helpers.php';

iniciarSesionSegura();

// Si ya está autenticado, redirigir al dashboard
if (estaAutenticado()) {
    $rol = obtenerRol();
    header("Location: views/dashboard_$rol.php");
    exit;
}

// Si no está autenticado, redirigir al login
header('Location: login.php');
exit;
?>