<?php
require_once '../models/Enrollment.php';
require_once '../models/Subject.php';
require_once '../models/User.php';

class EnrollmentController {
    private $enrollmentModel;
    private $subjectModel;
    private $userModel;
    
    public function __construct($db) {
        $this->enrollmentModel = new Enrollment($db);
        $this->subjectModel = new Subject($db);
        $this->userModel = new User($db);
    }
    
    public function solicitar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $estudiante_id = $_SESSION['user_id'];
            $materia_id = $_POST['materia_id'];
            
            if ($this->enrollmentModel->solicitarInscripcion($estudiante_id, $materia_id)) {
                $_SESSION['success'] = "Solicitud de inscripción enviada";
            } else {
                $_SESSION['error'] = "Error al enviar solicitud";
            }
            header('Location: ../views/materias/lista.php');
            exit;
        }
    }
    
    public function solicitudes() {
        if ($_SESSION['user_role'] !== 'maestro') {
            header('Location: ../views/dashboard_maestro.php');
            exit;
        }
        
        $maestro_id = $_SESSION['user_id'];
        $solicitudes = $this->enrollmentModel->getSolicitudesPendientes($maestro_id);
        include '../views/inscripciones/solicitudes.php';
    }
    
    public function aprobar() {
        if ($_POST && isset($_POST['inscripcion_id'])) {
            $this->enrollmentModel->actualizarEstado($_POST['inscripcion_id'], 'aprobada');
            $_SESSION['success'] = "Inscripción aprobada";
            header('Location: ?controller=enrollment&action=solicitudes');
            exit;
        }
    }
    
    public function rechazar() {
        if ($_POST && isset($_POST['inscripcion_id'])) {
            $this->enrollmentModel->actualizarEstado($_POST['inscripcion_id'], 'rechazada', $_POST['motivo']);
            $_SESSION['success'] = "Inscripción rechazada";
            header('Location: ?controller=enrollment&action=solicitudes');
            exit;
        }
    }
}
?>