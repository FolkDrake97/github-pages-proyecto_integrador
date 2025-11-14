<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';
include_once '../models/Enrollment.php';

$database = new Database();
$db = $database->getConnection();
$enrollment = new Enrollment($db);

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"));

switch($method) {
    case 'GET':
        if(isset($_GET['student_id'])) {
            // Obtener inscripciones de un estudiante
            $stmt = $enrollment->getByStudent($_GET['student_id']);
            $enrollments = array();
            
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $enrollments[] = $row;
            }
            
            echo json_encode(array(
                'success' => true,
                'data' => $enrollments
            ));
        } 
        elseif(isset($_GET['teacher_id'])) {
            // Obtener inscripciones pendientes de un maestro
            $stmt = $enrollment->getPendingByTeacher($_GET['teacher_id']);
            $enrollments = array();
            
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $enrollments[] = $row;
            }
            
            echo json_encode(array(
                'success' => true,
                'data' => $enrollments
            ));
        }
        break;
        
    case 'POST':
        // Crear nueva solicitud de inscripción
        $enrollment->id_estudiante = $data->id_estudiante;
        $enrollment->id_materia = $data->id_materia;
        
        $result = $enrollment->create();
        echo json_encode($result);
        break;
        
    case 'PUT':
        // Aprobar/rechazar inscripción
        $enrollment->id = $data->id;
        $enrollment->estado = $data->estado;
        $enrollment->motivo_rechazo = $data->motivo_rechazo ?? '';
        
        if($enrollment->updateStatus()) {
            echo json_encode(array(
                'success' => true,
                'message' => 'Estado de inscripción actualizado'
            ));
        } else {
            echo json_encode(array(
                'success' => false,
                'message' => 'Error al actualizar inscripción'
            ));
        }
        break;
}
?>