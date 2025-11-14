<?php
require_once '../config/config.php';
require_once '../models/Subject.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();
$subject = new Subject($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if (isset($_GET['teacher_id'])) {
            $materias = $subject->getMateriasMaestro($_GET['teacher_id']);
        } else {
            $materias = $subject->getAll();
        }
        echo json_encode(['success' => true, 'data' => $materias]);
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        if ($subject->create($data)) {
            echo json_encode(['success' => true, 'message' => 'Materia creada']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear materia']);
        }
        break;
}
?>