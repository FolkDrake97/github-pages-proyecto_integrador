<?php

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

require_once ROOT_PATH . '/includes/helpers.php';

iniciarSesionSegura();
requerirRol('estudiante');

$pageTitle = 'Dashboard Estudiante';

$db = Database::getInstance()->getConnection();
$idEstudiante = obtenerUsuarioId();

try {
    // Materias inscritas
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM inscripciones 
        WHERE id_estudiante = ? AND estado = 'aprobado'
    ");
    $stmt->execute([$idEstudiante]);
    $totalMaterias = $stmt->fetch()['total'];
    
    // Tareas pendientes
    $stmt = $db->prepare("
        SELECT COUNT(DISTINCT a.id_actividad) as total
        FROM actividades a
        INNER JOIN materias m ON a.id_materia = m.id_materia
        INNER JOIN inscripciones i ON m.id_materia = i.id_materia
        LEFT JOIN calificaciones c ON a.id_actividad = c.id_actividad AND c.id_estudiante = ?
        WHERE i.id_estudiante = ? 
          AND i.estado = 'aprobado'
          AND a.activa = 1
          AND a.fecha_limite >= NOW()
          AND c.id_calificacion IS NULL
    ");
    $stmt->execute([$idEstudiante, $idEstudiante]);
    $tareasPendientes = $stmt->fetch()['total'];
    
    // Promedio general
    $stmt = $db->prepare("
        SELECT AVG(c.calificacion) as promedio
        FROM calificaciones c
        WHERE c.id_estudiante = ? AND c.calificacion IS NOT NULL
    ");
    $stmt->execute([$idEstudiante]);
    $promedioGeneral = round($stmt->fetch()['promedio'] ?? 0, 2);
    
    // Solicitudes pendientes
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM inscripciones 
        WHERE id_estudiante = ? AND estado = 'pendiente'
    ");
    $stmt->execute([$idEstudiante]);
    $solicitudesPendientes = $stmt->fetch()['total'];
    
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $totalMaterias = $tareasPendientes = $promedioGeneral = $solicitudesPendientes = 0;
}

require_once ROOT_PATH . '/includes/header.php';
?>

<!-- Banner de Bienvenida -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body p-4">
                <h2><i class="fas fa-user-graduate me-2"></i>¡Bienvenido, <?php echo obtenerNombreUsuario(); ?>!</h2>
                <p class="mb-0">Aquí está el resumen de tu actividad académica - <?php echo date('d/m/Y'); ?></p>
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
                <p class="text-muted mb-0">Materias Inscritas</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-tasks fa-3x text-warning mb-2"></i>
                <h3 class="mb-1"><?php echo $tareasPendientes; ?></h3>
                <p class="text-muted mb-0">Tareas Pendientes</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-chart-line fa-3x text-success mb-2"></i>
                <h3 class="mb-1"><?php echo $promedioGeneral; ?></h3>
                <p class="text-muted mb-0">Promedio General</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-clock fa-3x text-info mb-2"></i>
                <h3 class="mb-1"><?php echo $solicitudesPendientes; ?></h3>
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
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Acciones Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <a href="<?php echo BASE_URL; ?>/views/materias/lista.php" class="btn btn-outline-primary w-100 p-4 h-100 text-decoration-none">
                            <i class="fas fa-book fa-3x d-block mb-3"></i>
                            <h5 class="mb-2">Materias Disponibles</h5>
                            <p class="mb-0 small text-muted">Buscar y solicitar inscripción a materias</p>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="<?php echo BASE_URL; ?>/views/calificaciones/mis_calificaciones.php" class="btn btn-outline-success w-100 p-4 h-100 text-decoration-none">
                            <i class="fas fa-trophy fa-3x d-block mb-3"></i>
                            <h5 class="mb-2">Mis Calificaciones</h5>
                            <p class="mb-0 small text-muted">Ver tus calificaciones y promedio</p>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="<?php echo BASE_URL; ?>/views/actividades/lista.php" class="btn btn-outline-warning w-100 p-4 h-100 text-decoration-none">
                            <i class="fas fa-tasks fa-3x d-block mb-3"></i>
                            <h5 class="mb-2">Mis Tareas</h5>
                            <p class="mb-0 small text-muted">Ver tareas pendientes y entregadas</p>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="<?php echo BASE_URL; ?>/views/usuarios/perfil.php" class="btn btn-outline-info w-100 p-4 h-100 text-decoration-none">
                            <i class="fas fa-user-cog fa-3x d-block mb-3"></i>
                            <h5 class="mb-2">Mi Perfil</h5>
                            <p class="mb-0 small text-muted">Configurar tu cuenta de estudiante</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Próximas Tareas -->
<?php if ($tareasPendientes > 0): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Próximas Tareas</h5>
            </div>
            <div class="card-body">
                <?php
                $stmt = $db->prepare("
                    SELECT a.*, m.nombre as materia_nombre
                    FROM actividades a
                    INNER JOIN materias m ON a.id_materia = m.id_materia
                    INNER JOIN inscripciones i ON m.id_materia = i.id_materia
                    LEFT JOIN calificaciones c ON a.id_actividad = c.id_actividad AND c.id_estudiante = ?
                    WHERE i.id_estudiante = ? 
                      AND i.estado = 'aprobado'
                      AND a.activa = 1
                      AND a.fecha_limite >= NOW()
                      AND c.id_calificacion IS NULL
                    ORDER BY a.fecha_limite ASC
                    LIMIT 5
                ");
                $stmt->execute([$idEstudiante, $idEstudiante]);
                $tareas = $stmt->fetchAll();
                ?>
                
                <?php if (empty($tareas)): ?>
                    <p class="text-muted text-center py-3">No tienes tareas pendientes próximas</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tarea</th>
                                    <th>Materia</th>
                                    <th>Tipo</th>
                                    <th>Fecha Límite</th>
                                    <th>Ponderación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tareas as $tarea): 
                                    $diasRestantes = diasRestantes($tarea['fecha_limite']);
                                    $urgente = $diasRestantes <= 2;
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($tarea['titulo']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($tarea['materia_nombre']); ?></td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo ucfirst($tarea['tipo']); ?></span>
                                    </td>
                                    <td class="<?php echo $urgente ? 'text-danger fw-bold' : ''; ?>">
                                        <?php echo formatearFecha($tarea['fecha_limite']); ?>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo $diasRestantes == 0 ? 'Hoy' : ($diasRestantes == 1 ? 'Mañana' : "En $diasRestantes días"); ?>
                                        </small>
                                    </td>
                                    <td><?php echo $tarea['ponderacion']; ?>%</td>
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
<?php endif; ?>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>