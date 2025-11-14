<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';
include_once '../models/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$data = json_decode(file_get_contents("php://input"));

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if(isset($data->action)) {
        
        // LOGIN
        if($data->action == 'login') {
            $user->email = $data->email;
            $user->password = $data->password;
            
            if($user->login()) {
                $_SESSION['user_id'] = $user->id;
                $_SESSION['user_role'] = $user->rol;
                $_SESSION['user_name'] = $user->nombre;
                
                echo json_encode(array(
                    'success' => true,
                    'message' => 'Login exitoso',
                    'user' => array(
                        'id' => $user->id,
                        'nombre' => $user->nombre,
                        'email' => $user->email,
                        'rol' => $user->rol
                    )
                ));
            } else {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Credenciales incorrectas'
                ));
            }
        }
        
        // REGISTRO
        elseif($data->action == 'register') {
            $user->nombre = $data->nombre;
            $user->email = $data->email;
            $user->password = $data->password;
            $user->rol = 'estudiante';
            
            if($user->register()) {
                echo json_encode(array(
                    'success' => true,
                    'message' => 'Usuario registrado exitosamente'
                ));
            } else {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Error al registrar usuario'
                ));
            }
        }
    }
}
?>