<?php
require_once __DIR__ . '/../models/login.php';
require_once __DIR__ . '/../config/JwtManager.php';


class LoginController
{


    public function login($request)
    {
        error_log(print_r($_POST, true));

        $email = $request['email'] ?? null;
        $password = $request['password'] ?? null;


        if (empty($email) || empty($password)) {
            echo json_encode([
                "status" => "error",
                "message" => "El email y la contraseña son obligatorios."
            ]);
            return;
        }

        $user = Login::checkCredentials($email, $password);

        if ($user) {

            $jwt = JwtManager::createToken($user['idUser'], $user['name'],$user['user_type']);

            echo json_encode([
                "status" => "success",
                "message" => "Inicio de sesión exitoso.",
                "token" => $jwt
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Credenciales incorrectas."
            ]);
        }
    }


}