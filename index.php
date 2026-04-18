<?php
header("Access-Control-Allow-Origin: https://anxitechfrontend.netlify.app");
header("Content-Type: application/json");

echo json_encode(['status' => 'ok', 'message' => 'API AnxiTech activa']);
?>