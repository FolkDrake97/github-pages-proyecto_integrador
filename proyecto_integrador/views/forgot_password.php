<?php
require_once '../config/config.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - <?= APP_NAME ?></title>
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card mt-5">
                    <div class="card-header bg-warning text-white text-center">
                        <h4><?= APP_NAME ?></h4>
                        <p class="mb-0">Recuperar Contraseña</p>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Ingresa tu email y te enviaremos instrucciones para recuperar tu contraseña.
                        </div>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required 
                                       placeholder="ejemplo@correo.com">
                            </div>
                            
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="fas fa-paper-plane me-2"></i>Enviar Instrucciones
                            </button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <a href="login.php" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>Volver al Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>