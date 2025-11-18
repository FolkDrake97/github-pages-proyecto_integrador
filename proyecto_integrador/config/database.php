<?php

define('DB_HOST', 'fdb1032.awardspace.net');  // Host de AwardSpace
define('DB_NAME', '4681910_plataforma');       // Nombre de tu BD
define('DB_USER', '4681910_plataforma');       // Usuario de BD
define('DB_PASS', 'isaac2002');                // Tu contraseña
define('DB_CHARSET', 'utf8mb4');

// Configuración de la aplicación
define('APP_NAME', 'Plataforma Académica');
define('APP_VERSION', '1.0.0');
define('SITE_NAME', 'Plataforma Académica');

// ✅ BASE_URL - CONFIGURACIÓN PARA AWARDSPACE
if (!defined('BASE_URL')) {
    // Para AwardSpace, tu URL será algo como:
    // http://tuusuario.atwebpages.com o tu dominio personalizado
    
    // OPCIÓN 1: Detección automática
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $path = dirname($script);
    $path = str_replace('\\', '/', $path);
    $path = trim($path, '/');
    $path = preg_replace('#/(api|views|includes|config).*$#', '', $path);
    
    define('BASE_URL', $protocol . '://' . $host . '/' . $path);
    
    // OPCIÓN 2: Manual (descomenta y ajusta si la automática no funciona)
    // define('BASE_URL', 'http://tuusuario.atwebpages.com');
}

// Configuración de contraseñas
define('PASSWORD_MIN_LENGTH', 6);

// Zona horaria
date_default_timezone_set('America/Mexico_City');

// Manejo de errores (CAMBIAR EN PRODUCCIÓN)
error_reporting(E_ALL);
ini_set('display_errors', 1);  // ⚠️ Cambiar a 0 en producción

// ============================================
// CLASE DE CONEXIÓN SINGLETON
// ============================================
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
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];

            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
            
            // Log de conexión exitosa (remover en producción)
            error_log("✓ Conexión exitosa a la base de datos");
            
        } catch(PDOException $e) {
            // Log del error
            error_log("✗ Error de conexión: " . $e->getMessage());
            
            // Mensaje de error para el usuario
            die("
                <div style='font-family: Arial; padding: 20px; background: #fee; border: 2px solid #fcc; border-radius: 5px; margin: 20px;'>
                    <h2 style='color: #c00;'>⚠️ Error de Conexión a la Base de Datos</h2>
                    <p><strong>No se pudo conectar a la base de datos.</strong></p>
                    <p>Por favor verifica:</p>
                    <ul>
                        <li>Que las credenciales en <code>config/database.php</code> sean correctas</li>
                        <li>Que la base de datos esté creada en tu panel de AwardSpace</li>
                        <li>Que el usuario tenga permisos sobre la base de datos</li>
                    </ul>
                    <details style='margin-top: 10px; background: #fafafa; padding: 10px; border-radius: 3px;'>
                        <summary style='cursor: pointer; font-weight: bold;'>Ver detalles técnicos</summary>
                        <pre style='margin-top: 10px; color: #666;'>" . htmlspecialchars($e->getMessage()) . "</pre>
                    </details>
                </div>
            ");
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

    // Prevenir clonación
    private function __clone() {}
    
    // Prevenir deserialización
    public function __wakeup() {
        throw new Exception("No se puede deserializar un singleton");
    }
}
?>