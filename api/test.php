<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

echo json_encode(['step' => '1_inicio']);

require_once __DIR__ . '/../config/db.php';
echo json_encode(['step' => '2_db_ok']);

require_once __DIR__ . '/../controllers/AuthController.php';
echo json_encode(['step' => '3_controller_ok']);

require_once __DIR__ . '/../models/Usuario.php';
echo json_encode(['step' => '4_modelo_ok']);