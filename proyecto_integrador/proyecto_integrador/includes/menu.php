<?php

$currentPage = basename($_SERVER['PHP_SELF']);
$rol = obtenerRol();

// Menú para Administrador
if ($rol === 'administrador') {
    $menuItems = [
        ['url' => 'views/dashboard_administrador.php', 'icon' => 'bi-speedometer2', 'text' => 'Dashboard'],
        ['url' => 'views/usuarios/lista.php', 'icon' => 'bi-people', 'text' => 'Usuarios'],
        ['url' => 'views/materias/lista.php', 'icon' => 'bi-book', 'text' => 'Materias'],
    ];
}
// Menú para Maestro
elseif ($rol === 'maestro') {
    $menuItems = [
        ['url' => 'views/dashboard_maestro.php', 'icon' => 'bi-speedometer2', 'text' => 'Dashboard'],
        ['url' => 'views/materias/lista.php', 'icon' => 'bi-book', 'text' => 'Mis Materias'],
        ['url' => 'views/inscripciones/solicitudes.php', 'icon' => 'bi-person-check', 'text' => 'Solicitudes'],
        ['url' => 'views/actividades/lista.php', 'icon' => 'bi-list-task', 'text' => 'Actividades'],
        ['url' => 'views/calificaciones/registrar.php', 'icon' => 'bi-clipboard-check', 'text' => 'Calificaciones'],
    ];
}
// Menú para Estudiante
elseif ($rol === 'estudiante') {
    $menuItems = [
        ['url' => 'views/dashboard_estudiante.php', 'icon' => 'bi-speedometer2', 'text' => 'Dashboard'],
        ['url' => 'views/materias/lista.php', 'icon' => 'bi-book', 'text' => 'Materias'],
        ['url' => 'views/actividades/lista.php', 'icon' => 'bi-list-task', 'text' => 'Mis Tareas'],
        ['url' => 'views/calificaciones/mis_calificaciones.php', 'icon' => 'bi-trophy', 'text' => 'Calificaciones'],
        ['url' => 'views/calificaciones/graficas_estudiante.php', 'icon' => 'bi-graph-up', 'text' => 'Mis Gráficas'],
        ['url' => 'views/logros/mis_logros.php', 'icon' => 'bi-award', 'text' => 'Mis Logros'],
    ];
}
?>

<nav class="sidebar-menu">
    <?php foreach ($menuItems as $item): 
        $isActive = strpos($_SERVER['REQUEST_URI'], $item['url']) !== false;
        $activeClass = $isActive ? 'active' : '';
    ?>
        <a href="<?php echo BASE_URL . '/' . $item['url']; ?>" class="menu-item <?php echo $activeClass; ?>">
            <i class="bi <?php echo $item['icon']; ?>"></i>
            <span><?php echo $item['text']; ?></span>
        </a>
    <?php endforeach; ?>
    
    <hr style="border-color: rgba(255,255,255,0.1); margin: 1rem 0;">
    
    <a href="<?php echo BASE_URL; ?>/views/usuarios/perfil.php" class="menu-item">
        <i class="bi bi-gear"></i>
        <span>Configuración</span>
    </a>
    
    <a href="<?php echo BASE_URL; ?>/logout.php" class="menu-item">
        <i class="bi bi-box-arrow-right"></i>
        <span>Cerrar Sesión</span>
    </a>
</nav>