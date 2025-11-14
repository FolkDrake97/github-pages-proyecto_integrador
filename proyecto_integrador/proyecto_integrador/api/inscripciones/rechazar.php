<?php
header('Content-Type: application/json; charset=utf-8');

define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/includes/helpers.php';

iniciarSesionSegura();

if (!estaAutenticado() || !tieneRol('maestro')) {
    respuestaJSON(false, null, 'No autorizado', 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    respuestaJSON(false, null, 'Método no permitido', 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$idInscripcion = $input['id_inscripcion'] ?? null;
$motivo = sanitizar($input['motivo'] ?? '');

if (!$idInscripcion) {
    respuestaJSON(false, null, 'ID de inscripción requerido', 400);
}

try {
    $db = Database::getInstance()->getConnection();
    $idMaestro = obtenerUsuarioId();
    
    $stmt = $db->prepare("
        SELECT i.*, m.nombre as materia_nombre
        FROM inscripciones i
        INNER JOIN materias m ON i.id_materia = m.id_materia
        WHERE i.id_inscripcion = ? AND m.id_maestro = ? AND i.estado = 'pendiente'
    ");
    $stmt->execute([$idInscripcion, $idMaestro]);
    $inscripcion = $stmt->fetch();
    
    if (!$inscripcion) {
        respuestaJSON(false, null, 'Solicitud no encontrada o no autorizada', 404);
    }
    
    $stmt = $db->prepare("
        UPDATE inscripciones 
        SET estado = 'rechazado', motivo_rechazo = ?, fecha_respuesta = NOW()
        WHERE id_inscripcion = ?
    ");
    
    if ($stmt->execute([$motivo, $idInscripcion])) {
        respuestaJSON(true, ['id_inscripcion' => $idInscripcion], 'Inscripción rechazada');
    } else {
        respuestaJSON(false, null, 'Error al rechazar la inscripción', 500);
    }
    
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    respuestaJSON(false, null, 'Error del servidor', 500);
}