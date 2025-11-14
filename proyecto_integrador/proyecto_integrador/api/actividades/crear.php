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

// Obtener datos del formulario
$idMateria = $_POST['id_materia'] ?? null;
$titulo = sanitizar($_POST['titulo'] ?? '');
$descripcion = sanitizar($_POST['descripcion'] ?? '');
$fechaLimite = $_POST['fecha_limite'] ?? '';
$ponderacion = $_POST['ponderacion'] ?? '';
$tipo = sanitizar($_POST['tipo'] ?? 'tarea');

// Validaciones
if (empty($idMateria) || empty($titulo) || empty($fechaLimite) || empty($ponderacion)) {
    respuestaJSON(false, null, 'Todos los campos son obligatorios', 400);
}

if (!is_numeric($ponderacion) || $ponderacion < 1 || $ponderacion > 100) {
    respuestaJSON(false, null, 'La ponderación debe ser un número entre 1 y 100', 400);
}

try {
    $db = Conexion::getInstance()->getConexion();
    $idMaestro = obtenerUsuarioId();
    
    // Verificar que la materia pertenece al maestro
    $stmt = $db->prepare("SELECT id_materia FROM materias WHERE id_materia = ? AND id_maestro = ?");
    $stmt->execute([$idMateria, $idMaestro]);
    $materia = $stmt->fetch();
    
    if (!$materia) {
        respuestaJSON(false, null, 'Materia no válida', 400);
    }
    
    // Crear actividad
    $stmt = $db->prepare("
        INSERT INTO actividades (id_materia, titulo, descripcion, fecha_limite, ponderacion, tipo) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    if ($stmt->execute([$idMateria, $titulo, $descripcion, $fechaLimite, $ponderacion, $tipo])) {
        respuestaJSON(true, [
            'id_actividad' => $db->lastInsertId(),
            'titulo' => $titulo
        ], 'Actividad creada exitosamente');
    } else {
        respuestaJSON(false, null, 'Error al crear la actividad', 500);
    }
    
} catch (PDOException $e) {
    error_log("Error al crear actividad: " . $e->getMessage());
    respuestaJSON(false, null, 'Error del servidor', 500);
}
?>