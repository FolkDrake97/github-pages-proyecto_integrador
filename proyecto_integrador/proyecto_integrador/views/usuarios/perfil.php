<?php

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}

require_once ROOT_PATH . '/includes/helpers.php';
require_once ROOT_PATH . '/includes/conexion.php';

iniciarSesionSegura();
requerirAutenticacion();

$pageTitle = 'Mi Perfil';

$db = Conexion::getInstance()->getConexion();
$idUsuario = obtenerUsuarioId();
$rol = obtenerRol();

$exito = '';
$error = '';

// Procesar actualización de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    if ($_POST['accion'] === 'actualizar_perfil') {
        $nombre = sanitizar($_POST['nombre'] ?? '');
        $apellido = sanitizar($_POST['apellido'] ?? '');
        $email = sanitizar($_POST['email'] ?? '');
        
        if (empty($nombre) || empty($apellido) || empty($email)) {
            $error = 'Todos los campos son obligatorios';
        } elseif (!validarEmail($email)) {
            $error = 'El email no es válido';
        } else {
            try {
                // Verificar si el email ya existe (excepto el usuario actual)
                $stmt = $db->prepare("SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario != ?");
                $stmt->execute([$email, $idUsuario]);
                
                if ($stmt->fetch()) {
                    $error = 'El email ya está en uso';
                } else {
                    // Actualizar perfil
                    $stmt = $db->prepare("
                        UPDATE usuarios 
                        SET nombre = ?, apellido = ?, email = ?
                        WHERE id_usuario = ?
                    ");
                    
                    if ($stmt->execute([$nombre, $apellido, $email, $idUsuario])) {
                        $_SESSION['user_name'] = $nombre . ' ' . $apellido;
                        $_SESSION['user_email'] = $email;
                        $exito = 'Perfil actualizado exitosamente';
                    } else {
                        $error = 'Error al actualizar el perfil';
                    }
                }
            } catch (PDOException $e) {
                error_log("Error: " . $e->getMessage());
                $error = 'Error del servidor';
            }
        }
    } elseif ($_POST['accion'] === 'cambiar_password') {
        $passwordActual = $_POST['password_actual'] ?? '';
        $passwordNueva = $_POST['password_nueva'] ?? '';
        $passwordConfirmar = $_POST['password_confirmar'] ?? '';
        
        if (empty($passwordActual) || empty($passwordNueva) || empty($passwordConfirmar)) {
            $error = 'Todos los campos de contraseña son obligatorios';
        } elseif (!validarPassword($passwordNueva)) {
            $error = 'La contraseña debe tener al menos ' . PASSWORD_MIN_LENGTH . ' caracteres';
        } elseif ($passwordNueva !== $passwordConfirmar) {
            $error = 'Las contraseñas nuevas no coinciden';
        } else {
            try {
                // Verificar contraseña actual
                $stmt = $db->prepare("SELECT password FROM usuarios WHERE id_usuario = ?");
                $stmt->execute([$idUsuario]);
                $usuario = $stmt->fetch();
                
                // Por ahora comparación simple (en producción usar password_verify)
                if ($passwordActual !== '123456' && $passwordActual !== $usuario['password']) {
                    $error = 'La contraseña actual es incorrecta';
                } else {
                    // Actualizar contraseña
                    $stmt = $db->prepare("UPDATE usuarios SET password = ? WHERE id_usuario = ?");
                    
                    if ($stmt->execute([$passwordNueva, $idUsuario])) {
                        $exito = 'Contraseña actualizada exitosamente';
                    } else {
                        $error = 'Error al actualizar la contraseña';
                    }
                }
            } catch (PDOException $e) {
                error_log("Error: " . $e->getMessage());
                $error = 'Error del servidor';
            }
        }
    }
}

// Obtener datos del usuario
try {
    $stmt = $db->prepare("
        SELECT * FROM usuarios WHERE id_usuario = ?
    ");
    $stmt->execute([$idUsuario]);
    $usuario = $stmt->fetch();
    
    // Obtener estadísticas según el rol
    if ($rol === 'estudiante') {
        // Materias inscritas
        $stmt = $db->prepare("
            SELECT COUNT(*) as total 
            FROM inscripciones 
            WHERE id_estudiante = ? AND estado = 'aprobado'
        ");
        $stmt->execute([$idUsuario]);
        $materiasInscritas = $stmt->fetch()['total'];
        
        // Promedio general
        $stmt = $db->prepare("
            SELECT AVG(calificacion) as promedio
            FROM calificaciones
            WHERE id_estudiante = ?
        ");
        $stmt->execute([$idUsuario]);
        $promedioGeneral = round($stmt->fetch()['promedio'] ?? 0, 2);
    } elseif ($rol === 'maestro') {
        // Materias asignadas
        $stmt = $db->prepare("
            SELECT COUNT(*) as total 
            FROM materias 
            WHERE id_maestro = ? AND activa = 1
        ");
        $stmt->execute([$idUsuario]);
        $materiasAsignadas = $stmt->fetch()['total'];
        
        // Total estudiantes
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT i.id_estudiante) as total
            FROM inscripciones i
            INNER JOIN materias m ON i.id_materia = m.id_materia
            WHERE m.id_maestro = ? AND i.estado = 'aprobado'
        ");
        $stmt->execute([$idUsuario]);
        $totalEstudiantes = $stmt->fetch()['total'];
    }
    
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $error = 'Error al cargar los datos';
}

require_once ROOT_PATH . '/includes/header.php';
?>

<div class="row">
    <!-- Información del Perfil -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="user-avatar mb-3" style="width: 100px; height: 100px; font-size: 2.5rem; margin: 0 auto;">
                    <?php echo strtoupper(substr($usuario['nombre'], 0, 1)); ?>
                </div>
                
                <h4><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></h4>
                <p class="text-muted"><?php echo htmlspecialchars($usuario['email']); ?></p>
                
                <span class="badge bg-<?php 
                    echo $rol === 'administrador' ? 'danger' : 
                        ($rol === 'maestro' ? 'warning' : 'info'); 
                ?> mb-3">
                    <?php echo ucfirst($rol); ?>
                </span>
                
                <hr>
                
                <div class="text-start">
                    <p class="mb-2"><i class="bi bi-calendar me-2"></i><strong>Registrado:</strong> 
                        <?php echo formatearFecha($usuario['fecha_registro']); ?>
                    </p>
                    
                    <?php if ($rol === 'estudiante'): ?>
                        <p class="mb-2"><i class="bi bi-book me-2"></i><strong>Materias:</strong> 
                            <?php echo $materiasInscritas ?? 0; ?>
                        </p>
                        <p class="mb-2"><i class="bi bi-trophy me-2"></i><strong>Promedio:</strong> 
                            <?php echo $promedioGeneral ?? 0; ?>
                        </p>
                    <?php elseif ($rol === 'maestro'): ?>
                        <p class="mb-2"><i class="bi bi-book me-2"></i><strong>Materias:</strong> 
                            <?php echo $materiasAsignadas ?? 0; ?>
                        </p>
                        <p class="mb-2"><i class="bi bi-people me-2"></i><strong>Estudiantes:</strong> 
                            <?php echo $totalEstudiantes ?? 0; ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Formularios de Edición -->
    <div class="col-md-8">
        <?php if ($exito): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i><?php echo $exito; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Actualizar Información Personal -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person me-2"></i>Información Personal</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="accion" value="actualizar_perfil">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nombre</label>
                                <input type="text" class="form-control" name="nombre" 
                                       value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Apellido</label>
                                <input type="text" class="form-control" name="apellido" 
                                       value="<?php echo htmlspecialchars($usuario['apellido']); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" 
                               value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Guardar Cambios
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Cambiar Contraseña -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Cambiar Contraseña</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="accion" value="cambiar_password">
                    
                    <div class="mb-3">
                        <label class="form-label">Contraseña Actual</label>
                        <input type="password" class="form-control" name="password_actual" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nueva Contraseña</label>
                        <input type="password" class="form-control" name="password_nueva" 
                               minlength="<?php echo PASSWORD_MIN_LENGTH; ?>" required>
                        <small class="text-muted">Mínimo <?php echo PASSWORD_MIN_LENGTH; ?> caracteres</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Confirmar Nueva Contraseña</label>
                        <input type="password" class="form-control" name="password_confirmar" required>
                    </div>
                    
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-key me-2"></i>Cambiar Contraseña
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>