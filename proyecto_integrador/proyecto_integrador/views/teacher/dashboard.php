<?php

$pageTitle = 'Dashboard Maestro';
require_once '../../includes/header.php';
require_once '../../includes/conexion.php';

requerirRol('maestro');

// LÓGICA PHP aquí para obtener datos
$db = Conexion::getInstance()->getConexion();
$idMaestro = obtenerUsuarioId();

try {
    // Consultas PHP a la base de datos
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM materias WHERE id_maestro = ?");
    $stmt->execute([$idMaestro]);
    $totalMaterias = $stmt->fetch()['total'];
    
    // Más lógica PHP...
    
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $error = "Error al cargar datos";
}
?>

<div class="container">
    <h1>Dashboard Maestro</h1>
    
    <!-- Mostrar datos PHP -->
    <div class="stat-card">
        <h3><?php echo $totalMaterias; ?></h3>
        <p>Total Materias</p>
    </div>
    
    <!-- Gráfica con Chart.js -->
    <div class="chart-container">
        <canvas id="gradesChart" width="400" height="200"></canvas>
    </div>
</div>

<!-- JavaScript embebido -->
<script>
// JavaScript para la gráfica
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('gradesChart').getContext('2d');
    
    // Aquí puedes usar datos de PHP en JavaScript
    const phpData = {
        totalMaterias: <?php echo $totalMaterias; ?>,
        // Más datos de PHP...
    };
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Materias', 'Estudiantes', 'Actividades'],
            datasets: [{
                label: 'Estadísticas',
                data: [phpData.totalMaterias, 25, 15], // Datos mezclados
                backgroundColor: ['#667eea', '#764ba2', '#f093fb']
            }]
        }
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>