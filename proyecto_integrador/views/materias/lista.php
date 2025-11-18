<?php
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}

require_once ROOT_PATH . '/includes/helpers.php';

iniciarSesionSegura();
requerirAutenticacion();

$pageTitle = 'Materias';
$rol = obtenerRol();

$db = Database::getInstance()->getConnection();

try {
    if ($rol === 'estudiante') {
        $stmt = $db->prepare("
            SELECT m.*, 
                   u.nombre as maestro_nombre, 
                   u.apellido as maestro_apellido,
                   i.estado as inscripcion_estado
            FROM materias m
            LEFT JOIN usuarios u ON m.id_maestro = u.id_usuario
            LEFT JOIN inscripciones i ON m.id_materia = i.id_materia 
                AND i.id_estudiante = ?
            WHERE m.activa = 1
            ORDER BY m.nombre
        ");
        $stmt->execute([obtenerUsuarioId()]);
    } elseif ($rol === 'maestro') {
        $stmt = $db->prepare("
            SELECT m.*, 
                   u.nombre as maestro_nombre, 
                   u.apellido as maestro_apellido,
                   COUNT(DISTINCT i.id_estudiante) as total_estudiantes
            FROM materias m
            LEFT JOIN usuarios u ON m.id_maestro = u.id_usuario
            LEFT JOIN inscripciones i ON m.id_materia = i.id_materia 
                AND i.estado = 'aprobado'
            WHERE m.id_maestro = ? AND m.activa = 1
            GROUP BY m.id_materia
            ORDER BY m.nombre
        ");
        $stmt->execute([obtenerUsuarioId()]);
    } else {
        $stmt = $db->prepare("
            SELECT m.*, 
                   u.nombre as maestro_nombre, 
                   u.apellido as maestro_apellido,
                   COUNT(DISTINCT i.id_estudiante) as total_estudiantes
            FROM materias m
            LEFT JOIN usuarios u ON m.id_maestro = u.id_usuario
            LEFT JOIN inscripciones i ON m.id_materia = i.id_materia 
                AND i.estado = 'aprobado'
            WHERE m.activa = 1
            GROUP BY m.id_materia
            ORDER BY m.nombre
        ");
        $stmt->execute();
    }
    
    $materias = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $error = "Error al cargar las materias";
}

require_once ROOT_PATH . '/includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2>
                <?php 
                if ($rol === 'maestro') {
                    echo '<i class="fas fa-book me-2"></i>Mis Materias';
                } else {
                    echo '<i class="fas fa-book me-2"></i>Materias Disponibles';
                }
                ?>
            </h2>
            <?php if ($rol === 'administrador'): ?>
                <a href="#" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Nueva Materia
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
    </div>
<?php endif; ?>

<?php if (empty($materias)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-book fa-3x text-muted mb-3"></i>
            <h5>No hay materias disponibles</h5>
            <p class="text-muted">
                <?php if ($rol === 'maestro'): ?>
                    No tienes materias asignadas actualmente.
                <?php else: ?>
                    No hay materias registradas en el sistema.
                <?php endif; ?>
            </p>
        </div>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($materias as $materia): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-primary">
                        <?php echo htmlspecialchars($materia['nombre']); ?>
                    </h5>
                    
                    <p class="card-text text-muted">
                        <?php echo htmlspecialchars($materia['descripcion'] ?? 'Sin descripción'); ?>
                    </p>
                    
                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="fas fa-star me-1"></i>
                            <strong>Créditos:</strong> <?php echo $materia['creditos']; ?>
                        </small>
                        <br>
                        <small class="text-muted">
                            <i class="fas fa-chalkboard-teacher me-1"></i>
                            <strong>Maestro:</strong> 
                            <?php 
                            if ($materia['maestro_nombre']) {
                                echo htmlspecialchars($materia['maestro_nombre'] . ' ' . $materia['maestro_apellido']);
                            } else {
                                echo 'No asignado';
                            }
                            ?>
                        </small>
                        
                        <?php if (isset($materia['total_estudiantes'])): ?>
                        <br>
                        <small class="text-muted">
                            <i class="fas fa-users me-1"></i>
                            <strong>Estudiantes:</strong> <?php echo $materia['total_estudiantes']; ?>
                        </small>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($rol === 'estudiante'): ?>
                        <?php if (isset($materia['inscripcion_estado'])): ?>
                            <?php if ($materia['inscripcion_estado'] === 'aprobado'): ?>
                                <span class="badge bg-success w-100">
                                    <i class="fas fa-check me-1"></i>Inscrito
                                </span>
                            <?php elseif ($materia['inscripcion_estado'] === 'pendiente'): ?>
                                <span class="badge bg-warning w-100">
                                    <i class="fas fa-clock me-1"></i>Pendiente de aprobación
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger w-100">
                                    <i class="fas fa-times me-1"></i>Solicitud rechazada
                                </span>
                            <?php endif; ?>
                        <?php else: ?>
                            <button class="btn btn-primary btn-sm w-100" 
                                    onclick="solicitarInscripcion(<?php echo $materia['id_materia']; ?>)">
                                <i class="fas fa-user-plus me-1"></i>Solicitar Inscripción
                            </button>
                        <?php endif; ?>
                        
                    <?php elseif ($rol === 'maestro'): ?>
                        <a href="<?php echo BASE_URL; ?>/views/actividades/lista.php?materia=<?php echo $materia['id_materia']; ?>" 
                           class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-tasks me-1"></i>Ver Actividades
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($rol === 'estudiante'): ?>
<script>
function solicitarInscripcion(materiaId) {
    if (confirm('¿Deseas solicitar inscripción a esta materia?')) {
        fetch('<?php echo BASE_URL; ?>/api/inscripciones/solicitar.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id_materia: materiaId})
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
    }
}
</script>
<?php endif; ?>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>