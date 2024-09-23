<?php

require_once __DIR__ . "/../models/usuario.php";
class UsuarioController
{

    public function insertarUsuario($request)
    {
        $name = $request['name'] ?? '';
        $lastname = $request['lastname'] ?? '';
        $email = $request['email'] ?? '';
        $cedula = $request['cedula'] ?? '';
        $password = $request['password'] ?? '';
        $telefono = $request['telefono'] ?? '';
        $direccion = $request['direccion'] ?? '';
        $user_type = $request['user_type'] ?? 'user'; // Si no se envía, se define como 'user' por defecto
        $id_estado = $request['id_estado'] ?? 1; // Estado por defecto activo
        $confCorreo = $request['confCorreo'] ?? 0; // Por defecto sin confirmar
        $municipio_id = $request['municipio_id'] ?? null;

        // Verificar que los campos obligatorios no estén vacíos
        if (empty($name) || empty($lastname) || empty($email) || empty($cedula) || empty($password)) {
            echo json_encode([
                "status" => "error",
                "message" => "Todos los campos obligatorios deben ser completados."
            ]);
            return;
        }

        // Intentar insertar el nuevo usuario en la base de datos
        $result = Usuario::save($name, $lastname, $email, $cedula, $password, $telefono, $direccion, $user_type, $id_estado, $confCorreo, $municipio_id);

        // Verificar si la inserción fue exitosa
        if ($result) {
            echo json_encode([
                "status" => "success",
                "message" => "Usuario registrado correctamente."
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al registrar el usuario. Intenta nuevamente."
            ]);
        }
    }
}
