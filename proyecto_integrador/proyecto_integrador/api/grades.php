<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';
include_once '../models/Grade.php';
include_once '../models/Enrollment.php';

$database = new Database();
$db = $database->getConnection();
$grade = new Grade($db);
$enrollment = new Enrollment($db);

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"));

switch($method) {
    case 'GET':
        if(isset($_GET['student_id']) && isset($_GET['subject_id'])) {
            // Calificaciones de estudiante por materia
            $stmt = $grade->getByStudentAndSubject($_GET['student_id'], $_GET['subject_id']);
            $grades = array();
            
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $grades[] = $row;
            }
            
            // Calcular promedio
            $average = $grade->calculateAverage($_GET['student_id'], $_GET['subject_id']);
            
            echo json_encode(array(
                'success' => true,
                'data' => $grades,
                'average' => $average
            ));
        }
        elseif(isset($_GET['student_id']) && !isset($_GET['subject_id'])) {
            // Promedio general del estudiante
            $overallAverage = $grade->calculateOverallAverage($_GET['student_id']);
            
            echo json_encode(array(
                'success' => true,
                'average' => $overallAverage
            ));
        }
        break;
        
    case 'POST':
        // Verificar si el estudiante está aprobado en la materia
        $taskQuery = "SELECT id_materia FROM tareas WHERE id = ?";
        $taskStmt = $db->prepare($taskQuery);
        $taskStmt->bindParam(1, $data->id_tarea);
        $taskStmt->execute();
        $taskData = $taskStmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$enrollment->isStudentApproved($data->id_estudiante, $taskData['id_materia'])) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Estudiante no aprobado en esta materia'
            ));
            break;
        }
        
        // Registrar calificación
        $grade->id_estudiante = $data->id_estudiante;
        $grade->id_tarea = $data->id_tarea;
        $grade->calificacion = $data->calificacion;
        $grade->fecha_entrega = date('Y-m-d H:i:s');
        
        if($grade->create()) {
            echo json_encode(array(
                'success' => true,
                'message' => 'Calificación registrada exitosamente'
            ));
        } else {
            echo json_encode(array(
                'success' => false,
                'message' => 'Error al registrar calificación'
            ));
        }
        break;
}
?>