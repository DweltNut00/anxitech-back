<?php
// Incluir la conexión PDO y los modelos Recomendacion
require_once '..\config\db.php';
require_once '..\models\Recomendacion.php';

class RecommendationController {
    private $pdo;

    // Constructor para pasar la conexión PDO
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Método para generar recomendaciones basadas en las respuestas del usuario
    public function getRecomendacion($resultado) {
        // Crear una instancia del modelo Question pasando la conexión PDO
        $questionModel = new Recomendacion($this->pdo);
        return $questionModel->getRecomendacion($resultado);
    }
}
?>
