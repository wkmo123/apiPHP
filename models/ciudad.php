<?php

require_once __DIR__ . '/../config/database.php';

class Ciudad
{
    public static function getByDepartamento($departamento_id)
    {
        $db = getConnection();
        $stmt = $db->prepare("SELECT * FROM municipios WHERE departamento_id = ?");
        $stmt->execute([$departamento_id]);
        return $stmt->fetchAll();
    }
}
