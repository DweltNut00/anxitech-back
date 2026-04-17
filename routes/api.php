<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../controllers/AnalyticsController.php';
require_once __DIR__ . '/../controllers/QuestionController.php';
require_once __DIR__ . '/../controllers/RecommendationController.php';

// Inicializamos los controladores con la conexión PDO
$userController = new UserController($pdo);
$analyticsController = new AnalyticsController($pdo);
$questionController = new QuestionController($pdo);
$recommendationController = new RecommendationController($pdo);

// Obtener el método HTTP y la ruta
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = explode('?', $_SERVER['REQUEST_URI'], 2)[0];

// Definir las rutas de la API
switch ($requestUri) {
    case '/api/user/get':
        if ($method === 'GET') {
            $userId = $_GET['id'] ?? null;
            echo json_encode($userController->getUserData($userId));
        }
        break;

    case '/api/user/update':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            echo json_encode($userController->updateUserData($data['id'], $data));
        }
        break;

    case '/api/questions':
        if ($method === 'GET') {
            echo json_encode($questionController->getQuestions());
        }
        break;

    case '/api/answers/save':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            echo json_encode($questionController->saveAnswers($data['userId'], $data['answers']));
        }
        break;

    case '/api/recommendations':
        if ($method === 'GET') {
            $userId = $_GET['id'] ?? null;
            echo json_encode($recommendationController->generateRecommendations($userId));
        }
        break;

    case '/api/analytics':
        if ($method === 'GET') {
            $userId = $_GET['id'] ?? null;
            echo json_encode($analyticsController->generateAnalytics($userId));
        }
        break;

    default:
        header("HTTP/1.1 404 Not Found");
        echo json_encode(["message" => "Ruta no encontrada"]);
        break;
}
?>
