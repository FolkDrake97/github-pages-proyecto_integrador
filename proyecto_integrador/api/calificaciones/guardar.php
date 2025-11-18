<?php

header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/conexion.php';

iniciarSesionSegura();

// Verificar autenticación y rol de maestro
if (!estaAutenticado() || !tieneRol('maestro')) {
    respuestaJSON(false, null, 'No autorizado', 403);
}

// Solo aceptar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respuestaJSON(false, null, 'Método no permitido', 405);
}

// Obtener datos
$idActividad = $_POST['id_actividad'] ?? null;
$calificaciones = $_POST['calificacion'] ?? [];
$comentarios = $_POST['comentarios'] ?? [];

if (!$idActividad) {
    respuestaJSON(false, null, 'ID de actividad requerido', 400);
}

try {
    $db = Conexion::getInstance()->getConexion();
    $idMaestro = obtenerUsuarioId();
    
    // Verificar que la actividad pertenece al maestro
    $stmt = $db->prepare("
        SELECT a.id_actividad 
        FROM actividades a
        INNER JOIN materias m ON a.id_materia = m.id_materia
        WHERE a.id_actividad = ? AND m.id_maestro = ?
    ");
    $stmt->execute([$idActividad, $idMaestro]);
    
    if (!$stmt->fetch()) {
        respuestaJSON(false, null, 'Actividad no válida', 400);
    }
    
    // Procesar calificaciones
    $guardados = 0;
    $errores = 0;
    
    foreach ($calificaciones as $idEstudiante => $calificacion) {
        if ($calificacion === '') {
            continue; // Saltar si no hay calificación
        }
        
        $calificacion = floatval($calificacion);
        $comentario = sanitizar($comentarios[$idEstudiante] ?? '');
        
        if ($calificacion < 0 || $calificacion > 100) {
            $errores++;
            continue;
        }
        
        // Verificar si ya existe una calificación
        $stmt = $db->prepare("
            SELECT id_calificacion FROM calificaciones 
            WHERE id_estudiante = ? AND id_actividad = ?
        ");
        $stmt->execute([$idEstudiante, $idActividad]);
        $existente = $stmt->fetch();
        
        if ($existente) {
            // Actualizar calificación existente
            $stmt = $db->prepare("
                UPDATE calificaciones 
                SET calificacion = ?, comentarios = ?, fecha_calificacion = NOW()
                WHERE id_estudiante = ? AND id_actividad = ?
            ");
            $stmt->execute([$calificacion, $comentario, $idEstudiante, $idActividad]);
        } else {
            // Insertar nueva calificación
            $stmt = $db->prepare("
                INSERT INTO calificaciones (id_estudiante, id_actividad, calificacion, comentarios, fecha_calificacion)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$idEstudiante, $idActividad, $calificacion, $comentario]);
        }
        
        if ($stmt->rowCount() > 0) {
            $guardados++;
        } else {
            $errores++;
        }
    }
    
    respuestaJSON(true, [
        'guardados' => $guardados,
        'errores' => $errores
    ], "Calificaciones guardadas: $guardados. Errores: $errores");
    
} catch (PDOException $e) {
    error_log("Error al guardar calificaciones: " . $e->getMessage());
    respuestaJSON(false, null, 'Error del servidor', 500);
}

// Verificar logros automáticamente
require_once '../../includes/logros.php';

try {
    $sistemaLogros = new SistemaLogros($db);
    
    // Verificar logros para cada estudiante calificado
    foreach (array_keys($calificaciones) as $idEstudiante) {
        $sistemaLogros->verificarLogros($idEstudiante);
    }
} catch (Exception $e) {
    error_log("Error al verificar logros: " . $e->getMessage());
}
?>