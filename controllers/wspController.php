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

    public function validarOTP($request)
    {
        $numero = $request["numero"] ?? "";
        $otp = $request["otp"] ?? "";


        if ((empty($numero) || empty($otp))) {
            echo json_encode([
                "status" => "error",

                "message" => "El numero de telefono y el OTP son obligatorios"
            ]);
            return;
        }

        $resultado = Wsp::validarOTP($numero, $otp);

        if ($resultado > 0) {

            $redis = new Predis\Client();
            $userId = $redis->get("user:$numero");

            error_log("el id del usuario en redis es de: " . $userId);

            if ($userId) {
                $userData = $redis->hgetall($userId);

                if ($userData) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "OTP validado correctamente",
                        "user" => $userData
                    ]);
                    $name = $userData['name'] ?? '';
                    $lastname = $userData['lastname'] ?? '';
                    $email = $userData['email'] ?? '';
                    $cedula = $userData['cedula'] ?? '';
                    $password = $userData['password'] ?? '';
                    $telefono = $userData['telefono'] ?? '';
                    $direccion = $userData['direccion'] ?? '';
                    $user_type = $userData['user_type'] ?? 'user';
                    $id_estado = $userData['id_estado'] ?? 1;
                    $confCorreo = $userData['confCorreo'] ?? 0;
                    $municipio_id = $userData['municipio_id'] ?? null;

                    $result = Usuario::save(
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
                    if ($result) {
                        error_log("Usuario registrado en Redis correctamente con ID: " . $result);
                    } else {
                        error_log("Error al registrar el usuario en Redis.");
                    }
                } else {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Datos del usuario no encontrados en Redis."
                    ]);
                }
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Usuario no encontrado en Redis."
                ]);
            }

        } else {
            echo json_encode([
                "status" => "error",
                "message" => "OTP invalido o numero incorrecto"
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
                "name" => "codverification",
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
        $authorization = "Authorization: Bearer EABVzZC4Gfh7YBO8wJDDHTZBSYUXqlq8d9bGWZAfeT5KdVbOb3syn3qWZCGhmLiaDy03ebNuPsZCuxMniswvZCxbuh2oMlOVVweZAeViuxZCgA4x2WxuIZCEksil8DzZBulKVoGAWPKQZC8IXSMS3ZC7oDMicnOMAZAYVs9qT6XIw7gAuyHtXoxbd6j2OhyEDxsE8rtejZBRfSVud5L15wYPOAsGvvMV6T6HBxLeQHEGmAZD";
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