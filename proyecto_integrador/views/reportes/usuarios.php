<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once ROOT_DIR . '/models/User.php';

requireRole(['administrador']);

$page_title = "Reportes de Usuarios";

$database = new Database();
$db = $database->getConnection();
$userModel = new User($db);

$usuarios = $userModel->getAll();

require_once ROOT_DIR . '/includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Reportes de Usuarios</h2>
                <div class="btn-group">
                    <a href="general.php" class="btn btn-outline-primary">General</a>
                    <a href="academicos.php" class="btn btn-outline-primary">Académicos</a>
                    <a href="usuarios.php" class="btn btn-primary active">Usuarios</a>
                </div>
            </div>

            <!-- Resumen de Usuarios -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card stat-card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-users fa-2x mb-2"></i>
                            <div class="stat-number"><?= count($usuarios) ?></div>
                            <div class="stat-label">Total Usuarios</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card stat-card bg-success text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-user-graduate fa-2x mb-2"></i>
                            <div class="stat-number">
                                <?= count(array_filter($usuarios, fn($u) => $u['rol'] === 'estudiante')) ?>
                            </div>
                            <div class="stat-label">Estudiantes</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card stat-card bg-warning text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-chalkboard-teacher fa-2x mb-2"></i>
                            <div class="stat-number">
                                <?= count(array_filter($usuarios, fn($u) => $u['rol'] === 'maestro')) ?>
                            </div>
                            <div class="stat-label">Maestros</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card stat-card bg-info text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-user-shield fa-2x mb-2"></i>
                            <div class="stat-number">
                                <?= count(array_filter($usuarios, fn($u) => $u['rol'] === 'administrador')) ?>
                            </div>
                            <div class="stat-label">Administradores</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Usuarios -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Lista Completa de Usuarios
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre Completo</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Fecha Registro</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td><?= $usuario['id_usuario'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                                    <td>
                                        <span class="badge 
                                            <?= $usuario['rol'] === 'administrador' ? 'bg-danger' : 
                                               ($usuario['rol'] === 'maestro' ? 'bg-warning' : 'bg-info') ?>">
                                            <?= ucfirst($usuario['rol']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $usuario['activo'] ? 'success' : 'secondary' ?>">
                                            <?= $usuario['activo'] ? 'Activo' : 'Inactivo' ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once ROOT_DIR . '/includes/footer.php';
?>