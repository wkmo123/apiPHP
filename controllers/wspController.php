<?php
require_once __DIR__ . "/../models/wsp.php";
require_once __DIR__ . "/../models/usuario.php";


class WspController
{

    public function sendMessage($request)
    {

        $number = $request["number"] ?? "";
        $name = $request["name"] ?? "";
        $cod = $request["cod"] ?? "";


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

    public function recuperarPass($request)
    {

        $number = $request["number"] ?? "";
        $name = $request["name"] ?? "";
        //$cod = $request["cod"] ?? "";


        if ((empty($number) || empty($name))) {

            echo json_encode([
                "status" => "error",
                "message" => "Todos los campos son obligatorios"
            ]);

            return;
        }
        $cod = rand(100000, 999999);
        $response = $this->recoverPass($number, $name, $cod);
        $result = Wsp::guardarOTPpass($number, $cod);

        echo $response;

    }

    public function validarOTP($request)
    {
        $numero = $request["numero"] ?? "";
        $otp = $request["otp"] ?? "";

        if (empty($numero) || empty($otp)) {
            echo json_encode([
                "status" => "error",
                "message" => "El número de teléfono y el OTP son obligatorios"
            ]);
            return;
        }

        // Valida el OTP con MySQL
        $resultado = Wsp::validarOTP($numero, $otp);

        if ($resultado > 0) {

            // Obtener el ID de usuario con el número de teléfono desde MySQL
            $userId = Usuario::traerIdByNumero($numero);

            if ($userId) {
                // Obtener los datos del usuario por ID desde MySQL
                $userData = Usuario::getAllById($userId);

                if ($userData) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "OTP validado correctamente",
                        "user" => $userData
                    ]);
                    $user = $userData[0];
                    // Asignar valores de usuario
                    $name = $user['name'] ?? '';
                    $lastname = $user['lastName'] ?? '';
                    $email = $user['email'] ?? '';
                    $cedula = $user['cedula'] ?? '';
                    $password = $user['password'] ?? '';
                    $telefono = $user['telefono'] ?? '';
                    $direccion = $user['direccion'] ?? '';
                    $user_type = $user['user_type'] ?? 'user';
                    $id_estado = $user['id_estado'] ?? 1;
                    $confCorreo = $user['confCorreo'] ?? 1;
                    $municipio_id = $user['municipio_id'] ?? null;

                    // Guardar o actualizar los datos del usuario en MySQL
                    $result = Usuario::saveUser(
                        $name,
                        $lastname,
                        $email,
                        $cedula,
                        $password,
                        $telefono,
                        $direccion,
                        $user_type,
                        $id_estado,
                        $confCorreo,
                        $municipio_id
                    );

                    Usuario::deleteById($userId);
                    if ($result) {
                        error_log("Usuario registrado correctamente con ID: " . $result);

                    } else {
                        error_log("Error al registrar el usuario en MySQL.");
                    }
                } else {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Datos del usuario no encontrados en la base de datos."
                    ]);
                }
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Usuario no encontrado en la base de datos."
                ]);
            }

        } else {
            echo json_encode([
                "status" => "error",
                "message" => "OTP inválido o número incorrecto"
            ]);
        }
    }


    public function validarOTPPass($request)
    {
        $numero = $request["numero"] ?? "";
        $otp = $request["otp"] ?? "";

        if (empty($numero) || empty($otp)) {
            echo json_encode([
                "status" => "error",
                "message" => "El número de teléfono y el OTP son obligatorios"
            ]);
            return;
        }

        $id = Usuario::traerIdByOTP($otp);
      //  error_log
        $resultado = Wsp::validarOTPPass($numero, $otp);
        Usuario::deleteOTPtemporal($id);

        if ($resultado > 0) {
            echo json_encode([
                "status" => "success",
                "message" => "OTP validado correctamente, ahora cambie la contrase;a"
            ]);


        }
    }

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

    private function recoverPass($numberuser, $name, $cod)
    {
        $number = "57" . $numberuser;
        $post = array(
            "messaging_product" => "whatsapp",
            "to" => $number,
            "type" => "template",
            "template" => array(
                "name" => "passwordrec", // para recuperar password recuperapass  y para verificacion codverification
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

    private function bodyrequestAPI($postaux, $url)
    {
        header('Content-Type: application/json');
        $ch = curl_init($url);
        $post = json_encode($postaux);
        $authorization = "Authorization: Bearer EABVzZC4Gfh7YBOzQ2Mm6V6UzKzAVixZAQzL6cZAqj9Qo9bSZA6NooVQZA9vrDi1ipLmsqg9tXBfvJ7GdBvQCZCiaUkx9t9VHDGPYVxGif5eo2to346ZCq7RVddhxVZAjmkqlSlCqK9t4yZAk9ZBSekNHit1tjSsz7M7LqACPzqe6ZBo2ZA35k9HkN2o3sYBW7u4Q6FOZB32Ovxf11nwZArI0R3uyQMIDcfAVP9EV7ZA4ZBYZD";
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}