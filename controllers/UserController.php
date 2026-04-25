<?php
require_once __DIR__ . '/../models/Usuario.php';

class UserController {

    public function enviarCodigo($destino) {
        global $pdo;
        $user = new Usuario($pdo);
        $codigo = $user->generarCodigo();
        try {
            $guardado = $user->guardarCodigo($destino, $codigo);
            if ($guardado['status'] == 'ok') {
                return $user->enviarCorreo($destino, $codigo);
            } else {
                return $guardado;
            }
        } catch (\Throwable $th) {
            return ['status' => 'error', 'message' => 'No se pudo generar el código.'];
        }
    }

    public function validarCodigo($email, $codigo) {
        global $pdo;
        $user = new Usuario($pdo);
        return $user->validarCodigo($email, $codigo);
    }

    public function actualizarPass($email, $pass) {
        global $pdo;
        $user = new Usuario($pdo);
        return $user->actualizarPass($email, $pass);
    }

    public function cargaMasiva($archivo) {
        global $pdo;
        $user = new Usuario($pdo);
        return $user->cargaMasiva($archivo);
    }

    public function getAlumnos($page = 1, $perPage = 50, $search = '') {
        global $pdo;
        $user = new Usuario($pdo);
        return $user->getAlumnos($page, $perPage, $search);
    }

    public function getAlumno($id) {
        global $pdo;
        $user = new Usuario($pdo);
        return $user->getAlumno($id);
    }

    public function getAdmin($id) {
        global $pdo;
        $user = new Usuario($pdo);
        return $user->getAdmin($id);
    }

    public function getAdmins() {
        global $pdo;
        $user = new Usuario($pdo);
        return $user->getAdmins();
    }

    public function deleteAdmin($id) {
        global $pdo;
        $user = new Usuario($pdo);
        return $user->deleteAdmin($id);
    }

    public function deleteAlumno($id) {
        global $pdo;
        $user = new Usuario($pdo);
        return $user->deleteAlumno($id);
    }

    public function updateAlumno($userId, $data) {
        global $pdo;
        $user = new Usuario($pdo);
        return $user->updateAlumno($userId, $data);
    }

    public function updateAdmin($userId, $data) {
        global $pdo;
        $user = new Usuario($pdo);
        return $user->updateAdmin($userId, $data);
    }

    public function updateTema($userId, $tema) {
        global $pdo;
        $user = new Usuario($pdo);
        return $user->updateTheme($userId, $tema);
    }
}
?>