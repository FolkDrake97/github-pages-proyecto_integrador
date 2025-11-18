<?php
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}

require_once ROOT_PATH . '/includes/helpers.php';
require_once ROOT_PATH . '/includes/conexion.php';

iniciarSesionSegura();
requerirAutenticacion();

$pageTitle = 'Actividades';
$rol = obtenerRol();

$db = Conexion::getInstance()->getConexion();
$idUsuario = obtenerUsuarioId();

// LÓGICA PARA MAESTRO
if ($rol === 'maestro') {
    try {
        // Obtener materias del maestro
        $stmt = $db->prepare("
            SELECT * FROM materias 
            WHERE id_maestro = ? AND activa = 1
            ORDER BY nombre
        ");
        $stmt->execute([$idUsuario]);
        $materias = $stmt->fetchAll();
        
        // Obtener actividades del maestro
        $stmt = $db->prepare("
            SELECT a.*, 
                   m.nombre as materia_nombre,
                   COUNT(DISTINCT c.id_calificacion) as total_entregas
            FROM actividades a
            INNER JOIN materias m ON a.id_materia = m.id_materia
            LEFT JOIN calificaciones c ON a.id_actividad = c.id_actividad
            WHERE m.id_maestro = ? AND a.activa = 1
            GROUP BY a.id_actividad
            ORDER BY a.fecha_limite DESC
        ");
        $stmt->execute([$idUsuario]);
        $actividades = $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error: " . $e->getMessage());
        $error = "Error al cargar actividades";
    }
    
    require_once ROOT_PATH . '/includes/header.php';
    ?>
    
    <!-- VISTA MAESTRO -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="bi bi-list-task me-2"></i>Mis Actividades</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearActividad">
                    <i class="bi bi-plus-circle me-2"></i>Nueva Actividad
                </button>
            </div>
        </div>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($actividades)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-clipboard-x fa-3x text-muted mb-3"></i>
                <h5>No tienes actividades creadas</h5>
                <p class="text-muted">Crea tu primera actividad para tus estudiantes</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearActividad">
                    <i class="bi bi-plus-circle me-2"></i>Crear Actividad
                </button>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Título</th>
                                <th>Materia</th>
                                <th>Tipo</th>
                                <th>Fecha Límite</th>
                                <th>Ponderación</th>
                                <th>Entregas</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($actividades as $actividad): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($actividad['titulo']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($actividad['materia_nombre']); ?></td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo ucfirst($actividad['tipo']); ?>
                                    </span>
                                </td>
                                <td><?php echo formatearFecha($actividad['fecha_limite']); ?></td>
                                <td><?php echo $actividad['ponderacion']; ?>%</td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo $actividad['total_entregas']; ?> entregas
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo BASE_URL; ?>/views/actividades/detalle.php?id=<?php echo $actividad['id_actividad']; ?>" 
                                           class="btn btn-outline-info" title="Ver Detalle">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>/views/calificaciones/registrar.php?actividad=<?php echo $actividad['id_actividad']; ?>" 
                                           class="btn btn-outline-primary" title="Calificar">
                                            <i class="bi bi-clipboard-check"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- MODAL: Crear Actividad -->
    <div class="modal fade" id="modalCrearActividad" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i>Nueva Actividad
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formCrearActividad">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Materia</label>
                            <select class="form-select" name="id_materia" required>
                                <option value="">Seleccionar materia...</option>
                                <?php foreach ($materias as $materia): ?>
                                <option value="<?php echo $materia['id_materia']; ?>">
                                    <?php echo htmlspecialchars($materia['nombre']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Título</label>
                            <input type="text" class="form-control" name="titulo" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Tipo</label>
                                    <select class="form-select" name="tipo" required>
                                        <option value="tarea">Tarea</option>
                                        <option value="examen">Examen</option>
                                        <option value="proyecto">Proyecto</option>
                                        <option value="participacion">Participación</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Fecha Límite</label>
                                    <input type="datetime-local" class="form-control" name="fecha_limite" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Ponderación (%)</label>
                                    <input type="number" class="form-control" name="ponderacion" 
                                           min="1" max="100" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Crear Actividad
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    document.getElementById('formCrearActividad').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('<?php echo BASE_URL; ?>/api/actividades/crear.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.exito) {
                alert('✓ ' + data.mensaje);
                location.reload();
            } else {
                alert('✗ ' + data.mensaje);
            }
        })
        .catch(error => {
            alert('Error de conexión');
            console.error(error);
        });
    });
    </script>
    
    <?php
    require_once ROOT_PATH . '/includes/footer.php';
    exit;
}

// LÓGICA PARA ESTUDIANTE
if ($rol === 'estudiante') {
    try {
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
        $stmt->execute([$idUsuario, $idUsuario]);
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
    
    <!-- VISTA ESTUDIANTE -->
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
    
    <?php
    require_once ROOT_PATH . '/includes/footer.php';
}
?>