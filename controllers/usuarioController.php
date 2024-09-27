<?php

require_once __DIR__ . "/../models/usuario.php";
class UsuarioController
{

    public function insertarUsuario($request)
    {
        // Extraer datos del request con valores por defecto
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

        // Verificar si el usuario ya existe
        $existingUser = Usuario::findByEmailOrCedula($email, $cedula);
        if ($existingUser) {
            http_response_code(409); // Conflict
            echo json_encode([
                "status" => "error",
                "message" => "El usuario ya está registrado con este email o cédula."
            ]);
            return; // Salir de la función si ya existe el usuario
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

    public function cambiarPassword($request)
    {
        $telefono = $request['telefono'] ?? '';
        $password = $request['password'] ?? '';

        if (empty($telefono) || empty($password)) {
            echo json_encode([
                "status" => "error",
                "message" => "Todos los campos obligatorios deben ser completados."
            ]);
            return;
        }

        $result = Usuario::cambiarpassword($telefono, $password);
        if ($result) {
            echo json_encode([
                "status" => "success",
                "message" => "Contraseña cambiada correctamente."
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al cambiar la contraseña, intentelo de nuevo"
            ]);
        }
    }

}
