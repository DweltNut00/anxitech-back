<?php
// Cargar el archivo de configuración
require_once 'config/db.php';

header("Access-Control-Allow-Origin: *"); // Permite que el frontend acceda a la API
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Redirigir todas las solicitudes al manejador de rutas
require_once 'routes/api.php';


// Incluir los archivos de las APIs correspondientes
require_once 'api/auth.php';            // Lógica para login, registro, recuperación de contraseña
require_once 'api/users.php';           // Gestión de usuarios (perfil, actualización de datos)
require_once 'api/questions.php';       // Lógica de preguntas para medir el nivel de ansiedad
require_once 'api/recommendations.php'; // Recomendaciones basadas en el nivel de ansiedad
require_once 'api/analytics.php';       // Cálculo de gráficos, visualización de datos

// Este es el punto de entrada para la API. El enrutador puede redirigir a otros archivos según la ruta solicitada.
// Por ejemplo, si la URL contiene `/auth`, se manejará con `auth.php`. Si contiene `/users`, se manejará con `users.php`.
// A continuación, te muestro cómo lo harías si tuvieras una estructura de rutas más avanzada.

// Definir la URL actual (puedes usar algo como $_SERVER['REQUEST_URI'] para obtener la URL)
$uri = $_SERVER['REQUEST_URI'];

// Verificar el tipo de solicitud HTTP y la ruta
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lógica para manejar el POST con base en la ruta de la solicitud
    if (strpos($uri, '/api/auth') !== false) {
        require_once 'api/auth.php'; // Lógica de autenticación
    } elseif (strpos($uri, '/api/users') !== false) {
        require_once 'api/users.php'; // Lógica de usuarios
    } elseif (strpos($uri, '/api/questions') !== false) {
        require_once 'api/questions.php'; // Lógica de preguntas
    } elseif (strpos($uri, '/api/recommendations') !== false) {
        require_once 'api/recommendations.php'; // Lógica de recomendaciones
    } elseif (strpos($uri, '/api/analytics') !== false) {
        require_once 'api/analytics.php'; // Lógica de análisis
    } else {
        // Si la ruta no coincide con ninguna de las anteriores
        echo json_encode(['error' => 'Endpoint no encontrado']);
    }
} else {
    // Si no es una solicitud POST, podrías manejar otras rutas, como GET
    echo json_encode(['message' => 'Bienvenido a la API de Residencias']);
}

// index.php (Archivo principal que maneja las rutas)

// Incluir los controladores
include_once 'controllers/HistoryController.php';

// Suponiendo que tu API use rutas RESTful y que uses parámetros en la URL
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (strpos($uri, '/api/users') !== false) {
        require_once 'api/users.php'; // Lógica de autenticación
    } elseif (strpos($uri, '/api/questions') !== false) {
        require_once 'api/questions.php'; // Lógica de usuarios
    } else {
        // Si la ruta no coincide con ninguna de las anteriores
        echo json_encode(['error' => 'Endpoint no encontrado']);
    }
}
?>


