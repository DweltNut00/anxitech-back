<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/AuthController.php';

require_once 'headers.php';
require_once '..\config\db.php';
require_once '..\controllers\AuthController.php';

$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents('php://input'), true);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

switch ($action) {
    case 'login':
        $username = $data['username'];
        $password = $data['password'];
        $authController = new AuthController();
        $response = $authController->login($username, $password);
        echo json_encode(array_merge($response, ['token' => session_id()]));
        break;

    case 'validateEmail':
        $email = $data['email'];
        $authController = new AuthController();
        $response = $authController->validateEmail($email);
        echo json_encode($response);
        break;
    
    case 'validateNoControl':
        $nocontrol = $data['nocontrol'];
        $authController = new AuthController();
        $response = $authController->validateNoControl($nocontrol);
        echo json_encode($response);
        break;

    case 'validateUsername':
        $username = $data['username'];
        $authController = new AuthController();
        $response = $authController->validateUsername($username);
        echo json_encode($response);
        break;

    case 'register':
        $username = $data['username'];
        $nombre = $data['nombre'];
        $apellido = $data['apellido'];
        $email = $data['email'];
        $password = $data['password'];
        $nocontrol = $data['nocontrol'];
        $fechan = $data['fechan'];
        $sexo = $data['sexo'];
        $estadoc = $data['estadoc'];
        $ciudad = $data['ciudad'];
        $estado = $data['estado'];
        $authController = new AuthController();
        $response = $authController->register($username, $nombre, $apellido, $email, $password, $nocontrol, $sexo, $fechan, $estadoc, $ciudad, $estado);
        echo json_encode($response);
        break;

    case 'registerAdmin':
        $username = $data['username'];
        $nombre = $data['nombre'];
        $apellido = $data['apellido'];
        $email = $data['email'];
        $password = $data['password'];
        $permisos = $data['permisos'];
        $authController = new AuthController();
        $response = $authController->registerAdmin($username, $nombre, $apellido, $email, $password, $permisos);
        echo json_encode($response);
        break;

    default:
        echo json_encode(['error' => 'Action not recognized']);
}
