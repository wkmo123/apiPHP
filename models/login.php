<?php

class Login
{
    public static function checkCredentials($email, $password)
    {
        $db = getConnection(); // Obtener la conexión a la base de datos

        $sql = "SELECT idUser, password FROM users WHERE email = :email LIMIT 1";
        $stmt = $db->prepare($sql);

        try {
            $stmt->execute([
                ':email' => $email
            ]);

            // Obtener los datos del usuario
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificar si el usuario existe y la contraseña es correcta
            if ($user && password_verify($password, $user['password'])) {
                return $user; // Devolver los datos del usuario si las credenciales son correctas
            }

            return false; // Retorna false si las credenciales no son válidas
        } catch (PDOException $e) {
            error_log("Error al verificar las credenciales: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'No se pudo verificar las credenciales: ' . $e->getMessage()
            ];
        }
    }
}