<?php

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}

require_once ROOT_PATH . '/includes/helpers.php';

iniciarSesionSegura();

$rol = obtenerRol();

// Redirigir según el rol
if ($rol === 'maestro') {
    // Ya existe la vista para maestro en el archivo actual
    require_once __DIR__ . '/lista_maestro.php';
    exit;
}

// Vista para ESTUDIANTE
requerirRol('estudiante');

$pageTitle = 'Mis Actividades';

$db = Database::getInstance()->getConnection();
$idEstudiante = obtenerUsuarioId();

try {
    // Obtener actividades del estudiante
    $stmt = $db->prepare("
        SELECT a.*, 
               m.nombre as materia_nombre,
               c.calificacion, 
               c.fecha_entrega,
               c.comentarios,
               u.nombre as maestro_nombre,
               u.apellido as maestro_apellido,
               CASE 
                   WHEN c.calificacion IS NOT NULL THEN 'calificada'
                   WHEN c.fecha_entrega IS NOT NULL THEN 'entregada'
                   WHEN a.fecha_limite < NOW() THEN 'vencida'
                   ELSE 'pendiente'
               END as estado_actividad
        FROM actividades a
        INNER JOIN materias m ON a.id_materia = m.id_materia
        INNER JOIN inscripciones i ON m.id_materia = i.id_materia
        INNER JOIN usuarios u ON m.id_maestro = u.id_usuario
        LEFT JOIN calificaciones c ON a.id_actividad = c.id_actividad AND c.id_estudiante = ?
        WHERE i.id_estudiante = ? AND i.estado = 'aprobado' AND a.activa = 1
        ORDER BY 
            CASE 
                WHEN c.calificacion IS NOT NULL THEN 4
                WHEN c.fecha_entrega IS NOT NULL THEN 3
                WHEN a.fecha_limite < NOW() THEN 2
                ELSE 1
            END,
            a.fecha_limite ASC
    ");
    $stmt->execute([$idEstudiante, $idEstudiante]);
    $actividades = $stmt->fetchAll();
    
    // Estadísticas
    $pendientes = count(array_filter($actividades, fn($a) => $a['estado_actividad'] === 'pendiente'));
    $entregadas = count(array_filter($actividades, fn($a) => $a['estado_actividad'] === 'entregada'));
    $calificadas = count(array_filter($actividades, fn($a) => $a['estado_actividad'] === 'calificada'));
    $vencidas = count(array_filter($actividades, fn($a) => $a['estado_actividad'] === 'vencida'));
    
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $error = "Error al cargar las actividades";
}

require_once ROOT_PATH . '/includes/header.php';
?>

<div class="row mb-4 g-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-warning border-4">
            <div class="card-body text-center">
                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                <h3><?php echo $pendientes; ?></h3>
                <p class="text-muted mb-0">Pendientes</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-info border-4">
            <div class="card-body text-center">
                <i class="fas fa-upload fa-2x text-info mb-2"></i>
                <h3><?php echo $entregadas; ?></h3>
                <p class="text-muted mb-0">Entregadas</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-success border-4">
            <div class="card-body text-center">
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                <h3><?php echo $calificadas; ?></h3>
                <p class="text-muted mb-0">Calificadas</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-danger border-4">
            <div class="card-body text-center">
                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                <h3><?php echo $vencidas; ?></h3>
                <p class="text-muted mb-0">Vencidas</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-list-check me-2"></i>Mis Actividades y Tareas
        </h5>
    </div>

    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($actividades)): ?>
            <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <h5>No tienes actividades asignadas</h5>
                <p class="text-muted">Las actividades aparecerán aquí cuando tus maestros las creen.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Actividad</th>
                            <th>Materia</th>
                            <th>Tipo</th>
                            <th>Fecha Límite</th>
                            <th>Ponderación</th>
                            <th>Estado</th>
                            <th>Calificación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($actividades as $actividad): 
                            $diasRestantes = diasRestantes($actividad['fecha_limite']);
                            $urgente = $diasRestantes <= 2 && $diasRestantes >= 0;
                            
                            $estadoClases = [
                                'pendiente' => 'warning',
                                'entregada' => 'info',
                                'calificada' => 'success',
                                'vencida' => 'danger'
                            ];
                            $estadoTextos = [
                                'pendiente' => 'Pendiente',
                                'entregada' => 'Entregada',
                                'calificada' => 'Calificada',
                                'vencida' => 'Vencida'
                            ];
                        ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($actividad['titulo']); ?></strong>
                                    <?php if ($actividad['descripcion']): ?>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars(substr($actividad['descripcion'], 0, 50)); ?>
                                            <?php echo strlen($actividad['descripcion']) > 50 ? '...' : ''; ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($actividad['materia_nombre']); ?>
                                    <br>
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars($actividad['maestro_nombre'] . ' ' . $actividad['maestro_apellido']); ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo ucfirst($actividad['tipo']); ?></span>
                                </td>
                                <td>
                                    <span class="<?php echo $urgente ? 'text-danger fw-bold' : ''; ?>">
                                        <?php echo formatearFecha($actividad['fecha_limite']); ?>
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        <?php 
                                        if ($diasRestantes < 0) {
                                            echo '<span class="text-danger">Vencida</span>';
                                        } elseif ($diasRestantes == 0) {
                                            echo '<span class="text-warning">Hoy</span>';
                                        } elseif ($diasRestantes == 1) {
                                            echo 'Mañana';
                                        } else {
                                            echo "En $diasRestantes días";
                                        }
                                        ?>
                                    </small>
                                </td>
                                <td><?php echo $actividad['ponderacion']; ?>%</td>
                                <td>
                                    <span class="badge bg-<?php echo $estadoClases[$actividad['estado_actividad']]; ?>">
                                        <?php echo $estadoTextos[$actividad['estado_actividad']]; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($actividad['calificacion'] !== null): ?>
                                        <span class="<?php echo claseCalificacion($actividad['calificacion']); ?>" style="font-size: 1.1rem;">
                                            <?php echo $actividad['calificacion']; ?>
                                        </span>
                                        <?php if ($actividad['comentarios']): ?>
                                            <br>
                                            <small class="text-muted" title="<?php echo htmlspecialchars($actividad['comentarios']); ?>">
                                                <i class="bi bi-chat-text"></i>
                                            </small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>