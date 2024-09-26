<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtManager
{
    private static $secretKey = '346aeb13d31387b6d9dae0d0a5e71c0c74d7c2c1b7101e283e5de793c6b4d7d2';

    private static $algorithm = 'HS256';

    public static function createToken($idUser, $email)
    {
        $issuedAdt = time();
        $expiration = $issuedAdt + 3600;

        //Payload del JWT
        $payload = [

            'iat' => $issuedAdt,
            'exp' => $expiration,
            'data' => [
                'idUser' => $idUser,
                'email' => $email
            ]
        ];

        //Generar el token usando encode 
        return JWT::encode($payload, self::$secretKey, self::$algorithm);
    }

    public static function verifyToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key(self::$secretKey, self::$algorithm));
            return (array) $decoded->data;
        } catch (Exception $e) {
            return false;
        }
    }
}