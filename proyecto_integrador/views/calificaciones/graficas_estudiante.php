<?php
// 1. CONFIGURACIÓN INICIAL
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}

require_once ROOT_PATH . '/includes/helpers.php';
require_once ROOT_PATH . '/includes/conexion.php';

iniciarSesionSegura();
requerirRol('estudiante');

$pageTitle = 'Mis Gráficas Académicas';

$db = Conexion::getInstance()->getConexion();
$idEstudiante = obtenerUsuarioId();

// 2. CONSULTAS PARA OBTENER DATOS
try {
    // Gráfica 1: Distribución de calificaciones
    $stmt = $db->prepare("
        SELECT 
            COUNT(CASE WHEN calificacion >= 90 THEN 1 END) as excelente,
            COUNT(CASE WHEN calificacion >= 80 AND calificacion < 90 THEN 1 END) as bien,
            COUNT(CASE WHEN calificacion >= 70 AND calificacion < 80 THEN 1 END) as regular,
            COUNT(CASE WHEN calificacion < 70 THEN 1 END) as bajo
        FROM calificaciones
        WHERE id_estudiante = ?
    ");
    $stmt->execute([$idEstudiante]);
    $distribucion = $stmt->fetch();
    
    // Gráfica 2: Promedios por materia
    $stmt = $db->prepare("
        SELECT m.nombre as materia,
               AVG(c.calificacion) as promedio
        FROM calificaciones c
        INNER JOIN actividades a ON c.id_actividad = a.id_actividad
        INNER JOIN materias m ON a.id_materia = m.id_materia
        WHERE c.id_estudiante = ?
        GROUP BY m.id_materia, m.nombre
        ORDER BY promedio DESC
    ");
    $stmt->execute([$idEstudiante]);
    $promedios = $stmt->fetchAll();
    
    // Gráfica 3: Estado de tareas
    $stmt = $db->prepare("
        SELECT 
            COUNT(CASE WHEN c.calificacion IS NOT NULL THEN 1 END) as calificadas,
            COUNT(CASE WHEN c.calificacion IS NULL AND a.fecha_limite >= NOW() THEN 1 END) as pendientes,
            COUNT(CASE WHEN c.calificacion IS NULL AND a.fecha_limite < NOW() THEN 1 END) as vencidas
        FROM actividades a
        INNER JOIN materias m ON a.id_materia = m.id_materia
        INNER JOIN inscripciones i ON m.id_materia = i.id_materia
        LEFT JOIN calificaciones c ON a.id_actividad = c.id_actividad AND c.id_estudiante = ?
        WHERE i.id_estudiante = ? AND i.estado = 'aprobado' AND a.activa = 1
    ");
    $stmt->execute([$idEstudiante, $idEstudiante]);
    $tareas = $stmt->fetch();
    
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $error = "Error al cargar datos para gráficas";
}

require_once ROOT_PATH . '/includes/header.php';
?>

<!-- 3. HTML: ESTRUCTURA DE LAS GRÁFICAS -->
<div class="row mb-3">
    <div class="col-12">
        <a href="<?php echo BASE_URL; ?>/views/calificaciones/mis_calificaciones.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver a Calificaciones
        </a>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="row g-4">
    
    <!-- GRÁFICA 1: Distribución (Dona) -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-pie-chart me-2"></i>Distribución de Calificaciones
                </h5>
            </div>
            <div class="card-body">
                <canvas id="graficoDistribucion" height="250"></canvas>
            </div>
        </div>
    </div>
    
    <!-- GRÁFICA 2: Estado de Tareas (Barras Horizontales) -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="bi bi-list-check me-2"></i>Estado de Tareas
                </h5>
            </div>
            <div class="card-body">
                <canvas id="graficoTareas" height="250"></canvas>
            </div>
        </div>
    </div>
    
    <!-- GRÁFICA 3: Promedios por Materia (Barras) -->
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="bi bi-bar-chart me-2"></i>Promedios por Materia
                </h5>
            </div>
            <div class="card-body">
                <canvas id="graficoPromedios" height="100"></canvas>
            </div>
        </div>
    </div>
    
</div>

<!-- 4. JAVASCRIPT: CÓDIGO DE LAS GRÁFICAS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // ========================================
    // GRÁFICA 1: DISTRIBUCIÓN (Dona/Pie)
    // ========================================
    const ctxDistribucion = document.getElementById('graficoDistribucion').getContext('2d');
    
    new Chart(ctxDistribucion, {
        type: 'doughnut', // Cambiar a 'pie' si prefieres gráfico de pastel completo
        data: {
            labels: ['Excelente (90-100)', 'Bien (80-89)', 'Regular (70-79)', 'Bajo (<70)'],
            datasets: [{
                label: 'Cantidad de Calificaciones',
                data: [
                    <?php echo $distribucion['excelente'] ?? 0; ?>,
                    <?php echo $distribucion['bien'] ?? 0; ?>,
                    <?php echo $distribucion['regular'] ?? 0; ?>,
                    <?php echo $distribucion['bajo'] ?? 0; ?>
                ],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',   // Verde - Excelente
                    'rgba(23, 162, 184, 0.8)',  // Azul - Bien
                    'rgba(255, 193, 7, 0.8)',   // Amarillo - Regular
                    'rgba(220, 53, 69, 0.8)'    // Rojo - Bajo
                ],
                borderColor: [
                    'rgb(40, 167, 69)',
                    'rgb(23, 162, 184)',
                    'rgb(255, 193, 7)',
                    'rgb(220, 53, 69)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            let value = context.parsed || 0;
                            let total = context.dataset.data.reduce((a, b) => a + b, 0);
                            let percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
    
    // ========================================
    // GRÁFICA 2: ESTADO DE TAREAS (Barras Horizontales)
    // ========================================
    const ctxTareas = document.getElementById('graficoTareas').getContext('2d');
    
    new Chart(ctxTareas, {
        type: 'bar',
        data: {
            labels: ['Calificadas', 'Pendientes', 'Vencidas'],
            datasets: [{
                label: 'Cantidad',
                data: [
                    <?php echo $tareas['calificadas'] ?? 0; ?>,
                    <?php echo $tareas['pendientes'] ?? 0; ?>,
                    <?php echo $tareas['vencidas'] ?? 0; ?>
                ],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(220, 53, 69, 0.8)'
                ],
                borderColor: [
                    'rgb(40, 167, 69)',
                    'rgb(255, 193, 7)',
                    'rgb(220, 53, 69)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            indexAxis: 'y', // Barras horizontales
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.x + ' tarea(s)';
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    
    // ========================================
    // GRÁFICA 3: PROMEDIOS POR MATERIA (Barras Verticales)
    // ========================================
    const ctxPromedios = document.getElementById('graficoPromedios').getContext('2d');
    
    new Chart(ctxPromedios, {
        type: 'bar',
        data: {
            labels: [
                <?php 
                foreach ($promedios as $p) {
                    echo "'" . addslashes($p['materia']) . "',";
                } 
                ?>
            ],
            datasets: [{
                label: 'Promedio',
                data: [
                    <?php 
                    foreach ($promedios as $p) {
                        echo round($p['promedio'], 2) . ",";
                    } 
                    ?>
                ],
                backgroundColor: 'rgba(102, 126, 234, 0.6)',
                borderColor: 'rgb(102, 126, 234)',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Promedio: ' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        stepSize: 10,
                        callback: function(value) {
                            return value;
                        }
                    }
                }
            }
        }
    });
    
});
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>