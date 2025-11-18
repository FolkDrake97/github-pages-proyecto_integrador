<?php
class Auth {
    public static function check() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: ../views/login.php');
            exit;
        }
        return true;
    }
    
    public static function checkRole($allowedRoles) {
        self::check();
        
        if (!in_array($_SESSION['user_role'], $allowedRoles)) {
            http_response_code(403);
            include '../views/errors/403.php';
            exit;
        }
        return true;
    }
    
    public static function isStudent() {
        return self::checkRole(['estudiante']);
    }
    
    public static function isTeacher() {
        return self::checkRole(['maestro']);
    }
    
    public static function isAdmin() {
        return self::checkRole(['administrador']);
    }
    
    public static function getUser() {
        self::check();
        return [
            'id' => $_SESSION['user_id'],
            'nombre' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'rol' => $_SESSION['user_role']
        ];
    }
    
    public static function logout() {
        session_start();
        session_destroy();
        header('Location: ../views/login.php');
        exit;
    }
}

// Helper functions
function requireAuth() {
    return Auth::check();
}

function requireStudent() {
    return Auth::isStudent();
}

function requireTeacher() {
    return Auth::isTeacher();
}

function requireAdmin() {
    return Auth::isAdmin();
}
?>