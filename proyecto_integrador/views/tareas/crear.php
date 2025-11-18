<?php include '../includes/header.php'; ?>

<div class="container mt-4">
    <h2>Crear Nueva Tarea</h2>
    
    <div class="card">
        <div class="card-body">
            <form method="POST" action="?controller=task&action=crear">
                <div class="mb-3">
                    <label for="materia_id" class="form-label">Materia</label>
                    <select class="form-control" id="materia_id" name="materia_id" required>
                        <option value="">Seleccionar materia...</option>
                        <?php foreach ($materias as $materia): ?>
                        <option value="<?= $materia['id'] ?>"><?= htmlspecialchars($materia['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="titulo" class="form-label">Título de la Tarea</label>
                    <input type="text" class="form-control" id="titulo" name="titulo" required>
                </div>
                
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="fecha_limite" class="form-label">Fecha Límite</label>
                            <input type="date" class="form-control" id="fecha_limite" name="fecha_limite" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="ponderacion" class="form-label">Ponderación (%)</label>
                            <input type="number" class="form-control" id="ponderacion" name="ponderacion" 
                                   min="1" max="100" required>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Crear Tarea</button>
                <a href="?controller=task&action=listar" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>