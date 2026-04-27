<?php
function env_value(string $key, ?string $default = null): ?string
{
    $value = getenv($key);

    if ($value !== false) {
        return $value;
    }

    return $_ENV[$key] ?? $default;
}

$envPath = __DIR__ . '/../.env';
if (is_file($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");

        if (getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

$host     = env_value('MYSQLHOST') ?: env_value('DB_HOST', 'localhost');
$dbname   = env_value('MYSQL_DATABASE') ?: env_value('DB_NAME');
$username = env_value('MYSQLUSER') ?: env_value('DB_USER');
$password = env_value('MYSQLPASSWORD') ?: env_value('DB_PASSWORD', '');
$port     = env_value('MYSQLPORT') ?: env_value('DB_PORT', '3306');
$charset  = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    header("Content-Type: application/json");
    echo json_encode([
        'status'  => 'error',
        'message' => 'Error de conexión a la base de datos.',
        'detail'  => $e->getMessage()
    ]);
    exit;
}
?>
