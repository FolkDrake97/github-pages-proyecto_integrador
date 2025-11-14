<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once ROOT_DIR . '/models/Grade.php';
require_once ROOT_DIR . '/models/Subject.php';

requireAuth();

$page_title = "Reportes Académicos";

$database = new Database();
$db = $database->getConnection();

$gradeModel = new Grade($db);
$subjectModel = new Subject($db);

require_once ROOT_DIR . '/includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Reportes Académicos</h2>
                <div class="btn-group">
                    <a href="general.php" class="btn btn-outline-primary">General</a>
                    <a href="academicos.php" class="btn btn-primary active">Académicos</a>
                    <?php if ($_SESSION['user_role'] === 'administrador'): ?>
                    <a href="usuarios.php" class="btn btn-outline-primary">Usuarios</a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($_SESSION['user_role'] === 'estudiante'): ?>
            <!-- Reportes para Estudiantes -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Mi Progreso Académico
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Calificaciones por Materia</h6>
                            <div class="list-group">
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    Matemáticas Avanzadas
                                    <span class="badge bg-primary rounded-pill">8.7</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    Ciencias Naturales
                                    <span class="badge bg-success rounded-pill">9.2</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    Programación Web
                                    <span class="badge bg-warning rounded-pill">7.8</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Estadísticas de Desempeño</h6>
                            <div class="progress mb-2" style="height: 20px;">
                                <div class="progress-bar bg-success" style="width: 85%">85% Completado</div>
                            </div>
                            <div class="progress mb-2" style="height: 20px;">
                                <div class="progress-bar bg-info" style="width: 90%">90% Entregas a tiempo</div>
                            </div>
                            <div class="progress mb-2" style="height: 20px;">
                                <div class="progress-bar bg-warning" style="width: 75%">75% Participación</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php elseif ($_SESSION['user_role'] === 'maestro'): ?>
            <!-- Reportes para Maestros -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chalkboard-teacher me-2"></i>Rendimiento por Materia
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Materia</th>
                                    <th>Estudiantes</th>
                                    <th>Promedio</th>
                                    <th>Más Alta</th>
                                    <th>Más Baja</th>
                                    <th>Aprobados</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Matemáticas Avanzadas</td>
                                    <td>25</td>
                                    <td>8.2</td>
                                    <td>9.8</td>
                                    <td>6.5</td>
                                    <td>92%</td>
                                </tr>
                                <tr>
                                    <td>Ciencias Naturales</td>
                                    <td>18</td>
                                    <td>7.9</td>
                                    <td>9.5</td>
                                    <td>5.8</td>
                                    <td>85%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php else: ?>
            <!-- Reportes para Administradores -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-university me-2"></i>Estadísticas Institucionales
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h3>85%</h3>
                                    <p class="mb-0">Tasa de Aprobación</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h3>92%</h3>
                                    <p class="mb-0">Asistencia Promedio</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h3>8.4</h3>
                                    <p class="mb-0">Promedio General</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h3>78%</h3>
                                    <p class="mb-0">Satisfacción</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Exportar Reportes -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-download me-2"></i>Exportar Reportes
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fas fa-file-pdf fa-2x text-danger mb-3"></i>
                                    <h6>Reporte PDF</h6>
                                    <p class="text-muted small">Descarga en formato PDF</p>
                                    <button class="btn btn-outline-danger btn-sm">Exportar PDF</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fas fa-file-excel fa-2x text-success mb-3"></i>
                                    <h6>Reporte Excel</h6>
                                    <p class="text-muted small">Descarga en formato Excel</p>
                                    <button class="btn btn-outline-success btn-sm">Exportar Excel</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fas fa-chart-bar fa-2x text-primary mb-3"></i>
                                    <h6>Gráficos</h6>
                                    <p class="text-muted small">Visualizaciones avanzadas</p>
                                    <button class="btn btn-outline-primary btn-sm">Ver Gráficos</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once ROOT_DIR . '/includes/footer.php';
?>