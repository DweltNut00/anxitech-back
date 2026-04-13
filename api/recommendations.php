<?php
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
