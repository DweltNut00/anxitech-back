<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

echo json_encode([
    'MYSQLHOST'     => getenv('MYSQLHOST'),
    'MYSQLDATABASE' => getenv('MYSQLDATABASE'),
    'MYSQLUSER'     => getenv('MYSQLUSER'),
    'MYSQLPORT'     => getenv('MYSQLPORT'),
    'MYSQLPASSWORD' => getenv('MYSQLPASSWORD') ? '***set***' : 'VACÍO'
]);