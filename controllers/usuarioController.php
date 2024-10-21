<?php

require_once __DIR__ . "/../models/usuario.php";
require_once __DIR__ . "/../models/wsp.php";
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
        $user_type = $request['user_type'] ?? 'user'; // Por defecto 'user'
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

        // Verificar si el usuario ya existe por email o cédula
        $existingUser = Usuario::findByEmailOrCedula($email, $cedula);
        if ($existingUser) {
            http_response_code(409); // Conflict
            echo json_encode([
                "status" => "error",
                "message" => "El usuario ya está registrado con este email o cédula."
            ]);
            return; // Salir de la función si ya existe el usuario
        }

        try {
            // Intentar insertar el nuevo usuario en la base de datos
            $result = Usuario::save($name, $lastname, $email, $cedula, $password, $telefono, $direccion, $user_type, $id_estado, $confCorreo, $municipio_id);

            // Verificar si la inserción fue exitosa
            if ($result) {
                $cod = rand(100000, 999999);
                $this->sendMessage($telefono, $name, $cod);
                echo json_encode([
                    "status" => "success",
                    "message" => "Usuario registrado correctamente.",
                    "datos" => $result
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al registrar el usuario. Intenta nuevamente."
                ]);
            }
        } catch (PDOException $e) {
            // Verificar si el error es de clave duplicada (por email o cédula)
            if ($e->getCode() == 23000) { // Código SQL para error de integridad (clave duplicada)
                http_response_code(409); // Conflict
                echo json_encode([
                    "status" => "error",
                    "message" => "El usuario ya está registrado con este email o cédula."
                ]);
            } else {
                // Manejar otros errores
                echo json_encode([
                    "status" => "error",
                    "message" => "Error: " . $e->getMessage()
                ]);
            }
        }
    }

    //enviar msg 
    public function sendMessage($number, $name, $cod)
    {



        if ((empty($number) || empty($name)) || empty($cod)) {

            echo json_encode([
                "status" => "error",
                "message" => "Todos los campos son obligatorios"
            ]);

            return;
        }

        $response = $this->apiWsp($number, $name, $cod);
        $result = Wsp::guardarOTP($number, $cod);

        echo $response;

    }

    //apiWSP
    private function apiWsp($numberuser, $name, $cod)
    {
        $number = "57" . $numberuser;
        $post = array(
            "messaging_product" => "whatsapp",
            "to" => $number,
            "type" => "template",
            "template" => array(
                "name" => "codverification", // para recuperar password recuperapass  y para verificacion codverification
                "language" => array(
                    "code" => "es"
                ),
                "components" => array(
                    array(
                        "type" => "body",
                        "parameters" => array(
                            array("type" => "text", "text" => "*" . $name . "*"),
                            array("type" => "text", "text" => "*" . $cod . "*")
                        )
                    )
                )
            )
        );
        $url = "https://graph.facebook.com/v15.0/105386462411555/messages";
        return $this->bodyrequestAPI($post, $url);
    }

    //bodyRequest
    private function bodyrequestAPI($postaux, $url)
    {
        header('Content-Type: application/json');
        $ch = curl_init($url);
        $post = json_encode($postaux);
        $authorization = "Authorization: Bearer EABVzZC4Gfh7YBO9nzZA1aAXH2C7wpP8ATV5ZBZCxaSz8OxyWayZBZCdFOvCDE09yOgPFMyXMBg33cbuREyYZAc2DVPOnZA5ZBh4xpZC0kVhDHZBUFnAXZCxXEZBIOFgAbKRihSUU2xvVxNn5AZADCH2QVDden9k4hFfvNQfJnsO3CXjYPSnZAUHbJSOLvl1BC2toSd9mgmUzYFX0ZBo4k4PneUAp06TDykGASBasxIViJNQZD";
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
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
        error_log("el resultado es:" . print_r($result, true));
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

    public function eliminarUsuario($request)
    {

        $idUsuario = $request['idUser'];
        if (empty($idUsuario)) {
            echo json_encode([
                "status" => "error",
                "message" => "Debe proporcionar el id del usuario."
            ]);
            return;
        }
        $result = Usuario::deleteUser($idUsuario);

        if ($result) {
            echo json_encode([
                "status" => "success",
                "message" => "Usuario eliminado correctamente."
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al eliminar el usuario, intentelo de nuevo"
            ]);
        }
    }



}
