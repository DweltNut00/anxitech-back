<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . '/../config/db.php';
echo json_encode(['step' => 'db_ok']);

$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo json_encode(['tablas' => $tables]);

require_once __DIR__ . '/../controllers/AuthController.php';
$authController = new AuthController();
$response = $authController->login('test', 'test');
echo json_encode(['response' => $response]);