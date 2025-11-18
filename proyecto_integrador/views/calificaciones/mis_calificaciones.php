<?php

// 1. Definir ROOT_PATH
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}

// 2. Incluir helpers
require_once ROOT_PATH . '/includes/helpers.php';
require_once ROOT_PATH . '/includes/conexion.php';

// 3. Iniciar sesión
iniciarSesionSegura();

// 4. Verificar rol
requerirRol('estudiante');

// 5. Variables
$pageTitle = 'Mis Calificaciones';

// 6. Lógica PHP
$db = Conexion::getInstance()->getConexion();
$idEstudiante = obtenerUsuarioId();

try {
    // Obtener calificaciones del estudiante
    $stmt = $db->prepare("
        SELECT c.*, 
               a.titulo as actividad_titulo, a.tipo as actividad_tipo, a.ponderacion,
               m.nombre as materia_nombre, m.id_materia,
               u.nombre as maestro_nombre, u.apellido as maestro_apellido
        FROM calificaciones c
        INNER JOIN actividades a ON c.id_actividad = a.id_actividad
        INNER JOIN materias m ON a.id_materia = m.id_materia
        INNER JOIN usuarios u ON m.id_maestro = u.id_usuario
        INNER JOIN inscripciones i ON m.id_materia = i.id_materia AND i.id_estudiante = c.id_estudiante
        WHERE c.id_estudiante = ? AND i.estado = 'aprobado'
        ORDER BY c.fecha_calificacion DESC
    ");
    $stmt->execute(array($idEstudiante));
    $calificaciones = $stmt->fetchAll();
    
    // Calcular promedios por materia
    $stmt = $db->prepare("
        SELECT m.id_materia, m.nombre as materia_nombre,
               AVG(c.calificacion) as promedio,
               COUNT(c.id_calificacion) as total_actividades
        FROM materias m
        INNER JOIN inscripciones i ON m.id_materia = i.id_materia
        LEFT JOIN actividades a ON m.id_materia = a.id_materia
        LEFT JOIN calificaciones c ON a.id_actividad = c.id_actividad AND c.id_estudiante = ?
        WHERE i.id_estudiante = ? AND i.estado = 'aprobado'
        GROUP BY m.id_materia, m.nombre
    ");
    $stmt->execute(array($idEstudiante, $idEstudiante));
    $promediosMaterias = $stmt->fetchAll();
    
    // Promedio general
    $promedioGeneral = 0;
    $totalMaterias = count($promediosMaterias);
    if ($totalMaterias > 0) {
        $sumaPromedios = 0;
        foreach ($promediosMaterias as $materia) {
            $sumaPromedios += $materia['promedio'] ?? 0;
        }
        $promedioGeneral = round($sumaPromedios / $totalMaterias, 2);
    }
    
} catch (PDOException $e) {
    error_log("Error al cargar calificaciones: " . $e->getMessage());
    $error = "Error al cargar las calificaciones";
}

// 7. Incluir header
require_once ROOT_PATH . '/includes/header.php';
?>

<div class="row">
    <!-- Estadísticas -->
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <h3 class="text-primary"><?php echo $promedioGeneral; ?></h3>
                        <p class="text-muted mb-0">Promedio General</p>
                    </div>
                    <div class="col-md-3">
                        <h3 class="text-success"><?php echo count($calificaciones); ?></h3>
                        <p class="text-muted mb-0">Total Calificaciones</p>
                    </div>
                    <div class="col-md-3">
                        <h3 class="text-info"><?php echo $totalMaterias; ?></h3>
                        <p class="text-muted mb-0">Materias Inscritas</p>
                    </div>
                    <div class="col-md-3">
                        <h3 class="text-warning">
                            <?php echo count(array_filter($calificaciones, function($c) { return $c['calificacion'] >= 70; })); ?>
                        </h3>
                        <p class="text-muted mb-0">Aprobadas</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Promedios por Materia -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up me-2"></i>Promedios por Materia
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($promediosMaterias)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        No tienes calificaciones registradas.
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($promediosMaterias as $materia): 
                            $promedio = $materia['promedio'] ? round($materia['promedio'], 2) : 'N/A';
                            $clase = is_numeric($promedio) ? claseCalificacion($promedio) : 'text-secondary';
                        ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($materia['materia_nombre']); ?></h6>
                                        <small class="text-muted">
                                            <?php echo $materia['total_actividades']; ?> actividades
                                        </small>
                                    </div>
                                    <span class="<?php echo $clase; ?>" style="font-size: 1.5rem;">
                                        <?php echo $promedio; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Detalle de Calificaciones -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clipboard-check me-2"></i>Detalle de Calificaciones
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($calificaciones)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        No tienes calificaciones registradas.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Actividad</th>
                                    <th>Materia</th>
                                    <th>Tipo</th>
                                    <th>Calificación</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($calificaciones as $calif): 
                                    $claseCalif = claseCalificacion($calif['calificacion']);
                                ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($calif['actividad_titulo']); ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                Ponderación: <?php echo $calif['ponderacion']; ?>%
                                            </small>
                                        </td>
                                        <td><?php echo htmlspecialchars($calif['materia_nombre']); ?></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo ucfirst($calif['actividad_tipo']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="<?php echo $claseCalif; ?>" style="font-size: 1.2rem;">
                                                <?php echo $calif['calificacion']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatearFecha($calif['fecha_calificacion']); ?></td>
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
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2>Mis Calificaciones</h2>
            <a href="<?php echo BASE_URL; ?>/views/calificaciones/graficas_estudiante.php" 
               class="btn btn-primary">
                <i class="bi bi-graph-up me-2"></i>Ver Gráficas
            </a>
        </div>
    </div>
</div>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>