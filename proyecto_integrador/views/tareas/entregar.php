<?php include '../includes/header.php'; ?>

<div class="container mt-4">
    <h2>Entregar Tarea</h2>
    
    <?php 
    // Obtener datos de la tarea
    $tarea_id = $_GET['id'] ?? null;
    if (!$tarea_id) {
        echo "<div class='alert alert-danger'>Tarea no especificada</div>";
        include '../includes/footer.php';
        exit;
    }
    
    // Aquí deberías cargar los datos de la tarea desde el modelo
    // $tarea = $this->taskModel->getById($tarea_id);
    ?>
    
    <div class="card">
        <div class="card-body">
            <form method="POST" action="?controller=task&action=entregar" enctype="multipart/form-data">
                <input type="hidden" name="tarea_id" value="<?= $tarea_id ?>">
                
                <div class="mb-3">
                    <label class="form-label">Título de la Tarea</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($tarea['titulo'] ?? 'Tarea') ?>" readonly>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea class="form-control" rows="4" readonly><?= htmlspecialchars($tarea['descripcion'] ?? '') ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="archivo" class="form-label">Subir Archivo</label>
                    <input type="file" class="form-control" id="archivo" name="archivo" accept=".pdf,.doc,.docx,.zip" required>
                    <div class="form-text">Formatos permitidos: PDF, Word, ZIP (Máximo 10MB)</div>
                </div>
                
                <div class="mb-3">
                    <label for="comentarios" class="form-label">Comentarios (opcional)</label>
                    <textarea class="form-control" id="comentarios" name="comentarios" rows="3" 
                              placeholder="Agrega cualquier comentario sobre tu entrega..."></textarea>
                </div>
                
                <div class="alert alert-info">
                    <strong>Fecha límite:</strong> <?= date('d/m/Y', strtotime($tarea['fecha_limite'] ?? 'now')) ?>
                </div>
                
                <button type="submit" class="btn btn-success">Entregar Tarea</button>
                <a href="?controller=task&action=listar" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>