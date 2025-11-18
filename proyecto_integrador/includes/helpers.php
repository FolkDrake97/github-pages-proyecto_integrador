<?php
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

require_once ROOT_PATH . '/config/database.php';

function iniciarSesionSegura() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function estaAutenticado() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function obtenerUsuarioId() {
    return $_SESSION['user_id'] ?? null;
}

function obtenerRol() {
    return $_SESSION['user_role'] ?? null;
}

function obtenerNombreUsuario() {
    return $_SESSION['user_name'] ?? 'Usuario';
}

function tieneRol($roles) {
    if (is_string($roles)) {
        $roles = [$roles];
    }
    return in_array(obtenerRol(), $roles);
}

function requerirAutenticacion() {
    if (!estaAutenticado()) {
        redirigir('login.php');
    }
}

function requerirRol($roles) {
    requerirAutenticacion();
    if (is_string($roles)) {
        $roles = [$roles];
    }
    if (!in_array(obtenerRol(), $roles)) {
        http_response_code(403);
        echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Acceso Denegado</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'></head>
        <body class='bg-light'><div class='container'><div class='row justify-content-center align-items-center min-vh-100'>
        <div class='col-md-6'><div class='card border-danger'><div class='card-header bg-danger text-white'>
        <h4 class='mb-0'>Acceso Denegado</h4></div><div class='card-body text-center'>
        <p class='lead'>No tienes permisos para acceder a esta página.</p>
        <a href='" . BASE_URL . "/views/dashboard_" . obtenerRol() . ".php' class='btn btn-primary'>Volver</a>
        </div></div></div></div></div></body></html>";
        exit;
    }
}

function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validarPassword($password) {
    return strlen($password) >= PASSWORD_MIN_LENGTH;
}

function sanitizar($data) {
    if (is_array($data)) {
        return array_map('sanitizar', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function redirigir($url) {
    if (!preg_match('/^https?:\/\//', $url)) {
        $url = BASE_URL . '/' . ltrim($url, '/');
    }
    header('Location: ' . $url);
    exit;
}

function formatearFecha($fecha, $formato = 'd/m/Y H:i') {
    if (empty($fecha)) return '-';
    $timestamp = is_numeric($fecha) ? $fecha : strtotime($fecha);
    return date($formato, $timestamp);
}

function diasRestantes($fechaLimite) {
    $ahora = time();
    $limite = is_numeric($fechaLimite) ? $fechaLimite : strtotime($fechaLimite);
    return floor(($limite - $ahora) / 86400);
}

function claseCalificacion($calificacion) {
    if ($calificacion >= 90) return 'text-success fw-bold';
    if ($calificacion >= 80) return 'text-info fw-bold';
    if ($calificacion >= 70) return 'text-warning fw-bold';
    return 'text-danger fw-bold';
}

function respuestaJSON($exito, $datos = null, $mensaje = '', $codigoHTTP = 200) {
    http_response_code($codigoHTTP);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['exito' => $exito, 'datos' => $datos, 'mensaje' => $mensaje], JSON_UNESCAPED_UNICODE);
    exit;
}