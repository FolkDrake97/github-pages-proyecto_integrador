<?php

class SistemaLogros {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Verifica todos los logros de un estudiante
     * Se ejecuta después de cada acción importante (entregar tarea, recibir calificación)
     */
    public function verificarLogros($idEstudiante) {
        $logrosNuevos = [];
        
        // 1. Primera Entrega (🎯)
        if ($this->verificarPrimeraEntrega($idEstudiante)) {
            $logro = $this->otorgarLogro($idEstudiante, 1);
            if ($logro) $logrosNuevos[] = $logro;
        }
        
        // 2. Promedio Alto (⭐)
        if ($this->verificarPromedioAlto($idEstudiante)) {
            $logro = $this->otorgarLogro($idEstudiante, 2);
            if ($logro) $logrosNuevos[] = $logro;
        }
        
        // 3. Racha Perfecta (🔥)
        if ($this->verificarRachaPerfecta($idEstudiante)) {
            $logro = $this->otorgarLogro($idEstudiante, 3);
            if ($logro) $logrosNuevos[] = $logro;
        }
        
        // 4. Estudiante Constante (💪)
        if ($this->verificarConstancia($idEstudiante)) {
            $logro = $this->otorgarLogro($idEstudiante, 4);
            if ($logro) $logrosNuevos[] = $logro;
        }
        
        // 5. Excelencia Académica (🏆)
        if ($this->verificarExcelencia($idEstudiante)) {
            $logro = $this->otorgarLogro($idEstudiante, 5);
            if ($logro) $logrosNuevos[] = $logro;
        }
        
        return $logrosNuevos;
    }
    
    // =========================================
    // VERIFICACIONES INDIVIDUALES
    // =========================================
    
    /**
     * Verifica si es la primera calificación del estudiante
     */
    private function verificarPrimeraEntrega($idEstudiante) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total 
            FROM calificaciones 
            WHERE id_estudiante = ?
        ");
        $stmt->execute([$idEstudiante]);
        $total = $stmt->fetch()['total'];
        
        return $total == 1; // Solo cuando tiene exactamente 1
    }
    
    /**
     * Verifica si el promedio general es >= 90
     */
    private function verificarPromedioAlto($idEstudiante) {
        $stmt = $this->db->prepare("
            SELECT AVG(calificacion) as promedio 
            FROM calificaciones 
            WHERE id_estudiante = ?
        ");
        $stmt->execute([$idEstudiante]);
        $promedio = $stmt->fetch()['promedio'];
        
        return $promedio >= 90;
    }
    
    /**
     * Verifica si tiene 5 tareas consecutivas a tiempo
     */
    private function verificarRachaPerfecta($idEstudiante) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total
            FROM (
                SELECT c.id_calificacion
                FROM calificaciones c
                INNER JOIN actividades a ON c.id_actividad = a.id_actividad
                WHERE c.id_estudiante = ?
                  AND c.fecha_entrega <= a.fecha_limite
                ORDER BY c.fecha_entrega DESC
                LIMIT 5
            ) as ultimas_cinco
        ");
        $stmt->execute([$idEstudiante]);
        $total = $stmt->fetch()['total'];
        
        return $total >= 5;
    }
    
    /**
     * Verifica si completó todas las actividades del mes actual
     */
    private function verificarConstancia($idEstudiante) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(DISTINCT a.id_actividad) as total_actividades,
                COUNT(DISTINCT c.id_actividad) as actividades_completadas
            FROM actividades a
            INNER JOIN materias m ON a.id_materia = m.id_materia
            INNER JOIN inscripciones i ON m.id_materia = i.id_materia
            LEFT JOIN calificaciones c ON a.id_actividad = c.id_actividad 
                AND c.id_estudiante = ?
            WHERE i.id_estudiante = ?
              AND i.estado = 'aprobado'
              AND MONTH(a.fecha_limite) = MONTH(CURRENT_DATE)
              AND YEAR(a.fecha_limite) = YEAR(CURRENT_DATE)
        ");
        $stmt->execute([$idEstudiante, $idEstudiante]);
        $data = $stmt->fetch();
        
        return $data['total_actividades'] > 0 && 
               $data['total_actividades'] == $data['actividades_completadas'];
    }
    
    /**
     * Verifica si tiene al menos una calificación perfecta (100)
     */
    private function verificarExcelencia($idEstudiante) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total 
            FROM calificaciones 
            WHERE id_estudiante = ? AND calificacion = 100
        ");
        $stmt->execute([$idEstudiante]);
        $total = $stmt->fetch()['total'];
        
        return $total > 0;
    }
    
    // =========================================
    // GESTIÓN DE LOGROS
    // =========================================
    
    /**
     * Otorga un logro al estudiante si no lo tiene ya
     */
    private function otorgarLogro($idEstudiante, $idLogro) {
        // Verificar si ya tiene el logro
        $stmt = $this->db->prepare("
            SELECT * FROM usuarios_logros 
            WHERE id_usuario = ? AND id_logro = ?
        ");
        $stmt->execute([$idEstudiante, $idLogro]);
        
        if ($stmt->fetch()) {
            return null; // Ya tiene el logro
        }
        
        // Otorgar el logro
        $stmt = $this->db->prepare("
            INSERT INTO usuarios_logros (id_usuario, id_logro, fecha_obtencion)
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$idEstudiante, $idLogro]);
        
        // Obtener información del logro
        $stmt = $this->db->prepare("SELECT * FROM logros WHERE id_logro = ?");
        $stmt->execute([$idLogro]);
        
        return $stmt->fetch();
    }
    
    /**
     * Obtiene todos los logros del estudiante
     */
    public function obtenerLogrosEstudiante($idEstudiante) {
        $stmt = $this->db->prepare("
            SELECT l.*, ul.fecha_obtencion
            FROM usuarios_logros ul
            INNER JOIN logros l ON ul.id_logro = l.id_logro
            WHERE ul.id_usuario = ?
            ORDER BY ul.fecha_obtencion DESC
        ");
        $stmt->execute([$idEstudiante]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene todos los logros disponibles (para mostrar progreso)
     */
    public function obtenerTodosLosLogros() {
        $stmt = $this->db->prepare("SELECT * FROM logros ORDER BY id_logro");
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Verifica qué logros tiene un estudiante
     */
    public function obtenerProgreso($idEstudiante) {
        $todos = $this->obtenerTodosLosLogros();
        $obtenidos = $this->obtenerLogrosEstudiante($idEstudiante);
        
        $idsObtenidos = array_column($obtenidos, 'id_logro');
        
        $progreso = [];
        foreach ($todos as $logro) {
            $progreso[] = [
                'logro' => $logro,
                'desbloqueado' => in_array($logro['id_logro'], $idsObtenidos),
                'fecha' => null
            ];
        }
        
        // Agregar fechas de obtención
        foreach ($progreso as &$item) {
            foreach ($obtenidos as $obtenido) {
                if ($obtenido['id_logro'] == $item['logro']['id_logro']) {
                    $item['fecha'] = $obtenido['fecha_obtencion'];
                    break;
                }
            }
        }
        
        return $progreso;
    }
    
    /**
     * Obtiene el ranking de estudiantes por logros
     */
    public function obtenerRanking($limite = 10) {
        $stmt = $this->db->prepare("
            SELECT u.id_usuario, u.nombre, u.apellido, u.foto_perfil,
                   COUNT(ul.id_logro) as total_logros,
                   AVG(c.calificacion) as promedio
            FROM usuarios u
            LEFT JOIN usuarios_logros ul ON u.id_usuario = ul.id_usuario
            LEFT JOIN calificaciones c ON u.id_usuario = c.id_estudiante
            WHERE u.rol = 'estudiante' AND u.activo = 1
            GROUP BY u.id_usuario, u.nombre, u.apellido, u.foto_perfil
            ORDER BY total_logros DESC, promedio DESC
            LIMIT ?
        ");
        $stmt->execute([$limite]);
        
        return $stmt->fetchAll();
    }
}
?>