<?php
require_once __DIR__ . '/../config/database.php';

class Wsp
{

    public static function guardarOTP($numero, $otp)
    {
        $db = getConnection();

        $sql = "INSERT INTO tempotp (numero,otp)
                VALUES (:numero, :otp)";

        $stmt = $db->prepare($sql);


        $stmt->execute([
            ':numero' => $numero,
            ':otp' => $otp
        ]);


        return $stmt->rowCount();
    }

}