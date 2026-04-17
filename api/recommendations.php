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

require_once '..\config\db.php';
require_once '..\controllers\RecommendationController.php';

$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents('php://input'), true);

switch ($action) {
    case 'getRecomendacion':
        $recommendationController = new RecommendationController($pdo);
        $recommendations = $recommendationController->getRecomendacion($data['resultado']);
        echo json_encode($recommendations);
        break;

    default:
        echo json_encode(['error' => 'Action not recognized']);
}
