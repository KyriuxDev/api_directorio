<?php
define('ROOT_PATH', __DIR__);

$config = require ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/shared/Response.php';
require_once ROOT_PATH . '/shared/Database.php';
require_once ROOT_PATH . '/middleware/Auth.php';

header('Content-Type: application/json; charset=utf-8');

Auth::validate($config['api']['token']);

// BD principal: cdi_directorio
Database::getInstance($config['db'], 'default');

// BD secundaria: cdi_imss
Database::getInstance($config['db_imss'], 'imss');

$uri      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = array_values(array_filter(explode('/', trim($uri, '/'))));
$method   = $_SERVER['REQUEST_METHOD'];

$versionIndex = null;
foreach ($segments as $i => $seg) {
    if (preg_match('/^v\d+$/', $seg)) {
        $versionIndex = $i;
        break;
    }
}

if ($versionIndex === null) {
    Response::error('Version de API no valida', 400);
    exit;
}

$resource = isset($segments[$versionIndex + 1]) ? $segments[$versionIndex + 1] : '';
$action   = isset($segments[$versionIndex + 2]) ? $segments[$versionIndex + 2] : '';

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

    case 'personal':
        require_once ROOT_PATH . '/personal/PersonalRouter.php';
        (new PersonalRouter($config))->handle($action, $method);
        break;

    case 'usuarios':
        require_once ROOT_PATH . '/usuarios/UsuarioRouter.php';
        (new UsuarioRouter($config))->handle($action, $method);
        break;

    default:
        Response::error('Ruta no encontrada', 404);
}