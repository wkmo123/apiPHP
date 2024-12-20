<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/redis.php';

class Usuario
{
    public static function save($nombre, $apellido, $correo, $cedula, $password, $telefono, $direccion, $user_type, $id_estado, $confCorreo, $municipio_id)
    {
        $db = getConnection();

        $sql = "INSERT INTO pre_registro (name, lastname, email, cedula, password, telefono, direccion, user_type, id_estado, confCorreo, municipio_id)
                VALUES (:name, :lastname, :email, :cedula, :password, :telefono, :direccion, :user_type, :id_estado, :confCorreo, :municipio_id)";

        $stmt = $db->prepare($sql);

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $stmt->execute([
            ':name' => $nombre,
            ':lastname' => $apellido,
            ':email' => $correo,
            ':cedula' => $cedula,
            ':password' => $passwordHash, // Encriptada
            ':telefono' => $telefono,
            ':direccion' => $direccion,
            ':user_type' => $user_type,
            ':id_estado' => $id_estado,
            ':confCorreo' => $confCorreo,
            ':municipio_id' => $municipio_id
        ]);


        return $stmt->rowCount();

    }

    public static function saveUser($nombre, $apellido, $correo, $cedula, $password, $telefono, $direccion, $user_type, $id_estado, $confCorreo, $municipio_id)
    {
        $db = getConnection();

        $sql = "INSERT INTO users (name, lastname, email, cedula, password, telefono, direccion, user_type, id_estado, confCorreo, municipio_id)
            VALUES (:name, :lastname, :email, :cedula, :password, :telefono, :direccion, :user_type, :id_estado, :confCorreo, :municipio_id)";

        $stmt = $db->prepare($sql);

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt->execute([
                ':name' => $nombre,
                ':lastname' => $apellido,
                ':email' => $correo,
                ':cedula' => $cedula,
                ':password' => $passwordHash, // Encriptada
                ':telefono' => $telefono,
                ':direccion' => $direccion,
                ':user_type' => $user_type,
                ':id_estado' => $id_estado,
                ':confCorreo' => $confCorreo,
                ':municipio_id' => $municipio_id
            ]);

            return $stmt->rowCount(); // Retorna el número de filas afectadas
        } catch (PDOException $e) {
            // Manejo del error: puedes registrar el error o lanzarlo según sea necesario
            error_log("Error al guardar el usuario: " . $e->getMessage()); // Registra el error
            return [
                'status' => 'error',
                'message' => 'No se pudo guardar el usuario: ' . $e->getMessage() // Mensaje de error
            ];
        }
    }


    public static function traerIdByNumero($numero)
    {
        $db = getConnection();
        $stmt = $db->prepare("SELECT idUser FROM pre_registro WHERE telefono = ?");
        $stmt->execute([$numero]);
        return $stmt->fetchColumn();  // Esto devuelve solo el valor de la columna idUser
    }

    public static function traerIdByOTP($otp)
    {
        $db = getConnection();
        $stmt = $db->prepare("SELECT id_temp FROM temppass WHERE otp = ?");
        $stmt->execute([$otp]);
        return $stmt->fetchColumn();
    }



    public static function getAllById($id)
    {
        $db = getConnection();
        $stmt = $db->prepare("SELECT * FROM pre_registro WHERE idUser = ?");
        $stmt->execute([$id]);
        $results = $stmt->fetchAll();  // Esto devuelve un array de resultados
        return $results;
    }

    public static function deleteById($id)
    {
        $db = getConnection();
        $stmt = $db->prepare("DELETE FROM pre_registro WHERE idUser = ?");
        if ($stmt->execute([$id])) {
            return true;  // Borrado exitoso
        } else {
            return false;  // Error al borrar
        }
    }

    public static function deleteOTPtemporal($id)
    {
        $db = getConnection();
        $stmt = $db->prepare("DELETE FROM temppass WHERE id_temp = ?");
        if ($stmt->execute([$id])) {
            return true;  // Borrado exitoso
        } else {
            return false;  // Error al borrar
        }
    }

    //encotrar emaila o cedula
    public static function findByEmailOrCedula($email, $cedula)
    {
        $db = getConnection();

        $query = $db->prepare("SELECT * FROM users WHERE email = :email OR cedula = :cedula LIMIT 1");
        $query->execute([
            ':email' => $email,
            ':cedula' => $cedula
        ]);

        return $query->fetch(PDO::FETCH_ASSOC); // Retorna el usuario si existe o false si no
    }
    //cambiar password
    public static function cambiarpassword($telefono, $newPassword)
    {
        $db = getConnection();
        $sql = "UPDATE users SET password = :newPassword WHERE telefono = :telefono";
        $stmt = $db->prepare($sql);

        $passWord = password_hash($newPassword, PASSWORD_BCRYPT);

        try {
            $stmt->execute([
                ":newPassword" => $passWord,
                ":telefono" => $telefono
            ]);

            return $stmt->rowCount();
        } catch (PDOException $e) {

            // Manejo del error: registrar el error o lanzarlo según sea necesario
            error_log("Error al cambiar la contraseña: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'No se pudo cambiar la contraseña: ' . $e->getMessage()
            ];
        }

    }

    public static function deleteUser($idUser)
    {
        $db = getConnection();
        $sql = "DELETE FROM users WHERE idUser = :idUser";
        $stmt = $db->prepare($sql);
        try {
            $stmt->execute([
                ":idUser" => $idUser
            ]);

            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Error al elmiinar el usuario: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => "Error al elmiinar el usuario: " . $e->getMessage()
            ];
        }
    }


    public static function traerDatosbyEmail($email)
    {
        $db = getConnection();
        $stmt = $db->prepare("SELECT telefono, name FROM pre_registro WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch();

        // Verificar si se encontró un resultado y devolver ambos valores
        return $result ? ['telefono' => $result['telefono'], 'name' => $result['name']] : null;
    }


    public static function traerOTPbyTelefono($telefono)
    {
        $db = getConnection();
        $stmt = $db->prepare("SELECT otp FROM tempotp WHERE numero = ?");
        $stmt->execute([$telefono]);
        $result = $stmt->fetch();
        return $result ? $result['otp'] : null;
    }

    /*
    //guardar en la base de datos temporal redis
        public static function saveRedis($nombre, $apellido, $correo, $cedula, $password, $telefono, $direccion, $user_type, $id_estado, $confCorreo, $municipio_id)
        {
            $redis = getRedisConnection();

            $userId = uniqid('user:', true); // ID único para el usuario

            // Crear un hash con los datos del usuario
            $userData = [
                'name' => $nombre,
                'lastname' => $apellido,
                'email' => $correo,
                'cedula' => $cedula,
                'password' => password_hash($password, PASSWORD_BCRYPT), // Encriptar la contraseña
                'telefono' => $telefono,
                'direccion' => $direccion,
                'user_type' => $user_type,
                'id_estado' => $id_estado,
                'confCorreo' => $confCorreo,
                'municipio_id' => $municipio_id
            ];

            $expiration = 3600; // Expiración de 1 hora

            // Almacenar los datos del usuario como un hash
            $redis->hmset($userId, $userData);

            $redis->expire($userId, $expiration);

            // **Almacenar la relación del teléfono con el ID del usuario**
            $redis->set('user:' . $telefono, $userId);

            // Establecer el tiempo de expiración para la relación de teléfono
            $redis->expire('user:' . $telefono, $expiration);

            return $userId;
        }

         public static function getUser($userId)
        {
            $redis = getRedisConnection();
            return $redis->hgetall($userId);
        }
    */


}