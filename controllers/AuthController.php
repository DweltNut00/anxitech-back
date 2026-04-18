<?php
require_once __DIR__ . '/../models/Usuario.php';

class AuthController {
    public function login($username, $password) {
        global $pdo;
        $user = new Usuario($pdo);
        return $user->login($username, $password);
    }

    public function validateEmail($email) {
        global $pdo;
        $user = new Usuario($pdo);
        return $user->validateEmail($email);
    }

    public function validateUsername($username) {
        global $pdo;
        $user = new Usuario($pdo);
        return $user->validateUsername($username);
    }

    public function validateNoControl($nocontrol) {
        global $pdo;
        $user = new Usuario($pdo);
        return $user->validateNoControl($nocontrol);
    }

    public function register($username, $nombre, $apellido, $email, $password, $nocontrol, $sexo, $fechan, $estadoc, $ciudad, $estado) {
        global $pdo;
        $user = new Usuario($pdo);
        return $user->register($username, $nombre, $apellido, $email, $password, $nocontrol, $sexo, $fechan, $estadoc, $ciudad, $estado);
    }

    public function registerAdmin($username, $nombre, $apellido, $email, $password, $admin) {
        global $pdo;
        $user = new Usuario($pdo);
        return $user->registerAdmin($username, $nombre, $apellido, $email, $password, $admin);
    }

    public function recoverPassword($email) {
        // pendiente
    }

    public function resetPassword($email, $newPassword) {
        global $pdo;
        $user = new Usuario($pdo);
        return $user->resetPassword($email, $newPassword);
    }
}
?>