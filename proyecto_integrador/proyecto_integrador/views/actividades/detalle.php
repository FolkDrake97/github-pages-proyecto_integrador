<?php

$pageTitle = 'Detalle de Actividad';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/conexion.php';

requerirRol('maestro');

$db = Conexion::getInstance()->getConexion();
$idMaestro = obtenerUsuarioId();

// Obtener ID de la actividad
$idActividad = $_GET['id'] ?? null;

if (!$idActividad) {
    redirigir('views/actividades/lista.php');
}

try {
    // Obtener información de la actividad
    $stmt = $db->prepare("
        SELECT a.*, m.nombre as materia_nombre, m.id_materia,
               u.nombre as maestro_nombre, u.apellido as maestro_apellido
        FROM actividades a
        INNER JOIN materias m ON a.id_materia = m.id_materia
        INNER JOIN usuarios u ON m.id_maestro = u.id_usuario
        WHERE a.id_actividad = ? AND m.id_maestro = ?
    ");
    $stmt->execute([$idActividad, $idMaestro]);
    $actividad = $stmt->fetch();
    
    if (!$actividad) {
        redirigir('views/actividades/lista.php');
    }
    
    // Obtener estudiantes inscritos y sus calificaciones
    $stmt = $db->prepare("
        SELECT u.id_usuario, u.nombre, u.apellido, u.email,
               c.calificacion, c.comentarios, c.fecha_entrega, c.fecha_calificacion,
               CASE 
                   WHEN c.calificacion IS NOT NULL THEN 'calificada'
                   WHEN c.fecha_entrega IS NOT NULL THEN 'entregada'
                   ELSE 'pendiente'
               END as estado
        FROM usuarios u
        INNER JOIN inscripciones i ON u.id_usuario = i.id_estudiante
        LEFT JOIN calificaciones c ON u.id_usuario = c.id_estudiante AND c.id_actividad = ?
        WHERE i.id_materia = ? AND i.estado = 'aprobado'
        ORDER BY u.apellido, u.nombre
    ");
    $stmt->execute([$idActividad, $actividad['id_materia']]);
    $estudiantes = $stmt->fetchAll();
    
    // Estadísticas
    $totalEstudiantes = count($estudiantes);
    $entregadas = count(array_filter($estudiantes, function($e) { return $e['estado'] === 'entregada'; }));
    $calificadas = count(array_filter($estudiantes, function($e) { return $e['estado'] === 'calificada'; }));
    $pendientes = $totalEstudiantes - $entregadas - $calificadas;
    
    // Promedio de calificaciones
    $calificaciones = array_filter(array_column($estudiantes, 'calificacion'));
    $promedio = $calificaciones ? round(array_sum($calificaciones) / count($calificaciones), 2) : 0;
    
} catch (PDOException $e) {
    error_log("Error al cargar actividad: " . $e->getMessage());
    $error = "Error al cargar la actividad";
}
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="bi bi-eye me-2"></i>Detalle de Actividad
        </h5>
        <div>
            <a href="<?php echo BASE_URL; ?>views/calificaciones/registrar.php?actividad=<?php echo $idActividad; ?>" 
               class="btn btn-primary btn-sm me-2">
                <i class="bi bi-clipboard-check me-1"></i>Calificar
            </a>
            <a href="<?php echo BASE_URL; ?>views/actividades/lista.php" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>

    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Información de la Actividad -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h4><?php echo htmlspecialchars($actividad['titulo']); ?></h4>
                <?php if ($actividad['descripcion']): ?>
                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($actividad['descripcion'])); ?></p>
                <?php endif; ?>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <p><strong>Materia:</strong> <?php echo htmlspecialchars($actividad['materia_nombre']); ?></p>
                        <p><strong>Tipo:</strong> <span class="badge bg-secondary"><?php echo ucfirst($actividad['tipo']); ?></span></p>
                        <p><strong>Ponderación:</strong> <?php echo $actividad['ponderacion']; ?>%</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Fecha Límite:</strong> <?php echo formatearFecha($actividad['fecha_limite']); ?></p>
                        <p><strong>Fecha Creación:</strong> <?php echo formatearFecha($actividad['fecha_creacion']); ?></p>
                        <p><strong>Estado:</strong> 
                            <span class="badge bg-<?php echo $actividad['activa'] ? 'success' : 'danger'; ?>">
                                <?php echo $actividad['activa'] ? 'Activa' : 'Inactiva'; ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h5>Estadísticas</h5>
                        <div class="row mt-3">
                            <div class="col-4">
                                <h4 class="text-primary"><?php echo $totalEstudiantes; ?></h4>
                                <small>Total</small>
                            </div>
                            <div class="col-4">
                                <h4 class="text-success"><?php echo $calificadas; ?></h4>
                                <small>Calificadas</small>
                            </div>
                            <div class="col-4">
                                <h4 class="text-warning"><?php echo $entregadas; ?></h4>
                                <small>Entregadas</small>
                            </div>
                        </div>
                        <?php if ($calificadas > 0): ?>
                            <div class="mt-3">
                                <h5 class="text-info"><?php echo $promedio; ?></h5>
                                <small>Promedio General</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Estudiantes -->
        <h5 class="mb-3">
            <i class="bi bi-people me-2"></i>Estudiantes Inscritos
            <span class="badge bg-primary"><?php echo $totalEstudiantes; ?></span>
        </h5>

        <?php if (empty($estudiantes)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                No hay estudiantes inscritos en esta materia.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <th>Email</th>
                            <th>Estado</th>
                            <th>Fecha Entrega</th>
                            <th>Calificación</th>
                            <th>Comentarios</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estudiantes as $estudiante): 
                            $claseEstado = [
                                'calificada' => 'success',
                                'entregada' => 'warning', 
                                'pendiente' => 'secondary'
                            ][$estudiante['estado']];
                            
                            $textoEstado = [
                                'calificada' => 'Calificada',
                                'entregada' => 'Entregada',
                                'pendiente' => 'Pendiente'
                            ][$estudiante['estado']];
                        ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellido']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($estudiante['email']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $claseEstado; ?>">
                                        <?php echo $textoEstado; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($estudiante['fecha_entrega']): ?>
                                        <?php echo formatearFecha($estudiante['fecha_entrega']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($estudiante['calificacion'] !== null): ?>
                                        <span class="calificacion <?php echo claseCalificacion($estudiante['calificacion']); ?>">
                                            <?php echo $estudiante['calificacion']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($estudiante['comentarios']): ?>
                                        <small class="text-muted"><?php echo htmlspecialchars($estudiante['comentarios']); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($estudiante['estado'] !== 'pendiente'): ?>
                                        <a href="<?php echo BASE_URL; ?>views/calificaciones/registrar.php?actividad=<?php echo $idActividad; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
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

<!-- Gráfico de Distribución de Calificaciones -->
<?php if ($calificadas > 0): ?>
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-bar-chart me-2"></i>Distribución de Calificaciones
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <canvas id="graficoCalificaciones" width="400" height="200"></canvas>
            </div>
            <div class="col-md-4">
                <div class="list-group">
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        Excelente (90-100)
                        <span class="badge bg-success">
                            <?php echo count(array_filter($calificaciones, function($c) { return $c >= 90; })); ?>
                        </span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        Bien (80-89)
                        <span class="badge bg-info">
                            <?php echo count(array_filter($calificaciones, function($c) { return $c >= 80 && $c < 90; })); ?>
                        </span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        Regular (70-79)
                        <span class="badge bg-warning">
                            <?php echo count(array_filter($calificaciones, function($c) { return $c >= 70 && $c < 80; })); ?>
                        </span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        Reprobado (0-69)
                        <span class="badge bg-danger">
                            <?php echo count(array_filter($calificaciones, function($c) { return $c < 70; })); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('graficoCalificaciones').getContext('2d');
    
    // Datos para el gráfico
    const calificaciones = <?php echo json_encode($calificaciones); ?>;
    
    // Agrupar calificaciones en rangos
    const rangos = {
        '90-100': calificaciones.filter(c => c >= 90).length,
        '80-89': calificaciones.filter(c => c >= 80 && c < 90).length,
        '70-79': calificaciones.filter(c => c >= 70 && c < 80).length,
        '0-69': calificaciones.filter(c => c < 70).length
    };
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: Object.keys(rangos),
            datasets: [{
                label: 'Número de Estudiantes',
                data: Object.values(rangos),
                backgroundColor: [
                    '#28a745', // Verde
                    '#17a2b8', // Azul
                    '#ffc107', // Amarillo
                    '#dc3545'  // Rojo
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>