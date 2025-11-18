<?php

$pageTitle = 'Registrar Calificaciones';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/conexion.php';

requerirRol('maestro');

$db = Conexion::getInstance()->getConexion();
$idMaestro = obtenerUsuarioId();

// Obtener parámetros
$idActividad = $_GET['actividad'] ?? null;

try {
    // Obtener actividades del maestro
    $stmt = $db->prepare("
        SELECT a.id_actividad, a.titulo, m.nombre as materia_nombre
        FROM actividades a
        INNER JOIN materias m ON a.id_materia = m.id_materia
        WHERE m.id_maestro = ? AND a.activa = 1
        ORDER BY a.fecha_limite DESC
    ");
    $stmt->execute([$idMaestro]);
    $actividades = $stmt->fetchAll();
    
    $estudiantes = [];
    $actividadSeleccionada = null;
    
    if ($idActividad) {
        // Verificar que la actividad pertenece al maestro
        $stmt = $db->prepare("
            SELECT a.*, m.nombre as materia_nombre
            FROM actividades a
            INNER JOIN materias m ON a.id_materia = m.id_materia
            WHERE a.id_actividad = ? AND m.id_maestro = ?
        ");
        $stmt->execute([$idActividad, $idMaestro]);
        $actividadSeleccionada = $stmt->fetch();
        
        if ($actividadSeleccionada) {
            // Obtener estudiantes inscritos en la materia
            $stmt = $db->prepare("
                SELECT u.id_usuario, u.nombre, u.apellido, u.email,
                       c.calificacion, c.comentarios, c.fecha_entrega
                FROM usuarios u
                INNER JOIN inscripciones i ON u.id_usuario = i.id_estudiante
                LEFT JOIN calificaciones c ON u.id_usuario = c.id_estudiante AND c.id_actividad = ?
                WHERE i.id_materia = ? AND i.estado = 'aprobado'
                ORDER BY u.apellido, u.nombre
            ");
            $stmt->execute([$idActividad, $actividadSeleccionada['id_materia']]);
            $estudiantes = $stmt->fetchAll();
        }
    }
    
} catch (PDOException $e) {
    error_log("Error al cargar calificaciones: " . $e->getMessage());
    $error = "Error al cargar los datos";
}
?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-clipboard-check me-2"></i>Registrar Calificaciones
        </h5>
    </div>

    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Selector de Actividad -->
        <div class="row mb-4">
            <div class="col-md-8">
                <label class="form-label"><strong>Seleccionar Actividad:</strong></label>
                <select class="form-select" onchange="cambiarActividad(this.value)">
                    <option value="">-- Seleccionar actividad --</option>
                    <?php foreach ($actividades as $actividad): ?>
                        <option value="<?php echo $actividad['id_actividad']; ?>" 
                                <?php echo $idActividad == $actividad['id_actividad'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($actividad['titulo']); ?> 
                            (<?php echo htmlspecialchars($actividad['materia_nombre']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php if ($actividadSeleccionada): ?>
            <!-- Información de la Actividad -->
            <div class="alert alert-info">
                <h6><?php echo htmlspecialchars($actividadSeleccionada['titulo']); ?></h6>
                <p class="mb-1"><strong>Materia:</strong> <?php echo htmlspecialchars($actividadSeleccionada['materia_nombre']); ?></p>
                <p class="mb-1"><strong>Fecha Límite:</strong> <?php echo formatearFecha($actividadSeleccionada['fecha_limite']); ?></p>
                <p class="mb-0"><strong>Ponderación:</strong> <?php echo $actividadSeleccionada['ponderacion']; ?>%</p>
            </div>

            <!-- Formulario de Calificaciones -->
            <?php if (empty($estudiantes)): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    No hay estudiantes inscritos en esta materia.
                </div>
            <?php else: ?>
                <form id="formCalificaciones">
                    <input type="hidden" name="id_actividad" value="<?php echo $idActividad; ?>">
                    
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Estudiante</th>
                                    <th>Email</th>
                                    <th>Fecha Entrega</th>
                                    <th>Calificación (0-100)</th>
                                    <th>Comentarios</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($estudiantes as $estudiante): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellido']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($estudiante['email']); ?></td>
                                        <td>
                                            <?php if ($estudiante['fecha_entrega']): ?>
                                                <?php echo formatearFecha($estudiante['fecha_entrega']); ?>
                                            <?php else: ?>
                                                <span class="text-muted">No entregado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="width: 150px;">
                                            <input type="number" class="form-control form-control-sm" 
                                                   name="calificacion[<?php echo $estudiante['id_usuario']; ?>]"
                                                   min="0" max="100" step="0.1"
                                                   value="<?php echo $estudiante['calificacion'] ?? ''; ?>">
                                        </td>
                                        <td>
                                            <textarea class="form-control form-control-sm" 
                                                      name="comentarios[<?php echo $estudiante['id_usuario']; ?>]"
                                                      rows="1" placeholder="Comentarios..."><?php echo $estudiante['comentarios'] ?? ''; ?></textarea>
                                        </td>
                                        <td>
                                            <?php if ($estudiante['calificacion'] !== null): ?>
                                                <span class="badge bg-success">Calificado</span>
                                            <?php elseif ($estudiante['fecha_entrega']): ?>
                                                <span class="badge bg-warning">Pendiente calificar</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">No entregado</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="text-end mt-3">
                        <button type="button" class="btn btn-primary" onclick="guardarCalificaciones()">
                            <i class="bi bi-save me-2"></i>Guardar Calificaciones
                        </button>
                    </div>
                </form>
            <?php endif; ?>
            
        <?php elseif ($idActividad): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Actividad no encontrada o no tienes permisos para acceder a ella.
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                Selecciona una actividad para registrar calificaciones.
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function cambiarActividad(idActividad) {
    if (idActividad) {
        window.location.href = '<?php echo BASE_URL; ?>/views/calificaciones/registrar.php?actividad=' + idActividad;
    }
}

function guardarCalificaciones() {
    const form = document.getElementById('formCalificaciones');
    const formData = new FormData(form);
    
    fetch('<?php echo BASE_URL; ?>api/calificaciones/guardar.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.exito) {
            Notificacion.exito(data.mensaje);
            setTimeout(() => location.reload(), 1500);
        } else {
            Notificacion.error(data.mensaje);
        }
    })
    .catch(error => {
        Notificacion.error('Error de conexión');
    });
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>