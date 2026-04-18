<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . '/../config/db.php';
echo json_encode(['step' => 'db_ok']);

require_once __DIR__ . '/../controllers/AuthController.php';
echo json_encode(['step' => 'controller_ok']);

$authController = new AuthController();
echo json_encode(['step' => 'instancia_ok']);

$response = $authController->login('test', 'test');
echo json_encode(['step' => 'login_ok', 'response' => $response]);