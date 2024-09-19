<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/redis.php';

class Usuario
{
    public static function save($nombre, $apellido, $correo, $cedula, $password, $telefono, $direccion, $user_type, $id_estado, $confCorreo, $municipio_id)
    {
        $db = getConnection();

        $sql = "INSERT INTO users (name, lastname, email, cedula, password, telefono, direccion, user_type, id_estado, confCorreo, municipio_id)
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

    public static function saveRedis($nombre, $apellido, $correo, $cedula, $password, $telefono, $direccion, $user_type, $id_estado, $confCorreo, $municipio_id) // Por defecto 1 hora
    {
        $redis = getRedisConnection();

        $userId = uniqid('user:', true); //ID unico para el usuario

        //aca se crea un hash con los datos del usuario
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

        $expiration = 3600; // equivale a una hora
        // Acá almacenamos el hash en redis
        $redis->hmset($userId, $userData);

        $redis->expire($userId, $expiration);

        return $userId;

    }

    public static function getUser($userId)
    {
        $redis = getRedisConnection();
        return $redis->hgetall($userId);
    }
}