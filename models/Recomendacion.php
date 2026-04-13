<?php

class Recomendacion
{
    private $pdo;

    // Constructor que recibe la conexión PDO
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Método para recuperar a los periodos contestados por un alumno
    public function getRecomendacion($resultado)
    {
        // Consulta para obtener los datos
        $stmt = $this->pdo->prepare("SELECT * FROM recomendacion WHERE :resultado BETWEEN rango_min AND rango_max LIMIT 1");
        $stmt->execute([
            "resultado" => $resultado
        ]);

        // Verificamos si se encontraron resultados...
        if ($stmt->rowCount() > 0) {
            $results = [];
            while ($row = $stmt->fetch()) {
                $result = [];
                $result += ['id' => $row['id']];
                $result += ['rango_min' => $row['rango_min']];
                $result += ['rango_max' => $row['rango_max']];
                $result += ['resultado' => $row['resultado']];
                $result += ['descripcion' => json_decode($row['descripcion'], true)];
                $results[] = $result;
            }

            return ['status' => 'ok', 'data' => $results];
        } else {
            return ['status' => 'ok', 'data' => []];  // No se encontraron resultados
        }
    }
}
