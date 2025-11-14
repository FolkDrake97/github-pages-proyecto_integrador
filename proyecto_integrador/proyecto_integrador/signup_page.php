<?php

require_once 'config/database.php';
require_once 'includes/helpers.php';

iniciarSesionSegura();

// Si ya está autenticado, redirigir al dashboard
if (estaAutenticado()) {
    redirigir('views/dashboard/' . obtenerRol() . '.php');
}

$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'includes/conexion.php';
    
    // Obtener y sanitizar datos
    $nombre = sanitizar($_POST['nombre'] ?? '');
    $apellido = sanitizar($_POST['apellido'] ?? '');
    $email = sanitizar($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmarPassword = $_POST['confirmar_password'] ?? '';
    $rol = 'estudiante'; // Por defecto, los registros son estudiantes
    
    // Validaciones
    if (empty($nombre) || empty($apellido) || empty($email) || empty($password)) {
        $error = 'Todos los campos son obligatorios';
    } elseif (!validarEmail($email)) {
        $error = 'El email no es válido';
    } elseif (!validarPassword($password)) {
        $error = 'La contraseña debe tener al menos ' . PASSWORD_MIN_LENGTH . ' caracteres';
    } elseif ($password !== $confirmarPassword) {
        $error = 'Las contraseñas no coinciden';
    } else {
        try {
            $db = Conexion::getInstance()->getConexion();
            
            // Verificar si el email ya existe
            $stmt = $db->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = 'El email ya está registrado';
            } else {
                // Crear usuario
                $passwordHash = hashPassword($password);
                $stmt = $db->prepare("
                    INSERT INTO usuarios (nombre, apellido, email, password, rol) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                
                if ($stmt->execute([$nombre, $apellido, $email, $passwordHash, $rol])) {
                    $exito = 'Registro exitoso. Ahora puedes iniciar sesión.';
                    // Limpiar formulario
                    $nombre = $apellido = $email = '';
                } else {
                    $error = 'Error al crear la cuenta. Intenta nuevamente.';
                }
            }
            
        } catch (PDOException $e) {
            error_log("Error en registro: " . $e->getMessage());
            $error = 'Error del servidor. Por favor, intenta más tarde.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-mortarboard-fill text-primary" style="font-size: 3rem;"></i>
                            <h2 class="mt-3">Crear Cuenta</h2>
                            <p class="text-muted">Únete a <?php echo SITE_NAME; ?></p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($exito): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <?php echo $exito; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" id="formRegistro">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre(s)</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?php echo $nombre ?? ''; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="apellido" class="form-label">Apellido(s)</label>
                                <input type="text" class="form-control" id="apellido" name="apellido" 
                                       value="<?php echo $apellido ?? ''; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo $email ?? ''; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Mínimo <?php echo PASSWORD_MIN_LENGTH; ?> caracteres</small>
                            </div>

                            <div class="mb-3">
                                <label for="confirmar_password" class="form-label">Confirmar Contraseña</label>
                                <input type="password" class="form-control" id="confirmar_password" 
                                       name="confirmar_password" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-person-plus me-2"></i>Crear Cuenta
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="mb-0">¿Ya tienes cuenta? 
                                <a href="login.php" class="text-decoration-none">Inicia Sesión</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/validaciones.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    </script>
</body>
</html>