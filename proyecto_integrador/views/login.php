<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../models/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = sanitizeInput($_POST['password'] ?? '');
    
    if (!empty($email) && !empty($password)) {
        $user->email = $email;
        $user->password = $password;
        
        if ($user->login()) {
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_name'] = $user->nombre;
            $_SESSION['user_email'] = $user->email;
            $_SESSION['user_role'] = $user->rol;
            
            // Redirigir según el rol
            redirect('views/dashboard_' . $user->rol . '.php');
        } else {
            $error = "Credenciales incorrectas. Usa: admin@escuela.com / 123456";
        }
    } else {
        $error = "Por favor complete todos los campos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    
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
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h1 class="login-title"><?= APP_NAME ?></h1>
                <p class="login-subtitle">Iniciar Sesión en tu Cuenta</p>
            </div>

            <!-- Body -->
            <div class="login-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-1"></i>Email
                        </label>
                        <input type="email" class="form-control" id="email" name="email" required 
                               value="<?= htmlspecialchars($_POST['email'] ?? 'admin@escuela.com') ?>"
                               placeholder="tu@email.com">
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-1"></i>Contraseña
                        </label>
                        <input type="password" class="form-control" id="password" name="password" required 
                               value="123456"
                               placeholder="••••••">
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-sign-in-alt me-2"></i>Ingresar al Sistema
                    </button>
                </form>

                <div class="login-links">
                    <a href="signup_page.php" class="login-link">
                        <i class="fas fa-user-plus me-1"></i>¿No tienes cuenta? Regístrate
                    </a>
                </div>

                <!-- Cuentas de demostración -->
                <div class="demo-accounts">
                    <h6 class="demo-title">Cuentas de Prueba:</h6>
                    
                    <div class="demo-account">
                        <span class="demo-role">Administrador</span>
                        <span class="demo-credentials">admin@escuela.com / 123456</span>
                    </div>
                    
                    <div class="demo-account">
                        <span class="demo-role">Maestro</span>
                        <span class="demo-credentials">matematicas@escuela.com / 123456</span>
                    </div>
                    
                    <div class="demo-account">
                        <span class="demo-role">Estudiante</span>
                        <span class="demo-credentials">estudiante@escuela.com / 123456</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Efectos interactivos
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.form-control');
            
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                
                input.addEventListener('blur', function() {
                    if (!this.value) {
                        this.parentElement.classList.remove('focused');
                    }
                });
            });

            // Auto-seleccionar texto en inputs de demo
            const demoInputs = document.querySelectorAll('input[value="admin@escuela.com"], input[value="123456"]');
            demoInputs.forEach(input => {
                input.addEventListener('click', function() {
                    this.select();
                });
            });
        });
    </script>
</body>
</html>