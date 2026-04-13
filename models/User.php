<?php
class User
{
    private $pdo;

    // Constructor que recibe la conexión PDO
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Método para iniciar sesión
    public function login($username, $password)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        } else {
            return null;
        }
    }

    // Método para registrar un usuario
    public function register($username, $email, $password)
    {
        // Verificar si el correo electrónico ya está registrado
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            return null; // Ya existe un usuario con este correo
        }

        // Si no existe, registrar el nuevo usuario
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword
        ]);

        return $this->pdo->lastInsertId();
    }

    // Método para restablecer la contraseña
    public function resp($email, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare("UPDATE users SET password = :password WHERE email = :email");
        $stmt->execute(['password' => $hashedPassword, 'email' => $email]);

        return $stmt->rowCount();
    }

    // Método para obtener los datos del usuario
    public function getUserData($userId)
    {
        // Consulta para obtener los datos del usuario por ID
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :user_id LIMIT 1");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        // Verificamos si se encontró el usuario
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);  // Retorna los datos del usuario
        } else {
            return null;  // No se encontró el usuario
        }
    }

    // Método para actualizar los datos del usuario
    public function updateUserData($userId, $data)
    {
        // Consulta para actualizar los datos del usuario
        $stmt = $this->pdo->prepare("UPDATE users SET username = :username, email = :email WHERE id = :user_id");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':username', $data['username'], PDO::PARAM_STR);
        $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);

        // Ejecutar la consulta de actualización
        return $stmt->execute();
    }

    // Método para restablecer la contraseña
    public function resetPassword($userId, $newPassword)
    {
        // Verificar si el usuario existe
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :user_id LIMIT 1");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si el usuario existe y la contraseña anterior es correcta
        if ($user) {
            // Hashear la nueva contraseña
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            // Actualizar la contraseña
            $stmt = $this->pdo->prepare("UPDATE users SET password = :password WHERE id = :user_id");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
            return $stmt->execute(); // Devuelve true si se ejecutó correctamente
        }

        return false; // Si la contraseña actual no coincide
    }
}
