<?php

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

require_once ROOT_PATH . '/includes/helpers.php';

iniciarSesionSegura();
requerirRol('administrador');

$pageTitle = 'Dashboard Administrador';

$db = Database::getInstance()->getConnection();

try {
    // Total usuarios
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM usuarios WHERE activo = 1");
    $stmt->execute();
    $totalUsuarios = $stmt->fetch()['total'];
    
    // Total estudiantes
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'estudiante' AND activo = 1");
    $stmt->execute();
    $totalEstudiantes = $stmt->fetch()['total'];
    
    // Total maestros
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'maestro' AND activo = 1");
    $stmt->execute();
    $totalMaestros = $stmt->fetch()['total'];
    
    // Total materias
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM materias WHERE activa = 1");
    $stmt->execute();
    $totalMaterias = $stmt->fetch()['total'];
    
    // Inscripciones pendientes
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM inscripciones WHERE estado = 'pendiente'");
    $stmt->execute();
    $inscripcionesPendientes = $stmt->fetch()['total'];
    
    // Actividades activas
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM actividades WHERE activa = 1");
    $stmt->execute();
    $actividadesActivas = $stmt->fetch()['total'];
    
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $totalUsuarios = $totalEstudiantes = $totalMaestros = 0;
    $totalMaterias = $inscripcionesPendientes = $actividadesActivas = 0;
}

require_once ROOT_PATH . '/includes/header.php';
?>

<!-- Banner de Bienvenida -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body p-4">
                <h2><i class="fas fa-user-shield me-2"></i>¡Bienvenido, Administrador!</h2>
                <p class="mb-0">Panel de control del sistema - <?php echo date('d/m/Y'); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas Principales -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-users fa-3x text-primary mb-2"></i>
                <h3 class="mb-1"><?php echo $totalUsuarios; ?></h3>
                <p class="text-muted mb-0">Total Usuarios</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-book fa-3x text-success mb-2"></i>
                <h3 class="mb-1"><?php echo $totalMaterias; ?></h3>
                <p class="text-muted mb-0">Materias Activas</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-clipboard-list fa-3x text-info mb-2"></i>
                <h3 class="mb-1"><?php echo $actividadesActivas; ?></h3>
                <p class="text-muted mb-0">Actividades</p>
            </div>
        </div>
    </div>
</div>

<!-- Desglose por Roles -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-user-graduate fa-3x text-info mb-2"></i>
                <h3 class="mb-1"><?php echo $totalEstudiantes; ?></h3>
                <p class="text-muted mb-0">Estudiantes</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-chalkboard-teacher fa-3x text-warning mb-2"></i>
                <h3 class="mb-1"><?php echo $totalMaestros; ?></h3>
                <p class="text-muted mb-0">Maestros</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-user-clock fa-3x text-danger mb-2"></i>
                <h3 class="mb-1"><?php echo $inscripcionesPendientes; ?></h3>
                <p class="text-muted mb-0">Solicitudes Pendientes</p>
            </div>
        </div>
    </div>
</div>

<!-- Acciones Rápidas -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Administración del Sistema</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <a href="<?php echo BASE_URL; ?>/views/usuarios/lista.php" class="btn btn-outline-primary w-100 p-4 h-100 text-decoration-none">
                            <i class="fas fa-users fa-3x d-block mb-3"></i>
                            <h5 class="mb-2">Gestionar Usuarios</h5>
                            <p class="mb-0 small text-muted">Crear, editar y eliminar usuarios del sistema</p>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="<?php echo BASE_URL; ?>/views/materias/lista.php" class="btn btn-outline-success w-100 p-4 h-100 text-decoration-none">
                            <i class="fas fa-book fa-3x d-block mb-3"></i>
                            <h5 class="mb-2">Gestionar Materias</h5>
                            <p class="mb-0 small text-muted">Administrar materias y asignaciones</p>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="<?php echo BASE_URL; ?>/views/usuarios/perfil.php" class="btn btn-outline-warning w-100 p-4 h-100 text-decoration-none">
                            <i class="fas fa-user-cog fa-3x d-block mb-3"></i>
                            <h5 class="mb-2">Mi Perfil</h5>
                            <p class="mb-0 small text-muted">Configurar tu cuenta de administrador</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Usuarios Recientes -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Usuarios Registrados Recientemente</h5>
            </div>
            <div class="card-body">
                <?php
                $stmt = $db->prepare("
                    SELECT * FROM usuarios 
                    WHERE activo = 1 
                    ORDER BY fecha_registro DESC 
                    LIMIT 5
                ");
                $stmt->execute();
                $usuariosRecientes = $stmt->fetchAll();
                ?>
                
                <?php if (empty($usuariosRecientes)): ?>
                    <p class="text-muted text-center py-3">No hay usuarios registrados recientemente</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Fecha Registro</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuariosRecientes as $usuario): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $usuario['rol'] === 'administrador' ? 'danger' : 
                                                ($usuario['rol'] === 'maestro' ? 'warning' : 'info'); 
                                        ?>">
                                            <?php echo ucfirst($usuario['rol']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatearFecha($usuario['fecha_registro']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>