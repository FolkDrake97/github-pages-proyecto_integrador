<?php

require_once 'config/database.php';
require_once 'includes/helpers.php';

iniciarSesionSegura();

if (estaAutenticado()) {
    redirigir('views/dashboard_' . obtenerRol() . '.php');
}

$error = '';
$exito = '';
$tokenValido = false;

$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error = 'Token de recuperación no válido';
} else {
    require_once 'includes/conexion.php';
    
    try {
        $db = Conexion::getInstance()->getConexion();
        
        // Verificar token
        $stmt = $db->prepare("
            SELECT pr.*, u.email 
            FROM password_reset pr 
            INNER JOIN usuarios u ON pr.id_usuario = u.id_usuario 
            WHERE pr.token = ? AND pr.usado = 0 AND pr.fecha_expiracion > NOW()
        ");
        $stmt->execute([$token]);
        $resetData = $stmt->fetch();
        
        if ($resetData) {
            $tokenValido = true;
            
            // Procesar cambio de contraseña
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $password = $_POST['password'] ?? '';
                $confirmar_password = $_POST['confirmar_password'] ?? '';
                
                if (empty($password) || empty($confirmar_password)) {
                    $error = 'Ambos campos son obligatorios';
                } elseif (!validarPassword($password)) {
                    $error = 'La contraseña debe tener al menos ' . PASSWORD_MIN_LENGTH . ' caracteres';
                } elseif ($password !== $confirmar_password) {
                    $error = 'Las contraseñas no coinciden';
                } else {
                    // Actualizar contraseña
                    $passwordHash = hashPassword($password);
                    $stmt = $db->prepare("UPDATE usuarios SET password = ? WHERE id_usuario = ?");
                    $stmt->execute([$passwordHash, $resetData['id_usuario']]);
                    
                    // Marcar token como usado
                    $stmt = $db->prepare("UPDATE password_reset SET usado = 1 WHERE id = ?");
                    $stmt->execute([$resetData['id']]);
                    
                    $exito = 'Contraseña actualizada exitosamente. Ahora puedes iniciar sesión.';
                    $tokenValido = false; // Ya no mostrar el formulario
                }
            }
            
        } else {
            $error = 'El enlace de recuperación ha expirado o no es válido';
        }
        
    } catch (PDOException $e) {
        error_log("Error en reset password: " . $e->getMessage());
        $error = 'Error del servidor. Por favor, intenta más tarde.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - <?php echo SITE_NAME; ?></title>
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
                            <i class="bi bi-key text-primary" style="font-size: 3rem;"></i>
                            <h2 class="mt-3">Nueva Contraseña</h2>
                            <p class="text-muted">Crea una nueva contraseña para tu cuenta</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($exito): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i><?php echo $exito; ?>
                                <div class="mt-2">
                                    <a href="login.php" class="btn btn-primary btn-sm">Iniciar Sesión</a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($tokenValido && !$exito): ?>
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Nueva Contraseña</label>
                                    <input type="password" class="form-control" name="password" required minlength="8">
                                    <small class="text-muted">Mínimo 8 caracteres</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Confirmar Contraseña</label>
                                    <input type="password" class="form-control" name="confirmar_password" required>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">Restablecer Contraseña</button>
                                </div>
                            </form>
                        <?php endif; ?>

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