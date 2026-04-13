<?php
require_once 'headers.php';
require_once '..\config\db.php';

$action = $_GET['action'] ?? '';

switch ($action) {

    // GET geo.php?action=estados
    case 'estados':
        $stmt = $pdo->query("SELECT id, nombre FROM geo_estados ORDER BY nombre ASC");
        $estados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode([
            'status'  => 'ok',
            'estados' => $estados,
        ]);
        break;

    // GET geo.php?action=municipios&estado=Veracruz de Ignacio de la Llave
    case 'municipios':
        $nombreEstado = trim($_GET['estado'] ?? '');

        if (empty($nombreEstado)) {
            echo json_encode(['status' => 'error', 'message' => 'Parámetro estado requerido.']);
            break;
        }

        $stmt = $pdo->prepare("
            SELECT m.nombre
            FROM geo_municipios m
            INNER JOIN geo_estados e ON e.id = m.estado_id
            WHERE e.nombre = :nombre
            ORDER BY m.nombre ASC
        ");
        $stmt->execute([':nombre' => $nombreEstado]);
        $municipios = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode([
            'status'     => 'ok',
            'municipios' => $municipios,
        ]);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Action not recognized']);
        break;
}