<?php
require_once '../models/Task.php';
require_once '../models/Subject.php';
require_once '../models/Enrollment.php';

class TaskController {
    private $taskModel;
    private $subjectModel;
    private $enrollmentModel;
    
    public function __construct($db) {
        $this->taskModel = new Task($db);
        $this->subjectModel = new Subject($db);
        $this->enrollmentModel = new Enrollment($db);
    }
    
    public function listar() {
        $usuario_id = $_SESSION['user_id'];
        $rol = $_SESSION['user_role'];
        
        if ($rol === 'estudiante') {
            $tareas = $this->taskModel->getTareasEstudiante($usuario_id);
        } else if ($rol === 'maestro') {
            $tareas = $this->taskModel->getTareasMaestro($usuario_id);
        }
        
        include '../views/tareas/lista.php';
    }
    
    public function crear() {
        if ($_SESSION['user_role'] !== 'maestro') {
            header('Location: ../views/dashboard_maestro.php');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'materia_id' => $_POST['materia_id'],
                'titulo' => $_POST['titulo'],
                'descripcion' => $_POST['descripcion'],
                'fecha_limite' => $_POST['fecha_limite'],
                'ponderacion' => $_POST['ponderacion']
            ];
            
            if ($this->taskModel->crearTarea($datos)) {
                $_SESSION['success'] = "Tarea creada exitosamente";
                header('Location: ?controller=task&action=listar');
                exit;
            }
        }
        
        $materias = $this->subjectModel->getMateriasMaestro($_SESSION['user_id']);
        include '../views/tareas/crear.php';
    }
    
    public function entregar() {
        if ($_SESSION['user_role'] !== 'estudiante') {
            header('Location: ../views/dashboard_estudiante.php');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tarea_id = $_POST['tarea_id'];
            $estudiante_id = $_SESSION['user_id'];
            
            // Lógica para subir archivo y registrar entrega
            if ($this->taskModel->registrarEntrega($tarea_id, $estudiante_id)) {
                $_SESSION['success'] = "Tarea entregada exitosamente";
            }
            
            header('Location: ?controller=task&action=listar');
            exit;
        }
    }
}
?>