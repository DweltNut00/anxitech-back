<?php
// Incluir la conexión PDO y el modelo User
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Usuario.php';


class UserController {
    // Método para enviar código
 public function enviarCodigo($destino) {
    global $pdo;
    $user = new Usuario($pdo);
    $codigo = $user->generarCodigo();

    try {
        $guardado = $user->guardarCodigo($destino, $codigo);

        // ← ANTES: if ($guardado)
        if ($guardado['status'] == 'ok') {
            return $user->enviarCorreo($destino, $codigo);
        } else {
            return $guardado; // retorna ['status'=>'error', 'message'=>'No existe una cuenta con ese correo.']
        }
    } catch (\Throwable $th) {
        return ['status' => 'error', 'message' => 'No se pudo generar el código, prueba otra vez.'];
    }
}

    // Método para validar el código
    public function validarCodigo($email, $codigo) {
        // Crear una instancia del modelo User pasando la conexión PDO
        global $pdo;
        $user = new Usuario($pdo);
        return $user->validarCodigo($email, $codigo);
    }

    // Método para actualizar la contraseña
    public function actualizarPass($email, $pass) {
        // Crear una instancia del modelo User pasando la conexión PDO
        global $pdo;
        $user = new Usuario($pdo);
        return $user->actualizarPass($email, $pass);
    }

    // Método para cargar alumnos masivamente
    public function cargaMasiva($archivo) {
        // Crear una instancia del modelo User pasando la conexión PDO
        global $pdo;
        $user = new Usuario($pdo);
        return $user->cargaMasiva($archivo);
    }

    // Método para obtener los datos del usuario
   public function getAlumnos($page = 1, $perPage = 50, $search = '') {
    global $pdo;
    $user = new Usuario($pdo);
    return $user->getAlumnos($page, $perPage, $search);
}

    // Método para obtener los datos de un alumno
    public function getAlumno($id) {
        // Crear una instancia del modelo User pasando la conexión PDO
        global $pdo;
        $user = new Usuario($pdo);
        return $user->getAlumno($id);
    }

    // Método para obtener a un admin
    public function getAdmin($id) {
        // Crear una instancia del modelo User pasando la conexión PDO
        global $pdo;
        $user = new Usuario($pdo);
        return $user->getAdmin($id);
    }

    // Método para obtener a todos los admins
    public function getAdmins() {
        // Crear una instancia del modelo User pasando la conexión PDO
        global $pdo;
        $user = new Usuario($pdo);
        return $user->getAdmins();
    }

    public function deleteAdmin($id) {
        // Crear una instancia del modelo User pasando la conexión PDO
        global $pdo;
        $user = new Usuario($pdo);
        return $user->deleteAdmin($id);
    }

    public function deleteAlumno($id) {
        // Crear una instancia del modelo User pasando la conexión PDO
        global $pdo;
        $user = new Usuario($pdo);
        return $user->deleteAlumno($id);
    }

    // Método para actualizar los datos de un alumno
    public function updateAlumno($userId, $data) {
        // Crear una instancia del modelo User pasando la conexión PDO
        global $pdo;
        $user = new Usuario($pdo);
        return $user->updateAlumno($userId, $data);
    }

    // Método para actualizar los datos de un admin
    public function updateAdmin($userId, $data) {
        // Crear una instancia del modelo User pasando la conexión PDO
        global $pdo;
        $user = new Usuario($pdo);
        return $user->updateAdmin($userId, $data);
    }

    // Método para actualizar el tema
    public function updateTema($userId, $tema) {
        // Crear una instancia del modelo User pasando la conexión PDO
        global $pdo;
        $user = new Usuario($pdo);
        return $user->updateTheme($userId, $tema);
    }

    // Método para actualizar los datos del usuario
    public function updateUserData($userId, $data) {
        // Crear una instancia del modelo User pasando la conexión PDO
        global $pdo;
        $user = new User($pdo);
        return $user->updateUserData($userId, $data);
    }

    // Método para registrar un nuevo usuario
    public function registerUser($username, $email, $password) {
        // Crear una instancia del modelo User
        global $pdo;
        $user = new User($pdo);
        return $user->register($username, $email, $password);
    }

}
?>

