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

// Solo aceptar método DELETE
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    respuestaJSON(false, null, 'Método no permitido', 405);
}

// Obtener datos JSON
$input = json_decode(file_get_contents('php://input'), true);
$idUsuario = $input['id_usuario'] ?? null;

if (!$idUsuario) {
    respuestaJSON(false, null, 'ID de usuario requerido', 400);
}

// No permitir que el usuario se elimine a sí mismo
if ($idUsuario == obtenerUsuarioId()) {
    respuestaJSON(false, null, 'No puedes eliminarte a ti mismo', 400);
}

try {
    $db = Conexion::getInstance()->getConexion();
    
    // Verificar que el usuario existe
    $stmt = $db->prepare("SELECT id_usuario, nombre, apellido FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$idUsuario]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        respuestaJSON(false, null, 'Usuario no encontrado', 404);
    }
    
    // Eliminación lógica (desactivar usuario)
    $stmt = $db->prepare("UPDATE usuarios SET activo = 0 WHERE id_usuario = ?");
    
    if ($stmt->execute([$idUsuario])) {
        respuestaJSON(true, [
            'id_usuario' => $idUsuario,
            'nombre' => $usuario['nombre'] . ' ' . $usuario['apellido']
        ], 'Usuario eliminado exitosamente');
    } else {
        respuestaJSON(false, null, 'Error al eliminar el usuario', 500);
    }
    
} catch (PDOException $e) {
    error_log("Error al eliminar usuario: " . $e->getMessage());
    respuestaJSON(false, null, 'Error del servidor', 500);
}
?>