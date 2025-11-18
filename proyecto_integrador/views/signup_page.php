<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../models/User.php';

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
    $confirm_password = sanitizeInput($_POST['confirm_password'] ?? '');
    
    // Validaciones
    if (empty($nombre) || empty($apellido) || empty($email) || empty($password)) {
        $error = "Todos los campos son obligatorios";
    } elseif ($password !== $confirm_password) {
        $error = "Las contraseñas no coinciden";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres";
    } else {
        // Verificar si el email ya existe
        $user->email = $email;
        if ($user->emailExists()) {
            $error = "Este email ya está registrado";
        } else {
            // Registrar nuevo usuario (siempre como estudiante)
            $user->nombre = $nombre;
            $user->apellido = $apellido;
            $user->password = $password;
            $user->rol = 'estudiante';
            
            if ($user->register()) {
                $success = "¡Registro exitoso! Ahora puedes iniciar sesión";
                // Limpiar formulario
                $_POST = array();
            } else {
                $error = "Error al registrar el usuario. Intenta nuevamente.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - <?= APP_NAME ?></title>
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Estilos -->
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="login-screen">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header" style="background: linear-gradient(135deg, #4cc9f0, #4361ee);">
                <div class="login-logo">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h1 class="login-title"><?= APP_NAME ?></h1>
                <p class="login-subtitle">Crear Cuenta Nueva</p>
            </div>

            <!-- Body -->
            <div class="login-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?= $error ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= $success ?>
                        <br>
                        <small><a href="login.php" class="alert-link">Haz clic aquí para iniciar sesión</a></small>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombre" class="form-label">
                                    <i class="fas fa-user me-1"></i>Nombre
                                </label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required 
                                       value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                                       placeholder="Tu nombre">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="apellido" class="form-label">
                                    <i class="fas fa-user me-1"></i>Apellido
                                </label>
                                <input type="text" class="form-control" id="apellido" name="apellido" required 
                                       value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>"
                                       placeholder="Tu apellido">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-1"></i>Email
                        </label>
                        <input type="email" class="form-control" id="email" name="email" required 
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               placeholder="ejemplo@correo.com">
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-1"></i>Contraseña
                        </label>
                        <input type="password" class="form-control" id="password" name="password" required 
                               placeholder="Mínimo 6 caracteres"
                               minlength="6">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">
                            <i class="fas fa-lock me-1"></i>Confirmar Contraseña
                        </label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required 
                               placeholder="Repite tu contraseña">
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" style="background: linear-gradient(135deg, #4cc9f0, #4361ee);">
                        <i class="fas fa-user-plus me-2"></i>Crear Cuenta
                    </button>
                </form>

                <div class="login-links">
                    <a href="login.php" class="login-link">
                        <i class="fas fa-arrow-left me-1"></i>Volver al Inicio de Sesión
                    </a>
                </div>

                <!-- Información importante -->
                <div class="demo-accounts">
                    <h6 class="demo-title">Información importante:</h6>
                    
                    <div class="demo-account">
                        <span class="demo-role">Tipo de cuenta</span>
                        <span class="demo-credentials">Todos los registros son como Estudiante</span>
                    </div>
                    
                    <div class="demo-account">
                        <span class="demo-role">Contraseña</span>
                        <span class="demo-credentials">Mínimo 6 caracteres</span>
                    </div>
                    
                    <div class="demo-account">
                        <span class="demo-role">Email</span>
                        <span class="demo-credentials">Usa un email válido</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Validación de contraseñas en tiempo real
        document.addEventListener('DOMContentLoaded', function() {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            function validatePasswords() {
                if (password.value !== confirmPassword.value) {
                    confirmPassword.style.borderColor = '#f72585';
                } else {
                    confirmPassword.style.borderColor = '#4cc9f0';
                }
            }
            
            password.addEventListener('input', validatePasswords);
            confirmPassword.addEventListener('input', validatePasswords);
            
            // Limpiar formulario después de registro exitoso
            <?php if (!empty($success)): ?>
                document.querySelector('form').reset();
            <?php endif; ?>
        });
    </script>
</body>
</html>