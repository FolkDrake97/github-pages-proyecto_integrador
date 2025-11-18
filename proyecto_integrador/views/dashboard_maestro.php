<?php

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

require_once ROOT_PATH . '/includes/helpers.php';

iniciarSesionSegura();
requerirRol('maestro');

$pageTitle = 'Dashboard Maestro';

$db = Database::getInstance()->getConnection();
$idMaestro = obtenerUsuarioId();

try {
    // Total materias
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM materias 
        WHERE id_maestro = ? AND activa = 1
    ");
    $stmt->execute([$idMaestro]);
    $totalMaterias = $stmt->fetch()['total'];
    
    // Solicitudes pendientes
    $stmt = $db->prepare("
        SELECT COUNT(*) as total
        FROM inscripciones i
        INNER JOIN materias m ON i.id_materia = m.id_materia
        WHERE m.id_maestro = ? AND i.estado = 'pendiente'
    ");
    $stmt->execute([$idMaestro]);
    $solicitudesPendientes = $stmt->fetch()['total'];
    
    // Total estudiantes
    $stmt = $db->prepare("
        SELECT COUNT(DISTINCT i.id_estudiante) as total
        FROM inscripciones i
        INNER JOIN materias m ON i.id_materia = m.id_materia
        WHERE m.id_maestro = ? AND i.estado = 'aprobado'
    ");
    $stmt->execute([$idMaestro]);
    $totalEstudiantes = $stmt->fetch()['total'];
    
    // Pendientes calificar
    $stmt = $db->prepare("
        SELECT COUNT(DISTINCT c.id_calificacion) as total
        FROM calificaciones c
        INNER JOIN actividades a ON c.id_actividad = a.id_actividad
        INNER JOIN materias m ON a.id_materia = m.id_materia
        WHERE m.id_maestro = ?
          AND c.calificacion IS NULL
          AND c.fecha_entrega IS NOT NULL
    ");
    $stmt->execute([$idMaestro]);
    $pendientesCalificar = $stmt->fetch()['total'] ?? 0;
    
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $totalMaterias = $solicitudesPendientes = $totalEstudiantes = $pendientesCalificar = 0;
}

require_once ROOT_PATH . '/includes/header.php';
?>

<!-- Banner de Bienvenida -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body p-4">
                <h2><i class="fas fa-chalkboard-teacher me-2"></i>¡Bienvenido, Profesor <?php echo obtenerNombreUsuario(); ?>!</h2>
                <p class="mb-0">Panel de control para gestionar tus materias y estudiantes - <?php echo date('d/m/Y'); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas Principales -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-book fa-3x text-primary mb-2"></i>
                <h3 class="mb-1"><?php echo $totalMaterias; ?></h3>
                <p class="text-muted mb-0">Mis Materias</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-user-clock fa-3x text-warning mb-2"></i>
                <h3 class="mb-1"><?php echo $solicitudesPendientes; ?></h3>
                <p class="text-muted mb-0">Solicitudes Pendientes</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-users fa-3x text-success mb-2"></i>
                <h3 class="mb-1"><?php echo $totalEstudiantes; ?></h3>
                <p class="text-muted mb-0">Estudiantes Activos</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-clipboard-check fa-3x text-danger mb-2"></i>
                <h3 class="mb-1"><?php echo $pendientesCalificar; ?></h3>
                <p class="text-muted mb-0">Por Calificar</p>
            </div>
        </div>
    </div>
</div>

<!-- Acciones Rápidas -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Acciones Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <a href="<?php echo BASE_URL; ?>/views/materias/lista.php" class="btn btn-outline-primary w-100 p-4 h-100 text-decoration-none">
                            <i class="fas fa-book fa-3x d-block mb-3"></i>
                            <h5 class="mb-2">Mis Materias</h5>
                            <p class="mb-0 small text-muted">Ver y gestionar tus materias asignadas</p>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="<?php echo BASE_URL; ?>/views/inscripciones/solicitudes.php" class="btn btn-outline-warning w-100 p-4 h-100 text-decoration-none">
                            <i class="fas fa-user-check fa-3x d-block mb-3"></i>
                            <h5 class="mb-2">Solicitudes</h5>
                            <p class="mb-0 small text-muted">Aprobar o rechazar inscripciones</p>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="<?php echo BASE_URL; ?>/views/actividades/lista.php" class="btn btn-outline-info w-100 p-4 h-100 text-decoration-none">
                            <i class="fas fa-tasks fa-3x d-block mb-3"></i>
                            <h5 class="mb-2">Actividades</h5>
                            <p class="mb-0 small text-muted">Crear y gestionar tareas y exámenes</p>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="<?php echo BASE_URL; ?>/views/calificaciones/registrar.php" class="btn btn-outline-success w-100 p-4 h-100 text-decoration-none">
                            <i class="fas fa-clipboard-check fa-3x d-block mb-3"></i>
                            <h5 class="mb-2">Calificar</h5>
                            <p class="mb-0 small text-muted">Registrar calificaciones de actividades</p>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="<?php echo BASE_URL; ?>/views/usuarios/perfil.php" class="btn btn-outline-secondary w-100 p-4 h-100 text-decoration-none">
                            <i class="fas fa-user-cog fa-3x d-block mb-3"></i>
                            <h5 class="mb-2">Mi Perfil</h5>
                            <p class="mb-0 small text-muted">Configurar tu cuenta de maestro</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Solicitudes Recientes -->
<?php if ($solicitudesPendientes > 0): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Solicitudes Recientes</h5>
            </div>
            <div class="card-body">
                <?php
                $stmt = $db->prepare("
                    SELECT i.*, u.nombre, u.apellido, m.nombre as materia_nombre
                    FROM inscripciones i
                    INNER JOIN usuarios u ON i.id_estudiante = u.id_usuario
                    INNER JOIN materias m ON i.id_materia = m.id_materia
                    WHERE m.id_maestro = ? AND i.estado = 'pendiente'
                    ORDER BY i.fecha_solicitud DESC
                    LIMIT 5
                ");
                $stmt->execute([$idMaestro]);
                $solicitudes = $stmt->fetchAll();
                ?>
                
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Estudiante</th>
                                <th>Materia</th>
                                <th>Fecha</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($solicitudes as $sol): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sol['nombre'] . ' ' . $sol['apellido']); ?></td>
                                <td><?php echo htmlspecialchars($sol['materia_nombre']); ?></td>
                                <td><?php echo formatearFecha($sol['fecha_solicitud']); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/views/inscripciones/solicitudes.php" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye me-1"></i>Ver
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>