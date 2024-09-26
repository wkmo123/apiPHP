<?php

require_once __DIR__ . "/../config/jwtManager.php";

class AuthMiddleware
{

    public static function validateAuth($requestHeaders)
    {
        if (!isset($requestHeaders["Authorization"])) {
            echo json_encode([
                "status" => "error",
                "message" => "Token no proporcinado"
            ]);
            http_response_code(404);
            exit;
        }

        // Obtener el token del encabezado Authorization (Bearer <token>)
        $authHeader = $requestHeaders["Authorization"];
        list($bearer, $token) = explode(" ", $authHeader);

        if ($bearer != 'Bearer' || empty($token)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Formato del token incorrecto'
            ]);
            http_response_code(401);
            exit;
        }

        //Validar el token
        $payload = JwtManager::verifyToken($token);

        if (!$payload) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Token invalido o ha expirado'
            ]);
            http_response_code(401);
            exit;
        }

        return $payload;
    }
}