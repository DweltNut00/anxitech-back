<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$vars = [];
foreach ($_ENV as $key => $value) {
    if (stripos($key, 'mysql') !== false || stripos($key, 'db') !== false) {
        $vars[$key] = (stripos($key, 'pass') !== false) ? '***' : $value;
    }
}

echo json_encode($vars);