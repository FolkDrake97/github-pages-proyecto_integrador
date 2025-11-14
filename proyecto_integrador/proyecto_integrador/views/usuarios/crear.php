<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once ROOT_DIR . '/models/User.php';

requireRole(['administrador']); // Solo administradores

$page_title = "Crear Usuario";

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = sanitizeInput($_POST['nombre'] ?? '');
    $apellido = sanitizeInput($_POST['apellido'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = sanitizeInput($_POST['password'] ?? '');
    $rol = sanitizeInput($_POST['rol'] ?? 'estudiante');
    
    // Validaciones...
    if (empty($nombre) || empty($apellido) || empty($email) || empty($password)) {
        $error = "Todos los campos son obligatorios";
    } elseif ($user->emailExists($email)) {
        $error = "Este email ya está registrado";
    } else {
        $user->nombre = $nombre;
        $user->apellido = $apellido;
        $user->email = $email;
        $user->password = $password;
        $user->rol = $rol;
        
        if ($user->register()) {
            $success = "Usuario creado exitosamente";
            $_POST = array();
        } else {
            $error = "Error al crear el usuario";
        }
    }
}

// Incluir header
require_once ROOT_DIR . '/includes/header.php';
?>

<!-- Contenido HTML -->
<div class="container mt-4">
    <!-- Tu contenido aquí -->
</div>

<?php
require_once ROOT_DIR . '/includes/footer.php';
?>