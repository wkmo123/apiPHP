<?php

require_once __DIR__ . '/../controllers/DepartamentoController.php';
require_once __DIR__ . '/../controllers/municipioController.php';

$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($url == '/api/departamentos' && $_SERVER['REQUEST_METHOD'] === 'GET') {

    $controller = new DepartamentoController();
    $controller->index();
} elseif (preg_match('/\/api\/ciudades\/(\d+)/', $url, $matches) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $departamentos_id = $matches[1];
    $controller = new MunicipioController();
    $controller->getByDepartamento($departamentos_id);

}else{
    header('HTTP/1.1 404 Not Found');
    echo json_encode(['status' => 'error', 'message' => 'Endpoint no encontrado']);
}