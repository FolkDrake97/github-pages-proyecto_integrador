<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';
include_once '../models/Task.php';
include_once '../models/Enrollment.php';

$database = new Database();
$db = $database->getConnection();
$task = new Task($db);
$enrollment = new Enrollment($db);

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"));

switch($method) {
    case 'GET':
        if(isset($_GET['subject_id'])) {
            // Tareas por materia (para maestros)
            $stmt = $task->getBySubject($_GET['subject_id']);
            $tasks = array();
            
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $tasks[] = $row;
            }
            
            echo json_encode(array(
                'success' => true,
                'data' => $tasks
            ));
        } 
        elseif(isset($_GET['student_id'])) {
            // Tareas para estudiante
            $subjectId = $_GET['subject_id'] ?? null;
            $stmt = $task->getForStudent($_GET['student_id'], $subjectId);
            $tasks = array();
            
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $tasks[] = $row;
            }
            
            echo json_encode(array(
                'success' => true,
                'data' => $tasks
            ));
        }
        elseif(isset($_GET['task_id'])) {
            // Tarea específica
            $task->id = $_GET['task_id'];
            $taskData = $task->getById();
            
            if($taskData) {
                echo json_encode(array(
                    'success' => true,
                    'data' => $taskData
                ));
            } else {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Tarea no encontrada'
                ));
            }
        }
        break;
        
    case 'POST':
        // Crear nueva tarea
        $task->id_materia = $data->id_materia;
        $task->titulo = $data->titulo;
        $task->descripcion = $data->descripcion;
        $task->fecha_limite = $data->fecha_limite;
        $task->ponderacion = $data->ponderacion;
        
        if($task->create()) {
            echo json_encode(array(
                'success' => true,
                'message' => 'Tarea creada exitosamente'
            ));
        } else {
            echo json_encode(array(
                'success' => false,
                'message' => 'Error al crear tarea'
            ));
        }
        break;
}
?>