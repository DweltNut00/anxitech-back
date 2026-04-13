<?php
$host = 'localhost';
$dbname = 'anxitech';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    // Creamos una instancia de PDO
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
    exit;
}
?>

