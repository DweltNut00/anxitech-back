<?php
require_once __DIR__ . '/../models/Recomendacion.php';

class RecommendationController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getRecomendacion($resultado) {
        $questionModel = new Recomendacion($this->pdo);
        return $questionModel->getRecomendacion($resultado);
    }
}
?>