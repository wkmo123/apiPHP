<?php

require_once __DIR__ . '/../controllers/DepartamentoController.php';
require_once __DIR__ . '/../controllers/municipioController.php';
require_once __DIR__ . '/../controllers/usuarioController.php';
require_once __DIR__ . '/../controllers/wspController.php';
require_once __DIR__ . '/../controllers/documentoController.php';


$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Mapeo de rutas: URL => [Método, Controlador, Función]
$routes = [
    '/api/departamentos' => ['GET', 'DepartamentoController', 'index'],
    '/api/usuarios/registro' => ['POST', 'UsuarioController', 'insertarUsuario'],
    '/api/usuarios/enviar-otp' => ['POST', 'WspController', 'sendMessage'],
    '/api/usuarios/verificar-otp' => ['POST', 'WspController', 'validarOTP'],
    '/api/documentos/subir' => ['POST', 'DocumentoController', 'insertarDocumentos'],

];

if (preg_match('/\/api\/ciudades\/(\d+)/', $url, $matches) && $method === 'GET') {
    $departamento_id = $matches[1];
    $controller = new MunicipioController();
    $controller->getByDepartamento($departamento_id);
} else {
    if (array_key_exists($url, $routes) && $routes[$url][0] === $method) {
        // Obtener detalles de la ruta
        $controllerName = $routes[$url][1];
        $function = $routes[$url][2];

        $controller = new $controllerName();

        $data = $method === 'POST' ? json_decode(file_get_contents('php://input'), true) : [];

        $controller->$function($data);
    } else {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['status' => 'error', 'message' => 'Endpoint no encontrado']);
    }
}
