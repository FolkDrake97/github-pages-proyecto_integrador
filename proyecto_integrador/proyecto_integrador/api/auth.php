<?php
require_once '../config/database.php';
require_once '../models/User.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$data = json_decode(file_get_contents("php://input"), true);

if ($_POST['action'] == 'login') {
    $user->email = $_POST['email'];
    $user->password = $_POST['password'];
    
    if ($user->login()) {
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas']);
    }
}
?>