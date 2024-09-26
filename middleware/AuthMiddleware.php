<?php

require_once __DIR__ . "/../config/jwtManager.php";

class AuthMiddleware
{
    public static function handleProtectedRequest(callable $callback)
    {
        // Obtener los headers
        $headers = apache_request_headers();

        // Validar el token
        $user = self::validateAuth($headers);

        // Si el token es válido, ejecuta el callback (la lógica de la API)
        if ($user) {
            call_user_func($callback, $user);
        } else {
            // En caso de token inválido, retornar un error (esto lo gestiona validateAuth)
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'No autorizado.'
            ]);
        }
    }
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