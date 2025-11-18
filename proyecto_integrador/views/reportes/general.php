<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once ROOT_DIR . '/models/User.php';
require_once ROOT_DIR . '/models/Subject.php';
require_once ROOT_DIR . '/models/Enrollment.php';
require_once ROOT_DIR . '/models/Task.php';
require_once ROOT_DIR . '/models/Grade.php';

requireAuth();

$page_title = "Reportes Generales";

$database = new Database();
$db = $database->getConnection();

$userModel = new User($db);
$subjectModel = new Subject($db);
$enrollmentModel = new Enrollment($db);
$taskModel = new Task($db);
$gradeModel = new Grade($db);

// Obtener estadísticas según el rol
if ($_SESSION['user_role'] === 'administrador') {
    $userStats = $userModel->getUserStats();
    $subjectStats = $subjectModel->getSubjectStats();
    $enrollmentStats = $enrollmentModel->getEnrollmentStats();
    $taskStats = $taskModel->getTaskStats();
    $gradeStats = $gradeModel->getGradeStats();
    
} elseif ($_SESSION['user_role'] === 'maestro') {
    $materias_maestro = $subjectModel->getByTeacher($_SESSION['user_id']);
    $total_materias = count($materias_maestro);
}

require_once ROOT_DIR . '/includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Reportes y Estadísticas</h2>
                <div class="btn-group">
                    <a href="general.php" class="btn btn-primary active">General</a>
                    <a href="academicos.php" class="btn btn-outline-primary">Académicos</a>
                    <?php if ($_SESSION['user_role'] === 'administrador'): ?>
                    <a href="usuarios.php" class="btn btn-outline-primary">Usuarios</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tarjetas de Estadísticas -->
            <div class="row mb-4">
                <?php if ($_SESSION['user_role'] === 'administrador'): ?>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-users fa-2x mb-2"></i>
                            <div class="stat-number"><?= $userStats['total'] ?></div>
                            <div class="stat-label">Total Usuarios</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card stat-card bg-success text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-user-graduate fa-2x mb-2"></i>
                            <div class="stat-number"><?= $userStats['estudiantes'] ?></div>
                            <div class="stat-label">Estudiantes</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card stat-card bg-warning text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-chalkboard-teacher fa-2x mb-2"></i>
                            <div class="stat-number"><?= $userStats['maestros'] ?></div>
                            <div class="stat-label">Maestros</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card stat-card bg-info text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-book fa-2x mb-2"></i>
                            <div class="stat-number"><?= $subjectStats['total'] ?></div>
                            <div class="stat-label">Materias</div>
                        </div>
                    </div>
                </div>

                <?php elseif ($_SESSION['user_role'] === 'maestro'): ?>
                <div class="col-md-4 mb-3">
                    <div class="card stat-card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-book fa-2x mb-2"></i>
                            <div class="stat-number"><?= $total_materias ?></div>
                            <div class="stat-label">Mis Materias</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="card stat-card bg-success text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-tasks fa-2x mb-2"></i>
                            <div class="stat-number">15</div>
                            <div class="stat-label">Tareas Activas</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="card stat-card bg-warning text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-clock fa-2x mb-2"></i>
                            <div class="stat-number">8</div>
                            <div class="stat-label">Por Calificar</div>
                        </div>
                    </div>
                </div>

                <?php else: // Estudiante ?>
                <div class="col-md-4 mb-3">
                    <div class="card stat-card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-book fa-2x mb-2"></i>
                            <div class="stat-number">3</div>
                            <div class="stat-label">Materias Inscritas</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="card stat-card bg-success text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-2x mb-2"></i>
                            <div class="stat-number">12</div>
                            <div class="stat-label">Tareas Entregadas</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="card stat-card bg-info text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-line fa-2x mb-2"></i>
                            <div class="stat-number">8.5</div>
                            <div class="stat-label">Promedio General</div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Gráficos y Tablas -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-bar me-2"></i>Actividad Reciente
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center py-4">
                                <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                                <h5>Gráficos en Desarrollo</h5>
                                <p class="text-muted">
                                    Los gráficos interactivos estarán disponibles en la próxima actualización.
                                    Se integrará Chart.js para visualizaciones avanzadas.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-bell me-2"></i>Alertas y Recordatorios
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>2 tareas pendientes</strong> por entregar esta semana
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>3 solicitudes</strong> de inscripción pendientes
                            </div>
                            
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>5 tareas</strong> calificadas recientemente
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen de Actividad -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>Resumen de Actividad
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Actividad</th>
                                    <th>Detalles</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?= date('d/m/Y') ?></td>
                                    <td>Inicio de sesión</td>
                                    <td>Acceso al sistema</td>
                                    <td><span class="badge bg-success">Completado</span></td>
                                </tr>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime('-1 day')) ?></td>
                                    <td>Entrega de tarea</td>
                                    <td>Matemáticas - Ejercicios prácticos</td>
                                    <td><span class="badge bg-warning">Pendiente calificación</span></td>
                                </tr>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime('-2 days')) ?></td>
                                    <td>Nueva tarea asignada</td>
                                    <td>Ciencias - Proyecto final</td>
                                    <td><span class="badge bg-info">En progreso</span></td>
                                </tr>
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