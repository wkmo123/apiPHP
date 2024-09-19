<?php

require_once __DIR__ . '/../config/database.php';

class Departamento
{
    public static function getAll()
    {
        $db = getConnection();
        $stmt = $db->query("SELECT * FROM departamentos");
        return $stmt->fetchAll();
    }
}
