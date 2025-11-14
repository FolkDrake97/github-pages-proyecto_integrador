<?php
// CONFIGURACIÓN
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}

require_once ROOT_PATH . '/includes/helpers.php';
require_once ROOT_PATH . '/includes/conexion.php';
require_once ROOT_PATH . '/includes/logros.php';

iniciarSesionSegura();
requerirRol('estudiante');

$pageTitle = 'Mis Logros';

$db = Conexion::getInstance()->getConexion();
$idEstudiante = obtenerUsuarioId();
$sistemaLogros = new SistemaLogros($db);

// Verificar nuevos logros automáticamente
$logrosNuevos = $sistemaLogros->verificarLogros($idEstudiante);

// Obtener progreso completo
$progreso = $sistemaLogros->obtenerProgreso($idEstudiante);
$totalLogros = count($progreso);
$logrosObtenidos = count(array_filter($progreso, function($p) { return $p['desbloqueado']; }));

// Obtener ranking
$ranking = $sistemaLogros->obtenerRanking(10);
$miPosicion = 0;
foreach ($ranking as $i => $estudiante) {
    if ($estudiante['id_usuario'] == $idEstudiante) {
        $miPosicion = $i + 1;
        break;
    }
}

require_once ROOT_PATH . '/includes/header.php';
?>

<!-- NOTIFICACIONES DE LOGROS NUEVOS -->
<?php if (!empty($logrosNuevos)): ?>
    <?php foreach ($logrosNuevos as $logro): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <h5 class="alert-heading">
                <i class="bi bi-trophy-fill me-2"></i>¡Nuevo Logro Desbloqueado!
            </h5>
            <hr>
            <h6><?php echo htmlspecialchars($logro['nombre']); ?></h6>
            <p class="mb-0"><?php echo htmlspecialchars($logro['descripcion']); ?></p>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- ESTADÍSTICAS -->
<div class="row mb-4 g-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="bi bi-trophy fa-3x text-warning mb-2"></i>
                <h3><?php echo $logrosObtenidos; ?> / <?php echo $totalLogros; ?></h3>
                <p class="text-muted mb-0">Logros Desbloqueados</p>
                <div class="progress mt-2" style="height: 10px;">
                    <div class="progress-bar bg-warning" 
                         style="width: <?php echo ($logrosObtenidos/$totalLogros)*100; ?>%"></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="bi bi-star fa-3x text-info mb-2"></i>
                <h3><?php echo $totalLogros - $logrosObtenidos; ?></h3>
                <p class="text-muted mb-0">Logros Pendientes</p>
                <small class="text-muted">¡Sigue esforzándote!</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="bi bi-bar-chart fa-3x text-success mb-2"></i>
                <h3><?php echo $miPosicion > 0 ? "#$miPosicion" : "N/A"; ?></h3>
                <p class="text-muted mb-0">Posición en Ranking</p>
                <small class="text-muted">De <?php echo count($ranking); ?> estudiantes</small>
            </div>
        </div>
    </div>
</div>

<!-- GRID DE LOGROS Y RANKING -->
<div class="row">
    
    <!-- COLUMNA IZQUIERDA: LOGROS -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-award me-2"></i>Todos los Logros
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php foreach ($progreso as $item): 
                        $logro = $item['logro'];
                        $desbloqueado = $item['desbloqueado'];
                    ?>
                        <div class="col-md-6">
                            <div class="card h-100 <?php echo $desbloqueado ? 'border-success' : 'border-secondary'; ?>">
                                <div class="card-body">
                                    <div class="d-flex align-items-start mb-2">
                                        <div style="font-size: 2.5rem; 
                                                    filter: <?php echo $desbloqueado ? 'none' : 'grayscale(100%)'; ?>" 
                                             class="me-3">
                                            <?php echo $logro['icono'] ?? '🏆'; ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <?php echo htmlspecialchars($logro['nombre']); ?>
                                                <?php if ($desbloqueado): ?>
                                                    <span class="badge bg-success ms-2">✓</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary ms-2">🔒</span>
                                                <?php endif; ?>
                                            </h6>
                                            <p class="text-muted small mb-0">
                                                <?php echo htmlspecialchars($logro['descripcion']); ?>
                                            </p>
                                            <?php if ($desbloqueado && $item['fecha']): ?>
                                                <small class="text-success">
                                                    <i class="bi bi-calendar-check me-1"></i>
                                                    Desbloqueado: <?php echo formatearFecha($item['fecha']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- COLUMNA DERECHA: RANKING -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="bi bi-star-fill me-2"></i>Ranking Top 10
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php foreach ($ranking as $i => $estudiante): 
                        $esActual = $estudiante['id_usuario'] == $idEstudiante;
                        $medallaColor = $i == 0 ? 'text-warning' : ($i == 1 ? 'text-secondary' : ($i == 2 ? 'text-danger' : ''));
                    ?>
                        <div class="list-group-item <?php echo $esActual ? 'bg-light border-primary' : ''; ?>">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <?php if ($i < 3): ?>
                                        <span class="fs-4 me-2 <?php echo $medallaColor; ?>">
                                            <?php echo $i == 0 ? '🥇' : ($i == 1 ? '🥈' : '🥉'); ?>
                                        </span>
                                    <?php else: ?>
                                        <strong class="me-2"><?php echo $i + 1; ?>.</strong>
                                    <?php endif; ?>
                                    
                                    <div>
                                        <div class="<?php echo $esActual ? 'fw-bold text-primary' : ''; ?>">
                                            <?php echo htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellido']); ?>
                                        </div>
                                        <small class="text-muted">
                                            Promedio: <?php echo round($estudiante['promedio'] ?? 0, 1); ?>
                                        </small>
                                    </div>
                                </div>
                                <span class="badge bg-warning text-dark">
                                    <?php echo $estudiante['total_logros']; ?> 🏆
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- CONSEJOS -->
        <div class="card mt-3">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="bi bi-lightbulb me-2"></i>Cómo Desbloquear Logros
                </h6>
            </div>
            <div class="card-body">
                <ul class="small mb-0">
                    <li>Entrega tus tareas a tiempo</li>
                    <li>Mantén un promedio alto (≥90)</li>
                    <li>Obtén calificaciones perfectas</li>
                    <li>Completa todas las actividades</li>
                    <li>Mantén una racha perfecta</li>
                </ul>
            </div>
        </div>
    </div>
    
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>