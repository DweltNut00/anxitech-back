<?php

class Question
{
    private $pdo;

    // Constructor que recibe la conexión PDO
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Método para recuperar a todas las preguntas
    public function getPreguntas()
    {
        // Consulta para obtener los datos
        $stmt = $this->pdo->prepare("SELECT * from pregunta");
        $stmt->execute();

        // Verificamos si se encontraron resultados...
        if ($stmt->rowCount() > 0) {
            $results = [];
            while ($row = $stmt->fetch()) {
                $result = [];
                $result += ['id' => $row['id']];
                $result += ['pregunta' => $row['pregunta']];
                $result += ['categoria' => $row['categoria']];
                $result += ['status' => $row['status']];
                $results[] = $result;
            }

            return ['status' => 'ok', 'data' => $results];
        } else {
            return ['status' => 'ok', 'data' => []];  // No se encontraron resultados
        }
    }

    // Método para recuperar a las preguntas de una encuesta
    public function getEncuesta()
    {
        // Consulta para obtener los datos
        $stmt = $this->pdo->prepare("SELECT * FROM pregunta WHERE status = 1 ORDER BY RAND() LIMIT :limite");
        $stmt->execute([
            'limite' => 7
        ]);

        // Verificamos si se encontraron resultados...
        if ($stmt->rowCount() > 0) {
            $results = [];
            while ($row = $stmt->fetch()) {
                $result = [];
                $result += ['id' => $row['id']];
                $result += ['pregunta' => $row['pregunta']];
                $result += ['categoria' => $row['categoria']];
                $result += ['status' => $row['status']];
                $results[] = $result;
            }

            return ['status' => 'ok', 'data' => $results];
        } else {
            return ['status' => 'ok', 'data' => []];  // No se encontraron resultados
        }
    }

    // Registrar periodo
    public function registerPregunta($data)
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO pregunta (pregunta) VALUES (:pregunta)");
            $stmt->execute([
                'pregunta' => $data['pregunta']
            ]);

            if ($stmt) {
                return ['status' => 'ok'];
            }

            return ['status' => 'error', 'message' => 'No se pudo agregar en "Pregunta".'];
        } catch (\Throwable $th) {
            return ['status' => 'error', 'message' => 'Ocurrió un error.'];
        }
    }

    // Registrar la encuesta de un usuario
    public function registerEncuesta($data)
    {
        if (!isset($data['id_alumno']) || !isset($data['preguntas']) || !isset($data['id_aplicacion'])) {
            return ['status' => 'error', 'message' => 'Ocurrió un error.'];
        }

        if (count($data['preguntas']) != 7) {
            return ['status' => 'error', 'message' => 'Ocurrió un error.'];
        }

        $stmt = $this->pdo->prepare("SELECT * FROM alumno_pregunta WHERE id_aplicacion = :id_aplicacion AND id_alumno = :id_alumno");
        $stmt->execute([
            'id_alumno' => $data['id_alumno'],
            'id_aplicacion' => $data['id_aplicacion']
        ]);
        $user = $stmt->fetch();

        if ($user) {
            return ['status' => 'error', 'message' => 'Ya has contestado esta encuesta.'];
        }

        try {
            $this->pdo->beginTransaction();
            $count = 0;

            for ($i = 0; $i < count($data['preguntas']); $i++) {
                $row = $data['preguntas'][$i];
                $stmt = $this->pdo->prepare("INSERT INTO alumno_pregunta (id_alumno, id_pregunta, valor, id_aplicacion) VALUES (:id_alumno, :id_pregunta, :valor, :id_aplicacion)");
                $stmt->execute([
                    'id_alumno' => $data['id_alumno'],
                    'id_pregunta' => $row['id_pregunta'],
                    'valor' => $row['value'],
                    'id_aplicacion' => $data['id_aplicacion']
                ]);

                if ($stmt) {
                    $count++;
                }
            }

            if ($count ==7) {
                $this->pdo->commit();
                return ['status' => 'ok'];
            }

            $this->pdo->rollback();
            return ['status' => 'error', 'message' => 'No se pudo agregar en "Encuesta".'];
        } catch (\Throwable $th) {
             $this->pdo->rollback();
             return ['status' => 'error', 'message' => 'Ocurrió un error: ' . $th->getMessage()]; // ← agrega getMessage()
        }
    }

 // Registrar la encuesta de información complementaria
public function registerEncuestaExtra($data)
{
    if (!isset($data['id_alumno']) || !isset($data['id_aplicacion'])) {
        return ['status' => 'error', 'message' => 'Ocurrió un error.'];
    }

    $stmt = $this->pdo->prepare("SELECT * FROM complemento WHERE id_aplicacion = :id_aplicacion AND id_alumno = :id_alumno");
    $stmt->execute([
        'id_alumno' => $data['id_alumno'],
        'id_aplicacion' => $data['id_aplicacion']
    ]);
    $user = $stmt->fetch();

    if ($user) {
        return ['status' => 'error', 'message' => 'Ya has contestado esta encuesta.'];
    }

    // **NUEVO: Obtener sexo, edad y estado civil del alumno**
    $stmt = $this->pdo->prepare("SELECT sexo, TIMESTAMPDIFF(YEAR, fechan, CURDATE()) as edad, estadoc as estado_civil FROM alumno WHERE id = :id_alumno");
    $stmt->execute(['id_alumno' => $data['id_alumno']]);
    $alumnoData = $stmt->fetch();

    if (!$alumnoData) {
        return ['status' => 'error', 'message' => 'No se encontró información del alumno.'];
    }

    try {
        // **MODIFICADO: Agregar los 3 nuevos campos**
        $stmt = $this->pdo->prepare("INSERT INTO complemento (id_alumno, id_aplicacion, carrera, promedio_anterior, semestre, materias, transporte, familiares, trabajo, beca, sexo, edad, estado_civil, tipo_ensenanza, tiene_hijos, ingreso_mensual, horas_sueno) VALUES (:id_alumno, :id_aplicacion, :carrera, :promedio_anterior, :semestre, :materias, :transporte, :familiares, :trabajo, :beca, :sexo, :edad, :estado_civil, :tipo_ensenanza, :tiene_hijos, :ingreso_mensual, :horas_sueno)");
        
        $stmt->execute([
            'id_alumno' => $data['id_alumno'],
            'id_aplicacion' => $data['id_aplicacion'],
            'carrera' => $data['carrera'],
            'promedio_anterior' => $data['promedio_anterior'],
            'semestre' => $data['semestre'],
            'materias' => $data['materias'],
            'transporte' => $data['transporte'],
            'familiares' => $data['familiares'],
            'trabajo' => $data['trabajo'],
            'beca' => $data['beca'],
            'sexo' => $alumnoData['sexo'],              // **NUEVO**
            'edad' => $alumnoData['edad'],              // **NUEVO**
            'estado_civil' => $alumnoData['estado_civil'], // **NUEVO**
            'tipo_ensenanza'    => $data['tipo_ensenanza'],
            'tiene_hijos'       => $data['tiene_hijos'],
            'ingreso_mensual'   => $data['ingreso_mensual'],
            'horas_sueno'       => $data['horas_sueno'],
        ]);

        if ($stmt) {
            return ['status' => 'ok'];
        }

        return ['status' => 'error', 'message' => 'No se pudo agregar en "Complemento".'];
    } catch (\Throwable $th) {
        return ['status' => 'error', 'message' => 'Ocurrió un error: ' . $th->getMessage()];
    }
}

    // Método para eliminar una pregunta
    public function deletePregunta($id)
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("SELECT COUNT(*) AS cuenta FROM alumno_pregunta WHERE id_pregunta = :id_pregunta");
            $stmt->bindParam(':id_pregunta', $id, PDO::PARAM_INT);
            $stmt->execute();
            $cuenta = $stmt->fetch();

            // Si ya se utilizó, deshabilitarla...
            if ($cuenta && $cuenta['cuenta'] > 0) {
                // Consulta para deshabilitar la pregunta
                $stmt = $this->pdo->prepare("UPDATE pregunta SET status = NOT status WHERE id = :id_pregunta");
                $stmt->bindParam(':id_pregunta', $id, PDO::PARAM_INT);

                // Ejecutar la consulta de actualización
                $stmt->execute();

                if (! $stmt->rowCount()) {
                    $this->pdo->rollback();
                    return ['status' => 'error', 'message' => 'No se pudo eliminar en "Pregunta".'];
                }

                $this->pdo->commit();
                return ['status' => 'ok'];
            }

            // Si no ha respondido una encuesta, eliminarlo...
            // Consulta para eliminar la pregunta
            $stmt = $this->pdo->prepare("DELETE FROM pregunta WHERE id = :id_pregunta");
            $stmt->bindParam(':id_pregunta', $id, PDO::PARAM_INT);

            // Ejecutar la consulta de eliminación
            $stmt->execute();

            // Comprobar que se haya eliminado
            if (! $stmt->rowCount()) {
                $this->pdo->rollback();
                return ['status' => 'error', 'message' => 'No se pudo eliminar en "Pregunta".'];
            }

            $this->pdo->commit();
            return ['status' => 'ok'];
        } catch (\Throwable $th) {
            $this->pdo->rollback();
            return ['status' => 'error', 'message' => 'Ocurrió un error.'];
        }
    }

    // Método para recuperar a los periodos de aplicacion
    public function getPeriodos()
    {
        // Consulta para obtener los datos
        $stmt = $this->pdo->prepare("SELECT * FROM aplicacion WHERE YEAR(inicio) >= 2026 ORDER BY inicio DESC");
        $stmt->execute();

        // Verificamos si se encontraron resultados...
        if ($stmt->rowCount() > 0) {
            $results = [];
            while ($row = $stmt->fetch()) {
                $result = [];
                $result += ['id' => $row['id']];
                $result += ['inicio' => $row['inicio']];
                $result += ['fin' => $row['fin']];
                $result += ['tipo' => $row['tipo']];
                $result += ['status' => $row['status']];
                $results[] = $result;
            }

            return ['status' => 'ok', 'data' => $results];
        } else {
            return ['status' => 'ok', 'data' => []];  // No se encontraron resultados
        }
    }

    // Método para recuperar a los periodos de aplicacion disponibles
    public function getAplicaciones()
    {
        // Consulta para obtener los datos
        $stmt = $this->pdo->prepare("SELECT * from aplicacion WHERE inicio <= CURDATE() and fin >= CURDATE() and status = 1 and tipo = 0");
        $stmt->execute();

        // Verificamos si se encontraron resultados...
        if ($stmt->rowCount() > 0) {
            $results = [];
            while ($row = $stmt->fetch()) {
                $result = [];
                $result += ['id' => $row['id']];
                $result += ['inicio' => $row['inicio']];
                $result += ['fin' => $row['fin']];
                $result += ['status' => $row['status']];
                $results[] = $result;
            }

            return ['status' => 'ok', 'data' => $results];
        } else {
            return ['status' => 'ok', 'data' => []];  // No se encontraron resultados
        }
    }

    // Método para recuperar a los periodos de aplicacion disponibles para encuesta complementaria
    public function getAplicacionesExtra()
    {
        // Consulta para obtener los datos
        $stmt = $this->pdo->prepare("SELECT * from aplicacion WHERE inicio <= CURDATE() and fin >= CURDATE() and status = 1 and tipo = 1");
        $stmt->execute();

        // Verificamos si se encontraron resultados...
        if ($stmt->rowCount() > 0) {
            $results = [];
            while ($row = $stmt->fetch()) {
                $result = [];
                $result += ['id' => $row['id']];
                $result += ['inicio' => $row['inicio']];
                $result += ['fin' => $row['fin']];
                $result += ['status' => $row['status']];
                $results[] = $result;
            }

            return ['status' => 'ok', 'data' => $results];
        } else {
            return ['status' => 'ok', 'data' => []];  // No se encontraron resultados
        }
    }

    // Método para recuperar a los periodos contestados por un alumno
    public function getMisAplicaciones($data)
    {
        // Consulta para obtener los datos
        $stmt = $this->pdo->prepare("SELECT DISTINCT t1.* FROM aplicacion AS t1
                                        JOIN alumno_pregunta AS t2
                                        ON t1.id = t2.id_aplicacion
                                        WHERE t2.id_alumno = :id_alumno AND t1.tipo = 0");
        $stmt->execute([
            "id_alumno" => $data['id_alumno']
        ]);

        // Verificamos si se encontraron resultados...
        if ($stmt->rowCount() > 0) {
            $results = [];
            while ($row = $stmt->fetch()) {
                $result = [];
                $result += ['id' => $row['id']];
                $result += ['inicio' => $row['inicio']];
                $result += ['fin' => $row['fin']];
                $result += ['status' => $row['status']];
                $results[] = $result;
            }

            return ['status' => 'ok', 'data' => $results];
        } else {
            return ['status' => 'ok', 'data' => []];  // No se encontraron resultados
        }
    }

    // Método para recuperar a los periodos contestados por un alumno de encuesta complementaria
    public function getMisAplicacionesExtra($data)
    {
        // Consulta para obtener los datos
        $stmt = $this->pdo->prepare("SELECT DISTINCT t1.* FROM aplicacion AS t1
                                        JOIN complemento AS t2
                                        ON t1.id = t2.id_aplicacion
                                        WHERE t2.id_alumno = :id_alumno AND t1.tipo = 1");
        $stmt->execute([
            "id_alumno" => $data['id_alumno']
        ]);

        // Verificamos si se encontraron resultados...
        if ($stmt->rowCount() > 0) {
            $results = [];
            while ($row = $stmt->fetch()) {
                $result = [];
                $result += ['id' => $row['id']];
                $result += ['inicio' => $row['inicio']];
                $result += ['fin' => $row['fin']];
                $result += ['status' => $row['status']];
                $results[] = $result;
            }

            return ['status' => 'ok', 'data' => $results];
        } else {
            return ['status' => 'ok', 'data' => []];  // No se encontraron resultados
        }
    }

    // Método para recuperar a las respuestas de una encuesta
    public function getMiEncuesta($data)
    {
        // Consulta para obtener los datos
        $stmt = $this->pdo->prepare("SELECT DISTINCT t1.*, t2.valor FROM pregunta AS t1
                                        JOIN alumno_pregunta AS t2
                                        ON t1.id = t2.id_pregunta
                                        WHERE t2.id_alumno = :id_alumno
                                        AND t2.id_aplicacion = :id_aplicacion");
        $stmt->execute([
            "id_alumno" => $data['id_alumno'],
            "id_aplicacion" => $data['id_aplicacion']
        ]);

        // Verificamos si se encontraron resultados...
        if ($stmt->rowCount() > 0) {
            $results = [];
            while ($row = $stmt->fetch()) {
                $result = [];
                $result += ['id' => $row['id']];
                $result += ['pregunta' => $row['pregunta']];
                $result += ['categoria' => $row['categoria']];
                $result += ['valor' => $row['valor']];
                $results[] = $result;
            }

            return ['status' => 'ok', 'data' => $results];
        } else {
            return ['status' => 'ok', 'data' => []];  // No se encontraron resultados
        }
    }

    // Registrar periodo
    public function registerPeriodo($data)
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO aplicacion (inicio, fin, tipo) VALUES (:inicio, :fin, :tipo)");
            $stmt->execute([
                'inicio' => $data['inicio'],
                'fin' => $data['fin'],
                'tipo' => $data['tipo'],
            ]);

            if ($stmt) {
                return ['status' => 'ok'];
            } else {
                return ['status' => 'error', 'message' => 'No se pudo agregar en "Aplicacion".'];
            }
        } catch (\Throwable $th) {
            return ['status' => 'error', 'message' => 'Ocurrió un error.'];
        }
    }

    // Método para eliminar un periodo
    public function deletePeriodo($id)
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("SELECT COUNT(*) AS cuenta FROM alumno_pregunta WHERE id_aplicacion = :id_aplicacion");
            $stmt->bindParam(':id_aplicacion', $id, PDO::PARAM_INT);
            $stmt->execute();
            $cuenta = $stmt->fetch();

            // Si ya se utilizó, deshabilitarla...
            if ($cuenta && $cuenta['cuenta'] > 0) {
                // Consulta para deshabilitar la aplicacion
                $stmt = $this->pdo->prepare("UPDATE aplicacion SET status = NOT status WHERE id = :id_aplicacion");
                $stmt->bindParam(':id_aplicacion', $id, PDO::PARAM_INT);

                // Ejecutar la consulta de actualización
                $stmt->execute();

                if (! $stmt->rowCount()) {
                    $this->pdo->rollback();
                    return ['status' => 'error', 'message' => 'No se pudo eliminar en "Aplicacion".'];
                }

                $this->pdo->commit();
                return ['status' => 'ok'];
            }

            // Si no ha respondido una encuesta, eliminarlo...
            // Consulta para eliminar la aplicacion
            $stmt = $this->pdo->prepare("DELETE FROM aplicacion WHERE id = :id_aplicacion");
            $stmt->bindParam(':id_aplicacion', $id, PDO::PARAM_INT);

            // Ejecutar la consulta de eliminación
            $stmt->execute();

            // Comprobar que se haya eliminado
            if (! $stmt->rowCount()) {
                $this->pdo->rollback();
                return ['status' => 'error', 'message' => 'No se pudo eliminar en "Aplicacion".'];
            }

            $this->pdo->commit();
            return ['status' => 'ok'];
        } catch (\Throwable $th) {
            $this->pdo->rollback();
            return ['status' => 'error', 'message' => 'Ocurrió un error.'];
        }
    }
}
