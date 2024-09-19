<?php
require_once __DIR__ . '/../config/database.php';

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
}