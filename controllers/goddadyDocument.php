<?php

require_once __DIR__ . "/../models/usuario.php";

class UsuarioController
{
    public function insertarUsuario($request)
    {
        try {
            // Obtener los valores del request
            $name = $request['name'] ?? '';
            $lastname = $request['lastname'] ?? '';
            $email = $request['email'] ?? '';
            $cedula = $request['cedula'] ?? '';
            $password = $request['password'] ?? '';
            $telefono = $request['telefono'] ?? '';
            $direccion = $request['direccion'] ?? '';
            $user_type = $request['user_type'] ?? 'user'; // Default: 'user'
            $id_estado = $request['id_estado'] ?? 1; // Default: activo
            $confCorreo = $request['confCorreo'] ?? 0; // Default: no confirmado
            $municipio_id = $request['municipio_id'] ?? null;

            // Validaci贸n de campos obligatorios
            if (empty($name) || empty($lastname) || empty($email) || empty($cedula) || empty($password)) {
                http_response_code(400); // Bad Request
                return [
                    "status" => "error",
                    "message" => "Todos los campos obligatorios deben ser completados."
                ];
            }

            // Verificar si el usuario ya existe (por email o cedula)
            $existingUser = Usuario::findByEmailOrCedula($email, $cedula);
            if ($existingUser) {
                http_response_code(409); // Conflict
                return [
                    "status" => "error",
                    "message" => "El usuario ya est谩 registrado con este email o c茅dula."
                ];
            }

            // Intentar insertar el nuevo usuario en la base de datos
            $result = Usuario::save($name, $lastname, $email, $cedula, $password, $telefono, $direccion, $user_type, $id_estado, $confCorreo, $municipio_id);

            // Verificar si la inserci贸n fue exitosa
            if ($result) {
                http_response_code(201); // Created
                return [
                    "status" => "success",
                    "message" => "Usuario registrado correctamente."
                ];
            } else {
                throw new Exception("Error al registrar el usuario.");
            }
        } catch (Exception $e) {
            // Manejo de errores generales
            http_response_code(500); // Internal Server Error
            return [
                "status" => "error",
                "message" => "Error: " . $e->getMessage()
            ];
        }
    }
    
    
   
    
    
  public function cambiarPassword($request)
{
    $telefono = $request['telefono'];
    $password = $request['password'];

    // Validar que los campos obligatorios estén completos
    if (empty($telefono) || empty($password)) {
        return ([
            "status" => "error",
            "message" => "Todos los campos obligatorios deben ser completados."
        ]);
        return;
    }

    $result = Usuario::cambiarpassword($telefono, $password);
    error_log("el resultado es: " . print_r($result, true));

    // Verificar el resultado para determinar si fue exitoso
 
        return([
            "status" => "success",
            "message" => "password cambiada correctamente."
        ]);
    
}

    public function eliminarUsuario($request)
    {

        $idUsuario = $request['idUser'];
        
        if (empty($idUsuario)) {
            return([
                "status" => "error",
                "message" => "Debe proporcionar el id del usuario."
            ]);
            return;
        }
        $result = Usuario::deleteUser($idUsuario);

        if ($result) {
            return([
                "status" => "success",
                "message" => "Usuario eliminado correctamente."
            ]);
        } else {
            return([
                "status" => "error",
                "message" => "Error al eliminar el usuario, intentelo de nuevo"
            ]);
        }
    }
}
