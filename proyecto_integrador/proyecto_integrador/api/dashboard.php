<?php
require_once '../config/config.php';
require_once '../models/User.php';
require_once '../models/Subject.php';
require_once '../models/Enrollment.php';
require_once '../models/Task.php';
require_once '../models/Grade.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

$user_id = $_GET['user_id'] ?? null;
$user_role = $_GET['user_role'] ?? null;

if (!$user_id || !$user_role) {
    echo json_encode(['success' => false, 'message' => 'Datos de usuario no proporcionados']);
    exit;
}

$response = ['success' => true, 'data' => []];

switch($user_role) {
    case 'estudiante':
        $enrollment = new Enrollment($db);
        $task = new Task($db);
        $grade = new Grade($db);
        
        $response['data'] = [
            'materias_inscritas' => $enrollment->getTotalMateriasAprobadas($user_id),
            'tareas_pendientes' => $task->getTotalTareasPendientes($user_id),
            'promedio_general' => $grade->calcularPromedioGeneral($user_id),
            'solicitudes_pendientes' => $enrollment->getTotalSolicitudesPendientes($user_id)
        ];
        break;
        
    case 'maestro':
        $subject = new Subject($db);
        $enrollment = new Enrollment($db);
        $task = new Task($db);
        
        $response['data'] = [
            'materias_asignadas' => $subject->getTotalMateriasMaestro($user_id),
            'solicitudes_pendientes' => $enrollment->getTotalSolicitudesPendientesMaestro($user_id),
            'estudiantes_totales' => $enrollment->getTotalEstudiantesAprobados($user_id),
            'tareas_pendientes_calificar' => $task->getTotalTareasPendientesCalificar($user_id)
        ];
        break;
        
    case 'administrador':
        $user = new User($db);
        $subject = new Subject($db);
        $enrollment = new Enrollment($db);
        
        $response['data'] = [
            'total_usuarios' => $user->getTotalUsuarios(),
            'total_materias' => $subject->getTotalMaterias(),
            'total_estudiantes' => $user->getTotalPorRol('estudiante'),
            'total_maestros' => $user->getTotalPorRol('maestro'),
            'solicitudes_pendientes' => $enrollment->getTotalSolicitudesPendientes()
        ];
        break;
        
    default:
        $response = ['success' => false, 'message' => 'Rol no válido'];
}

echo json_encode($response);
?>