<?php include '../includes/header.php'; ?>

<div class="container mt-4">
    <h2>Detalle de Tarea</h2>
    
    <?php 
    $tarea_id = $_GET['id'] ?? null;
    if (!$tarea_id) {
        echo "<div class='alert alert-danger'>Tarea no especificada</div>";
        include '../includes/footer.php';
        exit;
    }
    ?>
    
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><?= htmlspecialchars($tarea['titulo'] ?? 'Título de la tarea') ?></h4>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Materia:</strong> <?= htmlspecialchars($tarea['materia_nombre'] ?? 'Matemáticas') ?>
                </div>
                <div class="col-md-6">
                    <strong>Fecha Límite:</strong> <?= date('d/m/Y', strtotime($tarea['fecha_limite'] ?? 'now')) ?>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Ponderación:</strong> <?= $tarea['ponderacion'] ?? 0 ?>%
                </div>
                <div class="col-md-6">
                    <strong>Estado:</strong>
                    <span class="badge <?= ($tarea['entregada'] ?? false) ? 'bg-success' : 'bg-warning' ?>">
                        <?= ($tarea['entregada'] ?? false) ? 'Entregada' : 'Pendiente' ?>
                    </span>
                </div>
            </div>
            
            <div class="mb-3">
                <strong>Descripción:</strong>
                <p class="mt-2"><?= nl2br(htmlspecialchars($tarea['descripcion'] ?? 'Descripción de la tarea...')) ?></p>
            </div>
            
            <?php if ($_SESSION['user_role'] === 'estudiante' && !($tarea['entregada'] ?? false)): ?>
            <div class="alert alert-info">
                <strong>¡Atención!</strong> Esta tarea está pendiente de entrega.
            </div>
            <?php endif; ?>
            
            <?php if ($tarea['entregada'] ?? false): ?>
            <div class="alert alert-success">
                <strong>Entregado el:</strong> <?= date('d/m/Y H:i', strtotime($tarea['fecha_entrega'] ?? 'now')) ?>
                <?php if (isset($tarea['calificacion'])): ?>
                <br><strong>Calificación:</strong> <?= $tarea['calificacion'] ?>/100
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="card-footer">
            <a href="?controller=task&action=listar" class="btn btn-secondary">Volver a la lista</a>
            
            <?php if ($_SESSION['user_role'] === 'estudiante' && !($tarea['entregada'] ?? false)): ?>
            <a href="?controller=task&action=entregar&id=<?= $tarea_id ?>" class="btn btn-success">Entregar Tarea</a>
            <?php endif; ?>
            
            <?php if ($_SESSION['user_role'] === 'maestro'): ?>
            <a href="?controller=grade&action=calificar&tarea=<?= $tarea_id ?>" class="btn btn-primary">Calificar Entregas</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>