<?php
// Incluir la conexión PDO y los modelos Question y Answer
require_once '..\config\db.php';
require_once '..\models\Question.php';
require_once __DIR__ . '/../config/cors.php';

class QuestionController {
    private $pdo;

    // Constructor para pasar la conexión PDO
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Método para obtener todas las preguntas
    public function getQuestions() {
        // Crear una instancia del modelo Question pasando la conexión PDO
        $questionModel = new Question($this->pdo);
        return $questionModel->getPreguntas();
    }

    // Método para obtener las preguntas de una encuesta
    public function getEncuesta() {
        // Crear una instancia del modelo Question pasando la conexión PDO
        $questionModel = new Question($this->pdo);
        return $questionModel->getEncuesta();
    }
    
    // Método para registrar una pregunta
    public function registerPregunta($data) {
        // Crear una instancia del modelo Question pasando la conexión PDO
        $questionModel = new Question($this->pdo);
        return $questionModel->registerPregunta($data);
    }

    // Método para registrar una encuesta
    public function registerEncuesta($data) {
        // Crear una instancia del modelo Question pasando la conexión PDO
        $questionModel = new Question($this->pdo);
        return $questionModel->registerEncuesta($data);
    }
    
    // Método para registrar una encuesta extra
    public function registerEncuestaExtra($data) {
        // Crear una instancia del modelo Question pasando la conexión PDO
        $questionModel = new Question($this->pdo);
        return $questionModel->registerEncuestaExtra($data);
    }

    // Método para eliminar una pregunta
    public function deletePregunta($data) {
        // Crear una instancia del modelo Question pasando la conexión PDO
        $questionModel = new Question($this->pdo);
        return $questionModel->deletePregunta($data);
    }

    // Método para obtener todos los periodos de aplicacion
    public function getPeriodos() {
        // Crear una instancia del modelo Question pasando la conexión PDO
        $questionModel = new Question($this->pdo);
        return $questionModel->getPeriodos();
    }

    // Método para obtener todos los periodos de aplicacion
    public function getAplicaciones() {
        // Crear una instancia del modelo Question pasando la conexión PDO
        $questionModel = new Question($this->pdo);
        return $questionModel->getAplicaciones();
    }

    // Método para obtener todos los periodos de aplicacion para encuesta complementaria
    public function getAplicacionesExtra() {
        // Crear una instancia del modelo Question pasando la conexión PDO
        $questionModel = new Question($this->pdo);
        return $questionModel->getAplicacionesExtra();
    }

    // Método para obtener todos los periodos de aplicacion de un alumno
    public function getMisAplicaciones($data) {
        // Crear una instancia del modelo Question pasando la conexión PDO
        $questionModel = new Question($this->pdo);
        return $questionModel->getMisAplicaciones($data);
    }

    // Método para obtener todos los periodos de aplicacion de un alumno de encuesta complementaria
    public function getMisAplicacionesExtra($data) {
        // Crear una instancia del modelo Question pasando la conexión PDO
        $questionModel = new Question($this->pdo);
        return $questionModel->getMisAplicacionesExtra($data);
    }

    // Método para obtener una encuesta en específico
    public function getMiEncuesta($data) {
        // Crear una instancia del modelo Question pasando la conexión PDO
        $questionModel = new Question($this->pdo);
        return $questionModel->getMiEncuesta($data);
    }

    // Método para registrar un periodo de aplicacion
    public function registerPeriodo($data) {
        // Crear una instancia del modelo Question pasando la conexión PDO
        $questionModel = new Question($this->pdo);
        return $questionModel->registerPeriodo($data);
    }

    // Método para eliminar un periodo de aplicacion
    public function deletePeriodo($data) {
        // Crear una instancia del modelo Question pasando la conexión PDO
        $questionModel = new Question($this->pdo);
        return $questionModel->deletePeriodo($data);
    }

}
?>


