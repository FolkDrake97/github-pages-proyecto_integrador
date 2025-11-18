<?php
class Subject {
    private $conn;
    private $table = 'materias';

    public $id;
    public $nombre;
    public $descripcion;
    public $creditos;
    public $id_maestro;
    public $activa;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT m.*, u.nombre as maestro_nombre, u.apellido as maestro_apellido 
                  FROM " . $this->table . " m 
                  LEFT JOIN usuarios u ON m.id_maestro = u.id_usuario 
                  WHERE m.activa = 1 
                  ORDER BY m.nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET nombre=:nombre, descripcion=:descripcion, 
                      creditos=:creditos, id_maestro=:id_maestro";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":creditos", $this->creditos);
        $stmt->bindParam(":id_maestro", $this->id_maestro);

        return $stmt->execute();
    }

    // MÉTODOS NUEVOS PARA REPORTES:

    // Obtener total de materias
    public function getTotalMaterias() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE activa = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    // Obtener materias por maestro
    public function getByTeacher($teacherId) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE id_maestro = ? AND activa = 1 
                  ORDER BY nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $teacherId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener materias activas (versión mejorada)
    public function getAllActive() {
        $query = "SELECT m.*, u.nombre as maestro_nombre, u.apellido as maestro_apellido 
                  FROM " . $this->table . " m 
                  LEFT JOIN usuarios u ON m.id_maestro = u.id_usuario 
                  WHERE m.activa = 1 
                  ORDER BY m.nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener estadísticas de materias
    public function getSubjectStats() {
        $stats = [
            'total' => $this->getTotalMaterias(),
            'con_maestro' => $this->getCountWithTeacher(),
            'sin_maestro' => $this->getCountWithoutTeacher()
        ];
        return $stats;
    }

    // Contar materias con maestro asignado
    private function getCountWithTeacher() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " 
                  WHERE id_maestro IS NOT NULL AND activa = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    // Contar materias sin maestro asignado
    private function getCountWithoutTeacher() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " 
                  WHERE id_maestro IS NULL AND activa = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    // Método adicional: Obtener materia por ID
    public function getById($id) {
        $query = "SELECT m.*, u.nombre as maestro_nombre, u.apellido as maestro_apellido 
                  FROM " . $this->table . " m 
                  LEFT JOIN usuarios u ON m.id_maestro = u.id_usuario 
                  WHERE m.id_materia = ? AND m.activa = 1 
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>