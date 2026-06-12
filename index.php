<?php
define('ROOT_PATH', __DIR__);

// Carga config y capa compartida
$config = require ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/shared/Response.php';
require_once ROOT_PATH . '/shared/Database.php';
require_once ROOT_PATH . '/middleware/Auth.php';

// Siempre responder JSON
header('Content-Type: application/json; charset=utf-8');

// Validar token en todas las rutas
Auth::validate($config['api']['token']);

// Inicializar conexión a BD (singleton)
Database::getInstance($config['db']);

// Parsear URI — funciona sin importar dónde viva el proyecto
$uri      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = array_values(array_filter(explode('/', trim($uri, '/'))));
$method   = $_SERVER['REQUEST_METHOD'];

// Buscar segmento de versión (v1, v2, etc.)
$versionIndex = null;
foreach ($segments as $i => $seg) {
    if (preg_match('/^v\d+$/', $seg)) {
        $versionIndex = $i;
        break;
    }
}

if ($versionIndex === null) {
    Response::error('Versión de API no válida', 400);
    exit;
}

$resource = isset($segments[$versionIndex + 1]) ? $segments[$versionIndex + 1] : '';
$action   = isset($segments[$versionIndex + 2]) ? $segments[$versionIndex + 2] : '';

// Despachar al router correspondiente
switch ($resource) {

    case 'trabajador':
    case 'directorio':
        require_once ROOT_PATH . '/trabajadores/TrabajadorRouter.php';
        (new TrabajadorRouter($config))->handle($resource, $method);
        break;

    case 'sync':
        require_once ROOT_PATH . '/sync/SyncRouter.php';
        (new SyncRouter($config))->handle($action, $method);
        break;

    default:
        Response::error('Ruta no encontrada', 404);
}