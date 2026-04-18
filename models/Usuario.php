<?php
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Usuario
{
    private $pdo;

    // Constructor que recibe la conexión PDO
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Método para generar un código

    public function generarCodigo($longitud = 6)
    {
        $caracteres = '0123456789';
        $codigo = '';

        for ($i = 0; $i < $longitud; $i++) {
            $codigo .= $caracteres[rand(0, strlen($caracteres) - 1)];
        }

        return $codigo;
    }

    public function prepararCorreo()
    {
        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'anxitech2002@gmail.com';
            $mail->Password   = 'guhkzbbtfpycabty';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = '587';

            // Remitente y destinatario
            $mail->setFrom('anxitech2002@gmail.com', 'Equipo de soporte Anxitech');

            return $mail;
        } catch (Exception $e) {
            error_log("Error al preparar el correo: " . $mail->ErrorInfo, 0);
            return null;
        }
    }

    public function enviarCorreo($destinatario, $codigo)
    {
        try {
            $mail = $this->prepararCorreo();
            $mail->addAddress($destinatario);

            // Contenido del correo
            $mail->isHTML(true);
            $subject = 'Tu código de verificación';
            $subject = "=?UTF-8?B?" . base64_encode($subject) . "=?=";
            $mail->Subject = $subject;
            $mail->Body    = '
                <h1>Tu código de verificación</h1>
                <p>Utiliza el siguiente código para verificar tu cuenta:</p>
                <h2 style="background: #f4f4f4; padding: 10px; display: inline-block;">' . $codigo . '</h2>
                <p>Este código expirará en 15 minutos.</p>
                <p>Si no has solicitado un cambio de contraseña, ignora este mensaje.</p>
            ';
            $mail->AltBody = 'Tu código de verificación es: ' . $codigo . ' (válido por 15 minutos)';

            $mail->send();
            return ['status' => 'ok'];
        } catch (Exception $e) {
            error_log("Error al enviar correo: " . $mail->ErrorInfo, 0);
            return ['status' => 'error', 'message' => 'No se pudo enviar el correo.'];
        }
    }

    public function guardarCodigo($email, $codigo)
{
    $result = $this->validateEmail($email);

    // validateEmail retorna 'ok' si el correo NO existe en la BD
    if ($result['status'] == 'ok') {
        return ['status' => 'error', 'message' => 'No existe una cuenta con ese correo.'];
    }

    $fecha_expiracion = date('Y-m-d H:i:s', time() + 900);

    try {
        $stmt = $this->pdo->prepare("UPDATE codigo_password SET valido = 0 WHERE email = ?");
        $stmt->execute([$email]);

        $stmt = $this->pdo->prepare("INSERT INTO codigo_password (email, codigo, fecha_expiracion, valido) VALUES (?, ?, ?, 1)");
        $stmt->execute([$email, $codigo, $fecha_expiracion]);

        return ['status' => 'ok'];  // ← antes decía: return true
    } catch (PDOException $e) {
        error_log("Error en guardarCodigoEnDB: " . $e->getMessage());
        return ['status' => 'error', 'message' => 'Error al guardar el código.']; // ← antes decía: return false
    }
}

    public function validarCodigo($email, $codigo)
    {
        try {

            // Buscar el código válido más reciente para este email
            $stmt = $this->pdo->prepare("
                SELECT codigo, fecha_expiracion 
                FROM codigo_password 
                WHERE email = ? AND valido = 1 AND fecha_expiracion > NOW()
                ORDER BY id DESC 
                LIMIT 1
            ");
            $stmt->execute([$email]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$resultado) {
                return ['status' => 'error', 'message' => 'Código no encontrado.'];
            }

            // Verificar el código
            if ($resultado['codigo'] !== $codigo) {
                return ['status' => 'error', 'message' => 'Código incorrecto.'];
            }

            // Invalidar el código después de usarlo
            $stmt = $this->pdo->prepare("UPDATE codigo_password SET valido = 0 WHERE email = ? AND codigo = ?");
            $stmt->execute([$email, $codigo]);

            return ['status' => 'ok'];
        } catch (PDOException $e) {
            return ['status' => 'error', 'message' => 'Error en el sistema.'];
        }
    }

    public function actualizarPass($email, $pass)
    {
        try {
            $hashedPassword = password_hash($pass, PASSWORD_BCRYPT);

            // Consulta para actualizar la contraseña
            $stmt = $this->pdo->prepare("UPDATE usuario SET password = :pass WHERE email = :email");
            $stmt->bindParam(':pass', $hashedPassword, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);

            // Ejecutar la consulta de actualización
            $stmt->execute();

            $this->enviarConfirmacion($email);

            return ['status' => 'ok'];
        } catch (\Throwable $th) {
            return ['status' => 'error', 'message' => 'Ocurrió un error.'];
        }
    }

    public function enviarConfirmacion($destinatario)
    {
        try {
            $mail = $this->prepararCorreo();
            $mail->addAddress($destinatario);

            // Contenido del correo
            $mail->isHTML(true);
            $subject = 'Actualización de contraseña';
            $subject = "=?UTF-8?B?" . base64_encode($subject) . "=?=";
            $mail->Subject = $subject;
            $mail->Body    = '
                <h1>Tu contraseña se actualizó con éxito</h1>
                <p>Hemos recibido tu solicitud de cambio de contraseña y ha sido actualizada sin problemas.</p>
                <p>Si no has solicitado un cambio de contraseña, comunícate con nosotros cuanto antes.</p>
            ';
            $mail->AltBody = 'Aviso de cambio de contraseña.';

            $mail->send();
            return ['status' => 'ok'];
        } catch (Exception $e) {
            error_log("Error al enviar correo: " . $mail->ErrorInfo, 0);
            return ['status' => 'error', 'message' => 'No se pudo enviar el correo.'];
        }
    }

    // Método para iniciar sesión
    public function login($username, $password)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM usuario WHERE usuario = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password']) && $user['status']) {
            $stmt = $this->pdo->prepare('SELECT * FROM alumno WHERE id = :id');
            $stmt->execute([':id' => $user['id']]);
            $data = ['id' => $user['id'], 'nombre' => $user['nombre'], 'apellido' => $user['apellido'], 'tema' => $user['tema']];
            $alumno = $stmt->fetch();

            if ($alumno)
                return ['status' => 'ok', 'data' => array_merge(['tipo' => 'Alumno'], $data)];

            $stmt = $this->pdo->prepare('SELECT * FROM admin WHERE id = :id');
            $stmt->execute([':id' => $user['id']]);
            $admin = $stmt->fetch();

            if ($admin)
                return ['status' => 'ok', 'data' => array_merge(['tipo' => 'Admin', 'permisos' => $admin['permisos']], $data)];

            return ['status' => 'error', 'message' => 'No hay una cuenta con esos datos.'];
        } else {
            return ['status' => 'error', 'message' => 'No hay una cuenta con esos datos.'];
        }
    }

    public function validateNoControl($nocontrol)
{
    $stmt = $this->pdo->prepare("SELECT * FROM alumno WHERE nocontrol = :nocontrol");
    $stmt->execute(['nocontrol' => $nocontrol]);
    $alumno = $stmt->fetch();

    if ($alumno) {
        return ['status' => 'error', 'message' => 'Número de control ya registrado.'];
    }

    return ['status' => 'ok'];
}
    
    // Valida que el e-mail no esté ocupado
    public function validateEmail($email)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM usuario WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            return ['status' => 'error', 'message' => 'Correo electrónico ocupado.']; // Ya existe un usuario con este correo
        }

        return ['status' => 'ok'];
    }

    // Valida que el nombre de usuario no esté ocupado
    public function validateUsername($username)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM usuario WHERE usuario = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user) {
            return ['status' => 'error', 'message' => 'Nombre de usuario ocupado.']; // Ya existe un usuario con este nombre de usuario
        }

        return ['status' => 'ok'];
    }

    // Método para registrar alumnos masivamente
    public function cargaMasiva($archivo)
    {
        $tmpFile = $archivo;
        $spreadsheet = IOFactory::load($tmpFile);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // Eliminar encabezados si existen
        array_shift($rows);

        $results = [
            'status' => 'ok',
            'success' => 0,
            'errors' => []
        ];

        foreach ($rows as $index => $row) {
            // Validar estructura del archivo
            if (count($row) < 11) {
                $results['errors'][] = "Fila $index: Estructura incorrecta";
                continue;
            }

            // Mapear datos
            $data = [
                'username'  => $row[0],
                'nombre'    => $row[1],
                'apellido' => $row[2],
                'email'     => $row[3],
                'password'  => $row[4],
                'nocontrol' => $row[5],
                'sexo'     => $row[6],
                'fechan'   => $row[7],
                'estadoc'  => $row[8],
                'ciudad'    => $row[9],
                'estado'    => $row[10]
            ];

            // Validaciones adicionales
            $errorMessages = [];

            // 1. Validar nocontrol (8 dígitos)
            if (!preg_match('/^\d{8}$/', $data['nocontrol'])) {
                $errorMessages[] = "Nocontrol debe tener 8 dígitos";
            }

            // 2. Validar sexo (F/M)
            if (!in_array(strtoupper($data['sexo']), ['F', 'M'])) {
                $errorMessages[] = "Sexo debe ser F o M";
            }

            // 3. Validar fecha (formato yyyy-mm-dd)
            $fecha = DateTime::createFromFormat('Y-m-d', $data['fechan']);
            if (!$fecha || $fecha->format('Y-m-d') !== $data['fechan']) {
                $errorMessages[] = "Formato de fecha inválido (usar yyyy-mm-dd)";
            }

            // 4. Validar estado civil
            $estadosValidos = ['Casado', 'Soltero', 'Divorciado', 'Viudo'];
            if (!in_array(ucfirst(strtolower($data['estadoc'])), $estadosValidos)) {
                $errorMessages[] = "Estado civil inválido";
            }

            // Si hay errores de validación
            if (!empty($errorMessages)) {
                $results['errors'][] = "Fila $index: " . implode(', ', $errorMessages);
                continue; // Saltar a la siguiente fila
            }

            // Validaciones
            $emailCheck = $this->validateEmail($data['email']);
            $userCheck = $this->validateUsername($data['username']);

            if ($emailCheck['status'] === 'error') {
                $results['errors'][] = "Fila $index: " . $emailCheck['message'];
                continue;
            }

            if ($userCheck['status'] === 'error') {
                $results['errors'][] = "Fila $index: " . $userCheck['message'];
                continue;
            }

            // Registrar usuario
            $registration = $this->register(
                $data['username'],
                $data['nombre'],
                $data['apellido'],
                $data['email'],
                $data['password'],
                $data['nocontrol'],
                $data['sexo'],
                $data['fechan'],
                $data['estadoc'],
                $data['ciudad'],
                $data['estado']
            );

            if ($registration['status'] === 'ok') {
                $results['success']++;
            } else {
                $results['errors'][] = "Fila $index: " . ($registration['message'] ?? 'Error desconocido');
            }
        }

        return $results;
    }

    // Método para registrar un usuario (alumno, específicamente)
    public function register($username, $nombre, $apellido, $email, $password, $nocontrol, $sexo, $fechan, $estadoc, $ciudad, $estado)
    {
        try {
            // Verificar si el correo electrónico ya está registrado
            $stmt = $this->pdo->prepare("SELECT * FROM usuario WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user) {
                return ['status' => 'error', 'message' => 'Correo electrónico ocupado.']; // Ya existe un usuario con este correo
            }

            $this->pdo->beginTransaction();

            // Si no existe, registrar el nuevo usuario
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $this->pdo->prepare("INSERT INTO usuario (usuario, nombre, apellido, email, password) VALUES (:username, :nombre, :apellido, :email, :password)");
            $stmt->execute([
                'username' => $username,
                'nombre' => $nombre,
                'apellido' => $apellido,
                'email' => $email,
                'password' => $hashedPassword
            ]);

            if ($stmt) {
                $id = $this->pdo->lastInsertId();
                $stmt = $this->pdo->prepare("INSERT INTO alumno (id, nocontrol, fechan, sexo, estadoc, ciudad, estado) VALUES (:id, :nocontrol, :fechan, :sexo, :estadoc, :ciudad, :estado)");
                $stmt->execute([
                    'id' => $id,
                    'nocontrol' => $nocontrol,
                    'fechan' => $fechan,
                    'sexo' => $sexo,
                    'estadoc' => $estadoc,
                    'ciudad' => $ciudad,
                    'estado' => $estado
                ]);

                if ($stmt) {
                    $this->pdo->commit();

                    return ['status' => 'ok'];
                } else {
                    $this->pdo->rollback();

                    return ['status' => 'error', 'message' => 'No se pudo agregar en "Alumno".'];
                }
            } else {
                $this->pdo->rollback();

                return ['status' => 'error', 'message' => 'No se pudo agregar en "Usuario".'];
            }
        } catch (\Throwable $th) {
            $this->pdo->rollback();
        }
    }

    // Método para registrar a un administrador
    public function registerAdmin($username, $nombre, $apellido, $email, $password, $permisos)
    {
        try {
            // Verificar si el correo electrónico ya está registrado
            $stmt = $this->pdo->prepare("SELECT * FROM usuario WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user) {
                return ['status' => 'error', 'message' => 'Correo electrónico ocupado.']; // Ya existe un usuario con este correo
            }

            $this->pdo->beginTransaction();

            // Si no existe, registrar el nuevo usuario
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $this->pdo->prepare("INSERT INTO usuario (usuario, nombre, apellido, email, password) VALUES (:username, :nombre, :apellido, :email, :password)");
            $stmt->execute([
                'username' => $username,
                'nombre' => $nombre,
                'apellido' => $apellido,
                'email' => $email,
                'password' => $hashedPassword
            ]);

            if ($stmt) {
                $id = $this->pdo->lastInsertId();
                $stmt = $this->pdo->prepare("INSERT INTO admin (id, permisos) VALUES (:id, :permisos)");
                $stmt->execute([
                    'id' => $id,
                    'permisos' => $permisos
                ]);

                if ($stmt) {
                    $this->pdo->commit();

                    return ['status' => 'ok'];
                } else {
                    $this->pdo->rollback();

                    return ['status' => 'error', 'message' => 'No se pudo agregar en "Admin".'];
                }
            } else {
                $this->pdo->rollback();

                return ['status' => 'error', 'message' => 'No se pudo agregar en "Usuario".'];
            }
        } catch (\Throwable $th) {
            $this->pdo->rollback();
        }
    }

    
public function getAlumnos($page = 1, $perPage = 50, $search = '') {
    $offset = ($page - 1) * $perPage;
    
    // Query base
    $baseQuery = "FROM `usuario` AS t1 JOIN `alumno` AS t2 ON t1.id = t2.id";
    
    // Agregar búsqueda si existe
    $whereClause = "";
    $params = [];
    if (!empty($search)) {
        $whereClause = " WHERE t2.nocontrol LIKE :search 
                         OR t1.nombre LIKE :search 
                         OR t1.apellido LIKE :search 
                         OR t1.email LIKE :search";
        $params[':search'] = "%$search%";
    }
    
    // Contar total de registros
    $countStmt = $this->pdo->prepare("SELECT COUNT(*) as total " . $baseQuery . $whereClause);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $total = $countStmt->fetch()['total'];
    
    // Obtener registros paginados
    $stmt = $this->pdo->prepare("SELECT t1.*, t2.nocontrol, t2.fechan, t2.sexo, 
                                  t2.estadoc, t2.ciudad, t2.estado " 
                                  . $baseQuery . $whereClause . 
                                  " ORDER BY t1.id DESC 
                                    LIMIT :limit OFFSET :offset");
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = [
                'id' => $row['id'],
                'nombre' => $row['nombre'],
                'apellido' => $row['apellido'],
                'email' => $row['email'],
                'status' => $row['status'],
                'creacion' => $row['creacion'],
                'nocontrol' => $row['nocontrol'],
                'fechan' => $row['fechan'],
                'sexo' => $row['sexo'],
                'estadoc' => $row['estadoc'],
                'ciudad' => $row['ciudad'],
                'estado' => $row['estado']
            ];
        }
        
        return [
            'status' => 'ok', 
            'data' => $results,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    } else {
        return [
            'status' => 'ok', 
            'data' => [],
            'total' => 0,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => 0
        ];
    }
}

    // Método para recuperar a un alumno
    public function getAlumno($id)
    {
        // Consulta para obtener los datos del usuario por ID
        $stmt = $this->pdo->prepare("SELECT t1.*, t2.nocontrol, t2.fechan, t2.sexo, t2.estadoc, t2.ciudad, t2.estado
                                        FROM `usuario` AS t1 JOIN `alumno` AS t2 ON t1.id = t2.id
                                        WHERE t2.id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Verificamos si se encontró el usuario
        if ($stmt->rowCount() > 0) {
            $result = [];
            while ($row = $stmt->fetch()) {
                $result += ['id' => $row['id']];
                $result += ['nombre' => $row['nombre']];
                $result += ['apellido' => $row['apellido']];
                $result += ['usuario' => $row['usuario']];
                $result += ['email' => $row['email']];
                $result += ['status' => $row['status']];
                $result += ['creacion' => $row['creacion']];
                $result += ['nocontrol' => $row['nocontrol']];
                $result += ['fechan' => $row['fechan']];
                $result += ['sexo' => $row['sexo']];
                $result += ['estadoc' => $row['estadoc']];
                $result += ['ciudad' => $row['ciudad']];
                $result += ['estado' => $row['estado']];
            }

            return ['status' => 'ok', 'data' => $result];
        } else {
            return ['status' => 'ok', 'data' => []];  // No se encontró el usuario
        }
    }

    // Método para recuperar a todos los administradores
    public function getAdmins()
    {
        // Consulta para obtener los datos del usuario por ID
        $stmt = $this->pdo->prepare("SELECT t1.*, t2.permisos
                                        FROM `usuario` AS t1 JOIN `admin` AS t2 ON t1.id = t2.id");
        $stmt->execute();

        // Verificamos si se encontró el usuario
        if ($stmt->rowCount() > 0) {
            $results = [];
            while ($row = $stmt->fetch()) {
                $result = [];
                $result += ['id' => $row['id']];
                $result += ['nombre' => $row['nombre']];
                $result += ['apellido' => $row['apellido']];
                $result += ['usuario' => $row['usuario']];
                $result += ['email' => $row['email']];
                $result += ['status' => $row['status']];
                $result += ['creacion' => $row['creacion']];
                $result += ['permisos' => $row['permisos']];
                $results[] = $result;
            }

            return ['status' => 'ok', 'data' => $results];
        } else {
            return ['status' => 'ok', 'data' => []];  // No se encontró el usuario
        }
    }

    // Método para recuperar a un administrador
    public function getAdmin($id)
    {
        // Consulta para obtener los datos del usuario por ID
        $stmt = $this->pdo->prepare("SELECT t1.*, t2.permisos
                                        FROM `usuario` AS t1 JOIN `admin` AS t2 ON t1.id = t2.id
                                        WHERE t2.id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Verificamos si se encontró el usuario
        if ($stmt->rowCount() > 0) {
            $result = [];
            while ($row = $stmt->fetch()) {
                $result += ['id' => $row['id']];
                $result += ['nombre' => $row['nombre']];
                $result += ['apellido' => $row['apellido']];
                $result += ['usuario' => $row['usuario']];
                $result += ['email' => $row['email']];
                $result += ['status' => $row['status']];
                $result += ['creacion' => $row['creacion']];
                $result += ['permisos' => $row['permisos']];
            }

            return ['status' => 'ok', 'data' => $result];
        } else {
            return ['status' => 'ok', 'data' => []];  // No se encontró el usuario
        }
    }

    // Método para eliminar a un alumno
    public function deleteAlumno($alumnoId)
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("SELECT COUNT(*) AS cuenta FROM alumno_pregunta WHERE id_alumno = :alumno_id");
            $stmt->bindParam(':alumno_id', $alumnoId, PDO::PARAM_INT);
            $stmt->execute();
            $cuenta = $stmt->fetch();

            // Si el alumno ya respondió una encuesta, deshabilitarlo...
            if ($cuenta && $cuenta['cuenta'] > 0) {
                // Consulta para actualizar los datos del alumno
                $stmt = $this->pdo->prepare("UPDATE usuario SET status = NOT status WHERE id = :alumno_id");
                $stmt->bindParam(':alumno_id', $alumnoId, PDO::PARAM_INT);

                // Ejecutar la consulta de actualización
                $stmt->execute();

                if (! $stmt->rowCount()) {
                    $this->pdo->rollback();
                    return ['status' => 'error', 'message' => 'No se pudo eliminar en "Alumno".'];
                }

                $this->pdo->commit();
                return ['status' => 'ok'];
            }

            // Si no ha respondido una encuesta, eliminarlo...
            // Consulta para eliminar al alumno
            $stmt = $this->pdo->prepare("DELETE FROM alumno WHERE id = :alumno_id");
            $stmt->bindParam(':alumno_id', $alumnoId, PDO::PARAM_INT);

            // Ejecutar la consulta de eliminación
            $stmt->execute();

            // Comprobar que se haya eliminado
            if (! $stmt->rowCount()) {
                $this->pdo->rollback();
                return ['status' => 'error', 'message' => 'No se pudo eliminar en "Alumno".'];
            }

            // Consulta para eliminar al usuario
            $stmt = $this->pdo->prepare("DELETE FROM usuario WHERE id = :alumno_id");
            $stmt->bindParam(':alumno_id', $alumnoId, PDO::PARAM_INT);

            // Ejecutar la consulta de eliminación
            $stmt->execute();

            // Comprobar que se haya eliminado
            if (! $stmt->rowCount()) {
                $this->pdo->rollback();
                return ['status' => 'error', 'message' => 'No se pudo eliminar en "Usuario".'];
            }

            $this->pdo->commit();
            return ['status' => 'ok'];
        } catch (Exception $e) {
            $this->pdo->rollback();
            error_log($e->getMessage(), 0);
            return ['status' => 'error', 'message' => 'Ocurrió un error.'];
        }
    }

    // Método para eliminar a un administrador
    public function deleteAdmin($adminId)
    {
        try {
            $this->pdo->beginTransaction();

            // Consulta para actualizar los datos del admin
            $stmt = $this->pdo->prepare("DELETE FROM admin WHERE id = :admin_id");
            $stmt->bindParam(':admin_id', $adminId, PDO::PARAM_INT);

            // Ejecutar la consulta de eliminación
            $stmt->execute();

            // Comprobar que se haya eliminado
            if (! $stmt->rowCount()) {
                $this->pdo->rollback();
                return ['status' => 'error', 'message' => 'No se pudo eliminar en "Admin".'];
            }

            // Consulta para actualizar los datos del usuario
            $stmt = $this->pdo->prepare("DELETE FROM usuario WHERE id = :admin_id");
            $stmt->bindParam(':admin_id', $adminId, PDO::PARAM_INT);

            // Ejecutar la consulta de eliminación
            $stmt->execute();

            // Comprobar que se haya eliminado
            if (! $stmt->rowCount()) {
                $this->pdo->rollback();
                return ['status' => 'error', 'message' => 'No se pudo eliminar en "Usuario".'];
            }

            $this->pdo->commit();
            return ['status' => 'ok'];
        } catch (\Throwable $th) {
            $this->pdo->rollback();
            return ['status' => 'error', 'message' => 'Ocurrió un error.'];
        }
    }

    // Método para cambiar de tema
    public function updateTheme($userId, $theme)
    {
        try {
            // Consulta para obtener los datos del usuario por ID
            $stmt = $this->pdo->prepare("UPDATE usuario SET tema = :theme WHERE id = :id");
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':theme', $theme, PDO::PARAM_INT);
            $stmt->execute();

            return ['status' => 'ok'];
        } catch (\Throwable $th) {
            return ['status' => 'error', 'message' => 'Ocurrió un error.'];
        }
    }

    // Método para actualizar los datos de un alumno
    public function updateAlumno($userId, $data)
    {
        try {
            $this->pdo->beginTransaction();
            // Consulta para actualizar los datos del usuario
            $stmt = $this->pdo->prepare("UPDATE usuario SET nombre = :nombre, apellido = :apellido WHERE id = :user_id");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $data['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(':apellido', $data['apellido'], PDO::PARAM_STR);

            // Ejecutar la consulta de actualización
            $stmt->execute();

            // Actualizar la contraseña solo si es necesario
            if (isset($data['password']) && !empty($data['password'])) {
                $stmt = $this->pdo->prepare("UPDATE usuario SET password = :password WHERE id = :user_id");
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $newPass = password_hash($data['password'], PASSWORD_BCRYPT);
                $stmt->bindParam(':password', $newPass, PDO::PARAM_STR);

                $stmt->execute();
            }

            // Consulta para actualizar los datos del alumno
            $stmt = $this->pdo->prepare("UPDATE alumno SET nocontrol = :nocontrol, fechan = :fechan, sexo = :sexo, estadoc = :estadoc, ciudad = :ciudad, estado = :estado WHERE id = :user_id");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':nocontrol', $data['nocontrol'], PDO::PARAM_STR);
            $stmt->bindParam(':fechan', $data['fechan'], PDO::PARAM_STR);
            $stmt->bindParam(':sexo', $data['sexo'], PDO::PARAM_STR);
            $stmt->bindParam(':estadoc', $data['estadoc'], PDO::PARAM_STR);
            $stmt->bindParam(':ciudad', $data['ciudad'], PDO::PARAM_STR);
            $stmt->bindParam(':estado', $data['estado'], PDO::PARAM_STR);

            // Ejecutar la consulta de actualización
            $stmt->execute();

            $this->pdo->commit();
            return ['status' => 'ok'];
        } catch (\Throwable $th) {
            $this->pdo->rollback();
            return ['status' => 'error', 'message' => 'Ocurrió un error.'];
        }
    }

    // Método para actualizar los datos de un alumno
    public function updateAdmin($userId, $data)
    {
        try {
            $this->pdo->beginTransaction();
            // Consulta para actualizar los datos del usuario
            $stmt = $this->pdo->prepare("UPDATE usuario SET nombre = :nombre, apellido = :apellido WHERE id = :user_id");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $data['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(':apellido', $data['apellido'], PDO::PARAM_STR);

            // Ejecutar la consulta de actualización
            $stmt->execute();

            // Actualizar la contraseña solo si es necesario
            if (isset($data['password']) && !empty($data['password'])) {
                $stmt = $this->pdo->prepare("UPDATE usuario SET password = :password WHERE id = :user_id");
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $newPass = password_hash($data['password'], PASSWORD_BCRYPT);
                $stmt->bindParam(':password', $newPass, PDO::PARAM_STR);

                $stmt->execute();
            }

            // Consulta para actualizar los datos del alumno
            $stmt = $this->pdo->prepare("UPDATE admin SET permisos = :permisos WHERE id = :user_id");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':permisos', $data['permisos'], PDO::PARAM_STR);

            // Ejecutar la consulta de actualización
            $stmt->execute();

            $this->pdo->commit();
            return ['status' => 'ok'];
        } catch (\Throwable $th) {
            $this->pdo->rollback();
            return ['status' => 'error', 'message' => 'Ocurrió un error.'];
        }
    }
}
