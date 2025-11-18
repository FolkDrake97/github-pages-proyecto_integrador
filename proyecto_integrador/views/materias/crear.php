<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once ROOT_DIR . '/models/Subject.php';
require_once ROOT_DIR . '/models/User.php';

requireRole(['administrador', 'maestro']);

$page_title = "Crear Materia";

$database = new Database();
$db = $database->getConnection();
$subject = new Subject($db);
$user = new User($db);

$error = '';
$success = '';

// Obtener lista de maestros
$maestros = $user->getTeachers();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = sanitizeInput($_POST['nombre'] ?? '');
    $descripcion = sanitizeInput($_POST['descripcion'] ?? '');
    $creditos = sanitizeInput($_POST['creditos'] ?? '');
    $id_maestro = sanitizeInput($_POST['id_maestro'] ?? '');
    
    if (empty($nombre) || empty($creditos)) {
        $error = "Nombre y créditos son obligatorios";
    } else {
        $subject->nombre = $nombre;
        $subject->descripcion = $descripcion;
        $subject->creditos = $creditos;
        $subject->id_maestro = $id_maestro ?: null;
        
        if ($subject->create()) {
            $success = "Materia creada exitosamente";
            $_POST = array();
        } else {
            $error = "Error al crear la materia";
        }
    }
}

require_once ROOT_DIR . '/includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Crear Nueva Materia</h2>
                <a href="<?= BASE_URL ?>/views/materias/lista.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver a la Lista
                </a>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre de la Materia</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required 
                                   value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                                   placeholder="Ej: Matemáticas Avanzadas">
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"
                                      placeholder="Descripción de la materia..."><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="creditos" class="form-label">Créditos</label>
                                    <input type="number" class="form-control" id="creditos" name="creditos" required 
                                           value="<?= htmlspecialchars($_POST['creditos'] ?? '') ?>"
                                           min="1" max="10" placeholder="1-10">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="id_maestro" class="form-label">Maestro Responsable</label>
                                    <select class="form-control" id="id_maestro" name="id_maestro">
                                        <option value="">Seleccionar maestro (opcional)</option>
                                        <?php foreach ($maestros as $maestro): ?>
                                        <option value="<?= $maestro['id_usuario'] ?>" 
                                                <?= ($_POST['id_maestro'] ?? '') == $maestro['id_usuario'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($maestro['nombre'] . ' ' . $maestro['apellido']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Crear Materia
                            </button>
                            <a href="<?= BASE_URL ?>/views/materias/lista.php" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once ROOT_DIR . '/includes/footer.php';
?>