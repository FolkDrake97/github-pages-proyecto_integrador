<?php

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}

require_once ROOT_PATH . '/includes/helpers.php';

iniciarSesionSegura();
requerirRol('administrador');

$pageTitle = 'Gestión de Usuarios';

$db = Database::getInstance()->getConnection();

try {
    $stmt = $db->prepare("
        SELECT * FROM usuarios 
        WHERE activo = 1 
        ORDER BY fecha_registro DESC
    ");
    $stmt->execute();
    $usuarios = $stmt->fetchAll();
    
    // Contar por rol
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'estudiante' AND activo = 1");
    $stmt->execute();
    $totalEstudiantes = $stmt->fetch()['total'];
    
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'maestro' AND activo = 1");
    $stmt->execute();
    $totalMaestros = $stmt->fetch()['total'];
    
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'administrador' AND activo = 1");
    $stmt->execute();
    $totalAdmins = $stmt->fetch()['total'];
    
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $error = "Error al cargar usuarios";
}

require_once ROOT_PATH . '/includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2><i class="fas fa-users me-2"></i>Gestión de Usuarios</h2>
            <!-- 🆕 BOTÓN PARA ABRIR MODAL -->
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
                <i class="fas fa-user-plus me-2"></i>Nuevo Usuario
            </button>
        </div>
    </div>
</div>

<!-- ESTADÍSTICAS -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-user-graduate fa-2x text-info mb-2"></i>
                <h3><?php echo $totalEstudiantes ?? 0; ?></h3>
                <p class="text-muted mb-0">Estudiantes</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-chalkboard-teacher fa-2x text-warning mb-2"></i>
                <h3><?php echo $totalMaestros ?? 0; ?></h3>
                <p class="text-muted mb-0">Maestros</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-user-shield fa-2x text-danger mb-2"></i>
                <h3><?php echo $totalAdmins ?? 0; ?></h3>
                <p class="text-muted mb-0">Administradores</p>
            </div>
        </div>
    </div>
</div>

<!-- TABLA DE USUARIOS -->
<div class="card">
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($usuarios)): ?>
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5>No hay usuarios registrados</h5>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Fecha Registro</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo $usuario['id_usuario']; ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-2" style="width: 35px; height: 35px; font-size: 0.8rem;">
                                        <?php echo strtoupper(substr($usuario['nombre'], 0, 1)); ?>
                                    </div>
                                    <strong><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></strong>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $usuario['rol'] === 'administrador' ? 'danger' : 
                                        ($usuario['rol'] === 'maestro' ? 'warning' : 'info'); 
                                ?>">
                                    <?php echo ucfirst($usuario['rol']); ?>
                                </span>
                            </td>
                            <td><?php echo formatearFecha($usuario['fecha_registro']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $usuario['activo'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $usuario['activo'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-info" title="Ver Perfil">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ($usuario['id_usuario'] != obtenerUsuarioId()): ?>
                                    <button class="btn btn-outline-danger" 
                                            onclick="if(confirm('¿Eliminar usuario?')) alert('Funcionalidad pendiente')"
                                            title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="modalCrearUsuario" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i>Crear Nuevo Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCrearUsuario">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nombre</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Apellido</label>
                                <input type="text" class="form-control" name="apellido" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Contraseña</label>
                                <input type="password" class="form-control" name="password" 
                                       minlength="6" required>
                                <small class="text-muted">Mínimo 6 caracteres</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Rol</label>
                                <select class="form-select" name="rol" required>
                                    <option value="estudiante">Estudiante</option>
                                    <option value="maestro">Maestro</option>
                                    <option value="administrador">Administrador</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Crear Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('formCrearUsuario').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?php echo BASE_URL; ?>/api/usuarios/crear.php', {
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

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>