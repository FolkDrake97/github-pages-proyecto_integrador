<?php

require_once 'config/database.php';
require_once 'includes/helpers.php';

iniciarSesionSegura();

if (estaAutenticado()) {
    redirigir('views/dashboard_' . obtenerRol() . '.php');
}

$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    
    if (empty($email) || !validarEmail($email)) {
        $error = 'Por favor, ingresa un email válido';
    } else {
        require_once 'includes/conexion.php';
        
        try {
            $db = Conexion::getInstance()->getConexion();
            
            // Verificar si el usuario existe
            $stmt = $db->prepare("SELECT id_usuario, nombre FROM usuarios WHERE email = ? AND activo = 1");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();
            
            if ($usuario) {
                // Generar token
                $token = generarToken();
                $fechaExpiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Guardar token en la base de datos
                $stmt = $db->prepare("
                    INSERT INTO password_reset (id_usuario, token, fecha_expiracion) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$usuario['id_usuario'], $token, $fechaExpiracion]);
                
                // En un entorno real, aquí enviarías el email
                $resetLink = BASE_URL . "reset_password.php?token=" . $token;
                
                $exito = "Se ha enviado un enlace de recuperación a tu email.<br>
                         <strong>Enlace de prueba:</strong> <a href='$resetLink'>$resetLink</a><br>
                         <small>En producción, esto se enviaría por email.</small>";
                
            } else {
                $error = 'No se encontró una cuenta activa con ese email';
            }
            
        } catch (PDOException $e) {
            error_log("Error en recuperación: " . $e->getMessage());
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
    <title>Recuperar Contraseña - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; }
        .card { border-radius: 1rem; box-shadow: 0 1rem 3rem rgba(0,0,0,0.3); }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-shield-lock text-primary" style="font-size: 3rem;"></i>
                            <h2 class="mt-3">Recuperar Contraseña</h2>
                            <p class="text-muted">Ingresa tu email para restablecer tu contraseña</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($exito): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i><?php echo $exito; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Enviar Enlace de Recuperación</button>
                            </div>
                        </form>

                        <div class="text-center mt-3">
                            <a href="login.php" class="text-decoration-none">
                                <i class="bi bi-arrow-left me-1"></i>Volver al Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>