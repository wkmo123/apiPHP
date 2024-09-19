<?php

require_once __DIR__ . "/../models/ciudad.php";


class MunicipioController
{
    public function getByDepartamento($departamento_id)
    {
        $ciudades = Ciudad::getByDepartamento($departamento_id);

        if (empty($ciudades)) {
            echo json_encode([

                "status" => "error",
                "message" => "No se encontraron municipios para este dedpartamento"
            ]);
        } else {
            echo json_encode([
                "status" => "success",
                "data" => $ciudades
            ]);
        }
    }
}