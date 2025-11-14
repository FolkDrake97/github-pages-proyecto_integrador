<?php
class Validator {
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    public static function validatePassword($password) {
        return strlen($password) >= 8;
    }
    
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map('self::sanitizeInput', $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
}
// Agregar funciones
function validateSubjectData($data) {
    $errors = [];
    if (empty($data['nombre'])) $errors[] = "El nombre es requerido";
    if (empty($data['creditos'])) $errors[] = "Los créditos son requeridos";
    return $errors;
}

function validateTaskData($data) {
    $errors = [];
    if (empty($data['titulo'])) $errors[] = "El título es requerido";
    if (empty($data['fecha_limite'])) $errors[] = "La fecha límite es requerida";
    if (empty($data['ponderacion'])) $errors[] = "La ponderación es requerida";
    return $errors;
}
?>