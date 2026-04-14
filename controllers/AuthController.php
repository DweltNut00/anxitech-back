<?php
require_once '..\config\db.php'; // Asegúrate de que la conexión PDO esté disponible
require_once '..\models\Usuario.php';
require_once __DIR__ . '/../config/cors.php';

class AuthController {
    public function login($username, $password) {
        global $pdo; // Usa la variable global $pdo
        $user = new Usuario($pdo); // Pasa el objeto PDO al constructor de la clase Usuario
        return $user->login($username, $password);
    }

    public function validateEmail($email) {
        global $pdo; // Usa la variable global $pdo
        $user = new Usuario($pdo); // Pasa el objeto PDO al constructor de la clase User
        return $user->validateEmail($email);
    }

    public function validateUsername($username) {
        global $pdo; // Usa la variable global $pdo
        $user = new Usuario($pdo); // Pasa el objeto PDO al constructor de la clase User
        return $user->validateUsername($username);
    }

    public function validateNoControl($nocontrol) {
    global $pdo;
    $user = new Usuario($pdo);
    return $user->validateNoControl($nocontrol);
    }

    public function register($username, $nombre, $apellido, $email, $password, $nocontrol, $sexo, $fechan, $estadoc, $ciudad, $estado) {
        global $pdo; // Usa la variable global $pdo
        $user = new Usuario($pdo); // Pasa el objeto PDO al constructor de la clase User
        return $user->register($username, $nombre, $apellido, $email, $password, $nocontrol, $sexo, $fechan, $estadoc, $ciudad, $estado);
    }

    public function registerAdmin($username, $nombre, $apellido, $email, $password, $admin) {
        global $pdo; // Usa la variable global $pdo
        $user = new Usuario($pdo); // Pasa el objeto PDO al constructor de la clase User
        return $user->registerAdmin($username, $nombre, $apellido, $email, $password, $admin);
    }

    public function recoverPassword($email) {
        // Enviar enlace de recuperación de contraseña por email
    }

    public function resetPassword($email, $newPassword) {
        global $pdo; // Usa la variable global $pdo
        $user = new User($pdo); // Pasa el objeto PDO al constructor de la clase User
        return $user->resetPassword($email, $newPassword);
    }
}
?>

