<?php
require_once '../models/Subject.php';
require_once '../includes/validators.php';

class SubjectController {
    private $subjectModel;
    
    public function __construct($db) {
        $this->subjectModel = new Subject($db);
    }
    
    public function listar() {
        $materias = $this->subjectModel->getAll();
        include '../views/materias/lista.php';
    }
    
    public function crear() {
        if ($_POST) {
            $errors = validateSubjectData($_POST);
            if (empty($errors)) {
                if ($this->subjectModel->create($_POST)) {
                    header('Location: ?controller=subject&action=listar&success=1');
                }
            }
        }
        include '../views/materias/crear.php';
    }
}
?>