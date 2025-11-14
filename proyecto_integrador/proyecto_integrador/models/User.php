<?php
class User {
    private $conn;
    private $table = 'usuarios';

    public $id;
    public $nombre;
    public $email;
    public $password;
    public $rol;
    public $apellido;
    public $fecha_registro;
    public $activo;
    public $foto_perfil;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Login usuario
    public function login() {
        $query = "SELECT id_usuario, nombre, apellido, email, password, rol, fecha_registro, activo, foto_perfil 
                  FROM " . $this->table . " 
                  WHERE email = ? AND activo = 1 LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        if($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Para pruebas: aceptar contraseña simple
            if($this->password === $row['password']) {
                $this->id = $row['id_usuario'];
                $this->nombre = $row['nombre'];
                $this->apellido = $row['apellido'];
                $this->email = $row['email'];
                $this->rol = $row['rol'];
                $this->fecha_registro = $row['fecha_registro'];
                $this->activo = $row['activo'];
                $this->foto_perfil = $row['foto_perfil'];
                return true;
            }
        }
        return false;
    }

    // Verificar si el email ya existe
    public function emailExists($email = null) {
        $emailToCheck = $email ?: $this->email;
        $query = "SELECT id_usuario FROM " . $this->table . " WHERE email = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $emailToCheck);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Registrar usuario
    public function register() {
        // Verificar si el email ya existe
        if ($this->emailExists()) {
            return false;
        }

        $query = "INSERT INTO " . $this->table . " 
                  SET nombre=:nombre, apellido=:apellido, email=:email, password=:password, rol='estudiante'";
        
        $stmt = $this->conn->prepare($query);
        
        // Contraseña simple para pruebas
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":apellido", $this->apellido);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Obtener usuario por ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id_usuario = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener usuarios por rol con información completa
    public function getByRole($rol) {
        $query = "SELECT * FROM " . $this->table . " WHERE rol = ? AND activo = 1 ORDER BY nombre, apellido";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $rol);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener todos los maestros
    public function getTeachers() {
        return $this->getByRole('maestro');
    }

    // Obtener todos los estudiantes
    public function getStudents() {
        return $this->getByRole('estudiante');
    }

    // Obtener todos los administradores
    public function getAdmins() {
        return $this->getByRole('administrador');
    }

    // Obtener todos los usuarios
    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " WHERE activo = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener nombre completo
    public function getFullName() {
        return $this->nombre . ' ' . $this->apellido;
    }

    // Obtener total de usuarios
    public function getTotalUsuarios() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE activo = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    // Obtener total de usuarios por rol
    public function getTotalPorRol($rol) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE rol = ? AND activo = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $rol);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    // Obtener estadísticas de usuarios
    public function getUserStats() {
        $stats = [
            'total' => $this->getTotalUsuarios(),
            'estudiantes' => $this->getTotalPorRol('estudiante'),
            'maestros' => $this->getTotalPorRol('maestro'),
            'administradores' => $this->getTotalPorRol('administrador')
        ];
        return $stats;
    }
}
?>