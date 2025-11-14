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

// Solo aceptar método PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    respuestaJSON(false, null, 'Método no permitido', 405);
}

// Obtener datos JSON
$input = json_decode(file_get_contents('php://input'), true);

$idUsuario = $input['id_usuario'] ?? null;
$nombre = sanitizar($input['nombre'] ?? '');
$apellido = sanitizar($input['apellido'] ?? '');
$email = sanitizar($input['email'] ?? '');
$rol = sanitizar($input['rol'] ?? '');
$activo = isset($input['activo']) ? (int)$input['activo'] : 1;

// Validaciones
if (empty($idUsuario) || empty($nombre) || empty($apellido) || empty($email)) {
    respuestaJSON(false, null, 'Todos los campos son obligatorios', 400);
}

if (!validarEmail($email)) {
    respuestaJSON(false, null, 'El email no es válido', 400);
}

if (!in_array($rol, ['administrador', 'maestro', 'estudiante'])) {
    respuestaJSON(false, null, 'Rol no válido', 400);
}

try {
    $db = Conexion::getInstance()->getConexion();
    
    // Verificar que el usuario existe
    $stmt = $db->prepare("SELECT id_usuario, email FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$idUsuario]);
    $usuarioExistente = $stmt->fetch();
    
    if (!$usuarioExistente) {
        respuestaJSON(false, null, 'Usuario no encontrado', 404);
    }
    
    // Verificar que el email no esté en uso por otro usuario
    if ($email !== $usuarioExistente['email']) {
        $stmt = $db->prepare("SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario != ?");
        $stmt->execute([$email, $idUsuario]);
        
        if ($stmt->fetch()) {
            respuestaJSON(false, null, 'El email ya está en uso', 400);
        }
    }
    
    // Actualizar usuario
    $stmt = $db->prepare("
        UPDATE usuarios 
        SET nombre = ?, apellido = ?, email = ?, rol = ?, activo = ?
        WHERE id_usuario = ?
    ");
    
    if ($stmt->execute([$nombre, $apellido, $email, $rol, $activo, $idUsuario])) {
        respuestaJSON(true, [
            'id_usuario' => $idUsuario,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'email' => $email,
            'rol' => $rol
        ], 'Usuario actualizado exitosamente');
    } else {
        respuestaJSON(false, null, 'Error al actualizar el usuario', 500);
    }
    
} catch (PDOException $e) {
    error_log("Error al editar usuario: " . $e->getMessage());
    respuestaJSON(false, null, 'Error del servidor', 500);
}
?>