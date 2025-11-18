<?php include '../includes/header.php'; ?>

<div class="container mt-4">
    <h2>Gestionar Materias</h2>
    
    <?php if ($_SESSION['user_role'] !== 'maestro' && $_SESSION['user_role'] !== 'administrador'): ?>
        <div class="alert alert-danger">No tienes permisos para acceder a esta página</div>
    <?php else: ?>
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Lista de Materias</h4>
        <a href="?controller=subject&action=crear" class="btn btn-primary">+ Nueva Materia</a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Créditos</th>
                            <th>Maestro</th>
                            <th>Estudiantes</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($materias)): ?>
                        <tr>
                            <td colspan="6" class="text-center">No hay materias registradas</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($materias as $materia): ?>
                            <tr>
                                <td><?= htmlspecialchars($materia['nombre']) ?></td>
                                <td><?= htmlspecialchars($materia['descripcion']) ?></td>
                                <td><?= $materia['creditos'] ?></td>
                                <td><?= htmlspecialchars($materia['maestro_nombre'] ?? 'No asignado') ?></td>
                                <td>
                                    <span class="badge bg-primary"><?= $materia['total_estudiantes'] ?? 0 ?> estudiantes</span>
                                </td>
                                <td>
                                    <a href="?controller=subject&action=editar&id=<?= $materia['id'] ?>" 
                                       class="btn btn-warning btn-sm">Editar</a>
                                    <a href="?controller=subject&action=eliminar&id=<?= $materia['id'] ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('¿Estás seguro de eliminar esta materia?')">Eliminar</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>