<?php
require_once 'headers.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once '..\config\db.php';
require_once '..\controllers\QuestionController.php';

$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents('php://input'), true);

switch ($action) {

    case 'getPreguntas':
        $questionController = new QuestionController($pdo);  // Asegúrate de pasar la conexión PDO aquí
        $response = $questionController->getQuestions();
        echo json_encode($response);
        break;

    case 'getEncuesta':
        $questionController = new QuestionController($pdo);  // Asegúrate de pasar la conexión PDO aquí
        $response = $questionController->getEncuesta();
        echo json_encode($response);
        break;

    case 'registerEncuesta':
        $questionController = new QuestionController($pdo);  // Asegúrate de pasar la conexión PDO aquí
        $response = $questionController->registerEncuesta($data);
        echo json_encode($response);
        break;

    case 'registerEncuestaExtra':
        $questionController = new QuestionController($pdo);  // Asegúrate de pasar la conexión PDO aquí
        $response = $questionController->registerEncuestaExtra($data);
        echo json_encode($response);
        break;

    case 'getPeriodos':
        $questionController = new QuestionController($pdo);  // Asegúrate de pasar la conexión PDO aquí
        $response = $questionController->getPeriodos();
        echo json_encode($response);
        break;

    case 'getAplicaciones':
        $questionController = new QuestionController($pdo);  // Asegúrate de pasar la conexión PDO aquí
        $response = $questionController->getAplicaciones();
        echo json_encode($response);
        break;

    case 'getAplicacionesExtra':
        $questionController = new QuestionController($pdo);  // Asegúrate de pasar la conexión PDO aquí
        $response = $questionController->getAplicacionesExtra();
        echo json_encode($response);
        break;

    case 'getMisAplicaciones':
        $questionController = new QuestionController($pdo);  // Asegúrate de pasar la conexión PDO aquí
        $response = $questionController->getMisAplicaciones($data);
        echo json_encode($response);
        break;

    case 'getMisAplicacionesExtra':
        $questionController = new QuestionController($pdo);  // Asegúrate de pasar la conexión PDO aquí
        $response = $questionController->getMisAplicacionesExtra($data);
        echo json_encode($response);
        break;

    case 'getMiEncuesta':
        $questionController = new QuestionController($pdo);  // Asegúrate de pasar la conexión PDO aquí
        $response = $questionController->getMiEncuesta($data);
        echo json_encode($response);
        break;

    case 'registerPregunta':
        $questionController = new QuestionController($pdo);  // Asegúrate de pasar la conexión PDO aquí
        $response = $questionController->registerPregunta($data);
        echo json_encode($response);
        break;

    case 'deletePregunta':
        $questionController = new QuestionController($pdo);  // Asegúrate de pasar la conexión PDO aquí
        $response = $questionController->deletePregunta($data['id']);
        echo json_encode($response);
        break;

    case 'registerPeriodo':
        $questionController = new QuestionController($pdo);  // Asegúrate de pasar la conexión PDO aquí
        $response = $questionController->registerPeriodo($data);
        echo json_encode($response);
        break;

    case 'deletePeriodo':
        $questionController = new QuestionController($pdo);  // Asegúrate de pasar la conexión PDO aquí
        $response = $questionController->deletePeriodo($data['id']);
        echo json_encode($response);
        break;

    default:
        echo json_encode(['error' => 'Acción no reconocida']);
}
