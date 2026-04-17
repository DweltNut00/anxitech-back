<?php
$allowed_origins = [
    'https://anxitechfrontend.netlify.app',
    'http://localhost:5173',
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
}
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
require_once 'headers.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once '../config/db.php';
require_once '../controllers/UserController.php';

$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents('php://input'), true);

$userController = new UserController(); // 🔥 DEFINIR UNA SOLA VEZ AQUÍ

switch ($action) {
    case 'cargaMasiva':
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['excel'])) {
            echo json_encode(['status' => 'error', 'message' => 'Método no permitido o archivo no recibido.']);
            exit;
        }
        $response = $userController->cargaMasiva($_FILES['excel']['tmp_name']);
        echo json_encode($response);
        break;

    case 'enviarCodigo':
        $response = $userController->enviarCodigo($data['email']);
        echo json_encode($response);
        break;

    case 'validarCodigo':
        $response = $userController->validarCodigo($data['email'], $data['codigo']);
        echo json_encode($response);
        break;

    case 'actualizarPass':
        $response = $userController->actualizarPass($data['email'], $data['pass']);
        echo json_encode($response);
        break;

    // 🔥 CASO CORREGIDO: getAlumnos con paginación
    case 'getAlumnos':
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['perPage']) ? (int)$_GET['perPage'] : 50;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        $response = $userController->getAlumnos($page, $perPage, $search);
        echo json_encode($response);
        break;

    case 'getAlumno':
        $response = $userController->getAlumno($data['id']);
        echo json_encode($response);
        break;

    case 'getAdmin':
        $response = $userController->getAdmin($data['id']);
        echo json_encode($response);
        break;

    case 'getAdmins':
        $response = $userController->getAdmins();
        echo json_encode($response);
        break;

    case 'deleteAdmin':
        $response = $userController->deleteAdmin($data['id']);
        echo json_encode($response);
        break;

    case 'deleteAlumno':
        $response = $userController->deleteAlumno($data['id']);
        echo json_encode($response);
        break;

    case 'updateAlumno':
        $response = $userController->updateAlumno($data['id'], $data);
        echo json_encode($response);
        break;

    case 'updateAdmin':
        $response = $userController->updateAdmin($data['id'], $data);
        echo json_encode($response);
        break;

    case 'updateTema':
        $response = $userController->updateTema($data['id'], $data['tema']);
        echo json_encode($response);
        break;

    default:
        echo json_encode(['error' => 'Action not recognized']);
}
?>