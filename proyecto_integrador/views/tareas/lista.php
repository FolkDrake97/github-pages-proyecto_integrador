<?php include '../includes/header.php'; ?>

<div class="container mt-4">
    <h2><?= $_SESSION['user_role'] === 'estudiante' ? 'Mis Tareas' : 'Tareas Asignadas' ?></h2>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Materia</th>
                            <th>Fecha Límite</th>
                            <th>Ponderación</th>
                            <?php if ($_SESSION['user_role'] === 'estudiante'): ?>
                            <th>Estado</th>
                            <th>Acciones</th>
                            <?php else: ?>
                            <th>Entregas</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tareas)): ?>
                        <tr>
                            <td colspan="6" class="text-center">No hay tareas registradas</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($tareas as $tarea): ?>
                            <tr>
                                <td><?= htmlspecialchars($tarea['titulo']) ?></td>
                                <td><?= htmlspecialchars($tarea['materia_nombre']) ?></td>
                                <td><?= date('d/m/Y', strtotime($tarea['fecha_limite'])) ?></td>
                                <td><?= $tarea['ponderacion'] ?>%</td>
                                
                                <?php if ($_SESSION['user_role'] === 'estudiante'): ?>
                                <td>
                                    <span class="badge <?= $tarea['entregada'] ? 'bg-success' : 'bg-warning' ?>">
                                        <?= $tarea['entregada'] ? 'Entregada' : 'Pendiente' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!$tarea['entregada']): ?>
                                    <a href="?controller=task&action=entregar&id=<?= $tarea['id'] ?>" 
                                       class="btn btn-primary btn-sm">Entregar</a>
                                    <?php endif; ?>
                                </td>
                                <?php else: ?>
                                <td>
                                    <a href="?controller=grade&action=calificar&tarea=<?= $tarea['id'] ?>" 
                                       class="btn btn-info btn-sm">Ver Entregas (<?= $tarea['total_entregas'] ?>)</a>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>