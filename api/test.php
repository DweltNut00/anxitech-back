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

// Verificar usuario admin
$stmt = $pdo->query("SELECT id, usuario, email FROM usuario LIMIT 3");
$usuarios = $stmt->fetchAll();
echo json_encode(['usuarios' => $usuarios]);