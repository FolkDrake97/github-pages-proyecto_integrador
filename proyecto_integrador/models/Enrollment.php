<?php
class Enrollment {
    private $conn;
    private $table = 'inscripciones';

    public $id;
    public $id_estudiante;
    public $id_materia;
    public $estado;
    public $fecha_solicitud;
    public $motivo_rechazo;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Métodos básicos para reportes
    public function getTotalInscripciones() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getInscripcionesPorEstado($estado) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE estado = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $estado);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getEnrollmentStats() {
        return [
            'total' => $this->getTotalInscripciones(),
            'aprobadas' => $this->getInscripcionesPorEstado('aprobada'),
            'pendientes' => $this->getInscripcionesPorEstado('pendiente'),
            'rechazadas' => $this->getInscripcionesPorEstado('rechazada')
        ];
    }
}
?>