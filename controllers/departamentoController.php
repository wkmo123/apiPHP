<?php

require_once __DIR__ . '/../models/Departamento.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';


class DepartamentoController
{
    public function index()
    {
        $departamentos = Departamento::getAll();
        echo json_encode([
            'status' => 'success',
            'data' => $departamentos
        ]);
    }
}
