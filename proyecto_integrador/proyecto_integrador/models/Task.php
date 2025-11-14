<?php
class Task {
    private $conn;
    private $table = 'actividades'; // ✅ CORREGIDO: era 'tareas'

    public $id;
    public $id_materia;
    public $titulo;
    public $descripcion;
    public $fecha_creacion;
    public $fecha_limite;
    public $ponderacion;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Métodos básicos para reportes
    public function getTotalTareas() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getTareasPendientes() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " 
                  WHERE fecha_limite >= CURDATE() AND activa = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getTaskStats() {
        return [
            'total' => $this->getTotalTareas(),
            'pendientes' => $this->getTareasPendientes(),
            'vencidas' => $this->getTareasVencidas()
        ];
    }

    private function getTareasVencidas() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " 
                  WHERE fecha_limite < CURDATE() AND activa = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    // Obtener tareas por materia
    public function getBySubject($subjectId) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE id_materia = ? AND activa = 1 
                  ORDER BY fecha_limite DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$subjectId]);
        return $stmt;
    }

    // Obtener tarea por ID
    public function getById($taskId) {
        $query = "SELECT a.*, m.nombre as materia_nombre 
                  FROM " . $this->table . " a
                  INNER JOIN materias m ON a.id_materia = m.id_materia
                  WHERE a.id_actividad = ? 
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$taskId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener tareas para un estudiante
    public function getForStudent($studentId, $subjectId = null) {
        if ($subjectId) {
            $query = "SELECT a.*, m.nombre as materia_nombre,
                             c.calificacion, c.fecha_entrega, c.comentarios
                      FROM " . $this->table . " a
                      INNER JOIN materias m ON a.id_materia = m.id_materia
                      INNER JOIN inscripciones i ON m.id_materia = i.id_materia
                      LEFT JOIN calificaciones c ON a.id_actividad = c.id_actividad 
                            AND c.id_estudiante = ?
                      WHERE i.id_estudiante = ? 
                        AND i.estado = 'aprobado'
                        AND a.id_materia = ?
                        AND a.activa = 1
                      ORDER BY a.fecha_limite DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$studentId, $studentId, $subjectId]);
        } else {
            $query = "SELECT a.*, m.nombre as materia_nombre,
                             c.calificacion, c.fecha_entrega, c.comentarios
                      FROM " . $this->table . " a
                      INNER JOIN materias m ON a.id_materia = m.id_materia
                      INNER JOIN inscripciones i ON m.id_materia = i.id_materia
                      LEFT JOIN calificaciones c ON a.id_actividad = c.id_actividad 
                            AND c.id_estudiante = ?
                      WHERE i.id_estudiante = ? 
                        AND i.estado = 'aprobado'
                        AND a.activa = 1
                      ORDER BY a.fecha_limite DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$studentId, $studentId]);
        }
        return $stmt;
    }

    // Crear nueva tarea
    public function create() {
        $query = "INSERT INTO " . $this->table . "
                  SET id_materia = :id_materia,
                      titulo = :titulo,
                      descripcion = :descripcion,
                      fecha_limite = :fecha_limite,
                      ponderacion = :ponderacion,
                      tipo = :tipo";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id_materia', $this->id_materia);
        $stmt->bindParam(':titulo', $this->titulo);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':fecha_limite', $this->fecha_limite);
        $stmt->bindParam(':ponderacion', $this->ponderacion);
        $stmt->bindParam(':tipo', $this->tipo);
        
        return $stmt->execute();
    }

    // Obtener total de tareas pendientes para un estudiante
    public function getTotalTareasPendientes($studentId) {
        $query = "SELECT COUNT(*) as total 
                  FROM " . $this->table . " a
                  INNER JOIN inscripciones i ON a.id_materia = i.id_materia
                  LEFT JOIN calificaciones c ON a.id_actividad = c.id_actividad 
                        AND c.id_estudiante = ?
                  WHERE i.id_estudiante = ? 
                    AND i.estado = 'aprobado'
                    AND a.activa = 1
                    AND a.fecha_limite >= CURDATE()
                    AND c.id_calificacion IS NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$studentId, $studentId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    // Obtener tareas del maestro
    public function getTareasMaestro($teacherId) {
        $query = "SELECT a.*, m.nombre as materia_nombre,
                         COUNT(DISTINCT c.id_calificacion) as total_entregas
                  FROM " . $this->table . " a
                  INNER JOIN materias m ON a.id_materia = m.id_materia
                  LEFT JOIN calificaciones c ON a.id_actividad = c.id_actividad
                  WHERE m.id_maestro = ? AND a.activa = 1
                  GROUP BY a.id_actividad
                  ORDER BY a.fecha_limite DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener tareas pendientes de calificar para un maestro
    public function getTotalTareasPendientesCalificar($teacherId) {
        $query = "SELECT COUNT(DISTINCT c.id_calificacion) as total
                  FROM calificaciones c
                  INNER JOIN " . $this->table . " a ON c.id_actividad = a.id_actividad
                  INNER JOIN materias m ON a.id_materia = m.id_materia
                  WHERE m.id_maestro = ?
                    AND c.calificacion IS NULL
                    AND c.fecha_entrega IS NOT NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$teacherId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}
?>