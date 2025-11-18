<?php
require_once '../config/config.php';
require_once '../models/Report.php';
require_once '../models/Enrollment.php';
require_once '../models/Grade.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();
$report = new Report($db);
$enrollment = new Enrollment($db);
$grade = new Grade($db);

$method = $_SERVER['REQUEST_METHOD'];
$user_id = $_GET['user_id'] ?? null;
$user_role = $_GET['user_role'] ?? null;

if ($method === 'GET') {
    $report_type = $_GET['type'] ?? 'general';
    
    switch($report_type) {
        case 'student_progress':
            if ($user_id && $user_role === 'estudiante') {
                $progreso = $report->getProgresoEstudiante($user_id);
                $tareas_pendientes = $report->getTareasPendientes($user_id);
                $promedio_general = $grade->calcularPromedioGeneral($user_id);
                
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'progreso' => $progreso,
                        'tareas_pendientes' => $tareas_pendientes,
                        'promedio_general' => $promedio_general
                    ]
                ]);
            }
            break;
            
        case 'teacher_overview':
            if ($user_id && $user_role === 'maestro') {
                $resumen_materias = $report->getResumenMateriasMaestro($user_id);
                $solicitudes_pendientes = $enrollment->getSolicitudesPendientes($user_id);
                
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'resumen_materias' => $resumen_materias,
                        'solicitudes_pendientes' => $solicitudes_pendientes
                    ]
                ]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Tipo de reporte no válido']);
    }
}
?>