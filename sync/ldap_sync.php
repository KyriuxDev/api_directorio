<?php
// Uso:
// 0 */6 * * * /opt/lampp/bin/php /opt/lampp/htdocs/api/sync/ldap_sync.php >> /var/log/ldap_sync.log 2>&1

define('ROOT_PATH', dirname(__DIR__));

$config = require ROOT_PATH . '/config/config.php';

require_once ROOT_PATH . '/shared/Response.php';
require_once ROOT_PATH . '/shared/Database.php';
require_once ROOT_PATH . '/sync/SyncRepository.php';
require_once ROOT_PATH . '/sync/SyncService.php';

Database::getInstance($config['db']);

echo '[' . date('Y-m-d H:i:s') . '] Iniciando sync LDAP...' . PHP_EOL;

try {
    $service   = new SyncService($config);
    $resultado = $service->runSync();

    echo sprintf(
        '[%s] OK — Total: %d | Nuevos: %d | Actualizados: %d | Errores: %d | Duración: %s seg' . PHP_EOL,
        date('Y-m-d H:i:s'),
        $resultado['total'],
        $resultado['nuevos'],
        $resultado['actualizados'],
        $resultado['errores'],
        $resultado['duracion_seg']
    );

} catch (Exception $e) {
    echo '[' . date('Y-m-d H:i:s') . '] ERROR: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}