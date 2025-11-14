<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'plataforma_academica');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'Plataforma Académica');
define('APP_VERSION', '1.0.0');
define('SITE_NAME', 'Plataforma Académica');

// ✅ CORRECCIÓN CRÍTICA: BASE_URL
if (!defined('BASE_URL')) {
    // Detectar automáticamente la URL base
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // 🔥 OPCIÓN 1: Detectar automáticamente (RECOMENDADO)
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $path = dirname($script);
    
    // Limpiar la ruta
    $path = str_replace('\\', '/', $path);
    $path = trim($path, '/');
    
    // Remover subdirectorios específicos si existen
    $path = preg_replace('#/(api|views|includes|config).*$#', '', $path);
    
    // Construir BASE_URL
    define('BASE_URL', $protocol . '://' . $host . '/' . $path);
}

define('PASSWORD_MIN_LENGTH', 6);
date_default_timezone_set('America/Mexico_City');
error_reporting(E_ALL);
ini_set('display_errors', 1);

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch(PDOException $e) {
            error_log("Error de conexión: " . $e->getMessage());
            die("Error al conectar con la base de datos.");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    private function __clone() {}
    public function __wakeup() {}
}

?>