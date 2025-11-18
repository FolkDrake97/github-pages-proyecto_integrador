<?php
class Grade {
    private $conn;
    private $table = 'calificaciones';

    public $id;
    public $id_estudiante;
    public $id_tarea;
    public $calificacion;
    public $fecha_entrega;
    public $fecha_registro;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Métodos básicos para reportes
    public function getTotalCalificaciones() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getPromedioGeneral() {
        $query = "SELECT AVG(calificacion) as promedio FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['promedio'] ? round($result['promedio'], 2) : 0;
    }

    public function getGradeStats() {
        return [
            'total' => $this->getTotalCalificaciones(),
            'promedio_general' => $this->getPromedioGeneral(),
            'maxima' => $this->getMaximaCalificacion(),
            'minima' => $this->getMinimaCalificacion()
        ];
    }

    private function getMaximaCalificacion() {
        $query = "SELECT MAX(calificacion) as maxima FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['maxima'] ?: 0;
    }

    private function getMinimaCalificacion() {
        $query = "SELECT MIN(calificacion) as minima FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['minima'] ?: 0;
    }
}
?>