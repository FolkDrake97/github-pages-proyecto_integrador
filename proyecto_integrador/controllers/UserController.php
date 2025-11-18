<?php
require_once '../models/User.php';
require_once '../models/Enrollment.php';
require_once '../models/Grade.php';

class UserController {
    private $userModel;
    private $enrollmentModel;
    private $gradeModel;
    
    public function __construct($db) {
        $this->userModel = new User($db);
        $this->enrollmentModel = new Enrollment($db);
        $this->gradeModel = new Grade($db);
    }
    
    public function listar() {
        if ($_SESSION['user_role'] !== 'administrador') {
            header('Location: ../views/dashboard_administrador.php');
            exit;
        }
        
        $rol = $_GET['rol'] ?? null;
        
        if ($rol) {
            $usuarios = $this->userModel->getByRole($rol);
        } else {
            $usuarios = $this->userModel->getAll();
        }
        
        include '../views/usuarios/lista.php';
    }
    
    public function crear() {
        if ($_SESSION['user_role'] !== 'administrador') {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'nombre' => $_POST['nombre'],
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'rol' => $_POST['rol']
            ];
            
            if ($this->userModel->create($datos)) {
                $_SESSION['success'] = "Usuario creado exitosamente";
                header('Location: ?controller=user&action=listar');
                exit;
            } else {
                $_SESSION['error'] = "Error al crear usuario";
            }
        }
        
        include '../views/usuarios/crear.php';
    }
    
    public function perfil() {
        $usuario_id = $_SESSION['user_id'];
        $usuario = $this->userModel->getById($usuario_id);
        
        if ($_SESSION['user_role'] === 'estudiante') {
            $materias_inscritas = $this->enrollmentModel->getMateriasEstudiante($usuario_id);
            $promedio_general = $this->gradeModel->calcularPromedioGeneral($usuario_id);
        }
        
        include '../views/usuarios/perfil.php';
    }
    
    public function actualizarPerfil() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario_id = $_SESSION['user_id'];
            $datos = [
                'nombre' => $_POST['nombre'],
                'email' => $_POST['email']
            ];
            
            // Si se proporciona nueva contraseña
            if (!empty($_POST['nueva_password'])) {
                $datos['password'] = $_POST['nueva_password'];
            }
            
            if ($this->userModel->update($usuario_id, $datos)) {
                $_SESSION['success'] = "Perfil actualizado exitosamente";
                $_SESSION['user_name'] = $datos['nombre'];
            } else {
                $_SESSION['error'] = "Error al actualizar perfil";
            }
            
            header('Location: ?controller=user&action=perfil');
            exit;
        }
    }
}
?>