<?php
require_once '../models/Grade.php';
require_once '../models/Task.php';
require_once '../models/Enrollment.php';

class GradeController {
    private $gradeModel;
    private $taskModel;
    private $enrollmentModel;
    
    public function __construct($db) {
        $this->gradeModel = new Grade($db);
        $this->taskModel = new Task($db);
        $this->enrollmentModel = new Enrollment($db);
    }
    
    public function calificar() {
        if ($_SESSION['user_role'] !== 'maestro') {
            header('Location: ../views/dashboard_maestro.php');
            exit;
        }
        
        $tarea_id = $_GET['tarea'] ?? null;
        
        if (!$tarea_id) {
            $_SESSION['error'] = "Tarea no especificada";
            header('Location: ?controller=task&action=listar');
            exit;
        }
        
        // Obtener entregas de la tarea
        $entregas = $this->gradeModel->getEntregasTarea($tarea_id);
        $tarea = $this->taskModel->getById($tarea_id);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($_POST['calificaciones'] as $estudiante_id => $calificacion) {
                $this->gradeModel->registrarCalificacion($tarea_id, $estudiante_id, $calificacion);
            }
            $_SESSION['success'] = "Calificaciones registradas exitosamente";
            header('Location: ?controller=grade&action=calificar&tarea=' . $tarea_id);
            exit;
        }
        
        include '../views/calificaciones/registrar.php';
    }
    
    public function misCalificaciones() {
        if ($_SESSION['user_role'] !== 'estudiante') {
            header('Location: ../views/dashboard_estudiante.php');
            exit;
        }
        
        $estudiante_id = $_SESSION['user_id'];
        $calificaciones = $this->gradeModel->getCalificacionesEstudiante($estudiante_id);
        
        // Calcular promedios
        $promedios = $this->gradeModel->calcularPromedios($estudiante_id);
        
        include '../views/calificaciones/mis_calificaciones.php';
    }
    
    public function guardar() {
        if ($_SESSION['user_role'] !== 'maestro') {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        
        if ($_POST) {
            $tarea_id = $_POST['tarea_id'];
            $estudiante_id = $_POST['estudiante_id'];
            $calificacion = $_POST['calificacion'];
            
            if ($this->gradeModel->registrarCalificacion($tarea_id, $estudiante_id, $calificacion)) {
                echo json_encode(['success' => true, 'message' => 'Calificación guardada']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al guardar']);
            }
        }
    }
}
?>