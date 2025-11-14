<?php include '../includes/header.php'; ?>

<div class="container mt-4">
    <h2>Editar Materia</h2>
    
    <?php if ($_SESSION['user_role'] !== 'maestro' && $_SESSION['user_role'] !== 'administrador'): ?>
        <div class="alert alert-danger">No tienes permisos para acceder a esta página</div>
    <?php else: ?>
    
    <?php 
    $materia_id = $_GET['id'] ?? null;
    if (!$materia_id) {
        echo "<div class='alert alert-danger'>Materia no especificada</div>";
        include '../includes/footer.php';
        exit;
    }
    
    // Aquí deberías cargar los datos de la materia desde el modelo
    // $materia = $this->subjectModel->getById($materia_id);
    ?>
    
    <div class="card">
        <div class="card-body">
            <form method="POST" action="?controller=subject&action=actualizar">
                <input type="hidden" name="id" value="<?= $materia_id ?>">
                
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre de la Materia</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" 
                           value="<?= htmlspecialchars($materia['nombre'] ?? '') ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= htmlspecialchars($materia['descripcion'] ?? '') ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="creditos" class="form-label">Créditos</label>
                            <input type="number" class="form-control" id="creditos" name="creditos" 
                                   value="<?= $materia['creditos'] ?? 1 ?>" min="1" max="10" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="id_maestro" class="form-label">Maestro Responsable</label>
                            <select class="form-control" id="id_maestro" name="id_maestro" required>
                                <option value="">Seleccionar maestro...</option>
                                <option value="1" <?= ($materia['id_maestro'] ?? '') == 1 ? 'selected' : '' ?>>Profesor Matemáticas</option>
                                <option value="2" <?= ($materia['id_maestro'] ?? '') == 2 ? 'selected' : '' ?>>Profesor Ciencias</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="activa" name="activa" 
                           <?= ($materia['activa'] ?? 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="activa">Materia activa</label>
                </div>
                
                <button type="submit" class="btn btn-primary">Actualizar Materia</button>
                <a href="?controller=subject&action=gestionar" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
    
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>