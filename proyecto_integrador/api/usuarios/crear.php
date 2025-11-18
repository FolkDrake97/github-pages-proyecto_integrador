<?php

header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/conexion.php';

iniciarSesionSegura();

// Verificar autenticación y rol de administrador
if (!estaAutenticado() || !tieneRol('administrador')) {
    respuestaJSON(false, null, 'No autorizado', 403);
}

// Solo aceptar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respuestaJSON(false, null, 'Método no permitido', 405);
}

// Obtener datos del formulario
$nombre = sanitizar($_POST['nombre'] ?? '');
$apellido = sanitizar($_POST['apellido'] ?? '');
$email = sanitizar($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$rol = sanitizar($_POST['rol'] ?? 'estudiante');

// Validaciones
if (empty($nombre) || empty($apellido) || empty($email) || empty($password)) {
    respuestaJSON(false, null, 'Todos los campos son obligatorios', 400);
}

if (!validarEmail($email)) {
    respuestaJSON(false, null, 'El email no es válido', 400);
}

if (!validarPassword($password)) {
    respuestaJSON(false, null, 'La contraseña debe tener al menos ' . PASSWORD_MIN_LENGTH . ' caracteres', 400);
}

if (!in_array($rol, ['administrador', 'maestro', 'estudiante'])) {
    respuestaJSON(false, null, 'Rol no válido', 400);
}

try {
    $db = Conexion::getInstance()->getConexion();
    
    // Verificar si el email ya existe
    $stmt = $db->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        respuestaJSON(false, null, 'El email ya está registrado', 400);
    }
    
    // Crear usuario
    $passwordHash = hashPassword($password);
    $stmt = $db->prepare("
        INSERT INTO usuarios (nombre, apellido, email, password, rol) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    if ($stmt->execute([$nombre, $apellido, $email, $passwordHash, $rol])) {
        respuestaJSON(true, [
            'id_usuario' => $db->lastInsertId(),
            'email' => $email,
            'rol' => $rol
        ], 'Usuario creado exitosamente');
    } else {
        respuestaJSON(false, null, 'Error al crear el usuario', 500);
    }
    
} catch (PDOException $e) {
    error_log("Error al crear usuario: " . $e->getMessage());
    respuestaJSON(false, null, 'Error del servidor', 500);
}
?>