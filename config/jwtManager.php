<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtManager
{
    private static $secretKey = '346aeb13d31387b6d9dae0d0a5e71c0c74d7c2c1b7101e283e5de793c6b4d7d2';

    private static $algorithm = 'HS256';

    public static function createToken($idUser, $name, $role)
    {
        $issuedAdt = time();
        $expiration = $issuedAdt + 3600;

        //Payload del JWT
        $payload = [
            'sub' => $idUser,   // Identificador del usuario
            'name' => $name,    // Nombre del usuario
            'role' => $role,    // Rol del usuario
            'iat' => $issuedAdt, // Fecha de emisión
            'exp' => $expiration // Fecha de expiración
        ];

        //Generar el token usando encode 
        return JWT::encode($payload, self::$secretKey, self::$algorithm);
    }

    public static function verifyToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key(self::$secretKey, self::$algorithm));
            return (array) $decoded;
        } catch (Exception $e) {
            return false;
        }
    }
}