<?php
require_once ROOT_PATH . '/sync/SyncRepository.php';
require_once ROOT_PATH . '/sync/SyncService.php';

class SyncRouter {

    private $service;

    public function __construct($config) {
        $this->service = new SyncService($config);
    }

    public function handle($action, $method) {

        // GET /api/v1/sync/status
        if ($action === 'status' && $method === 'GET') {
            $status = $this->service->getStatus();
            Response::success(['sync' => $status]);
            return;
        }

        // POST /api/v1/sync  — dispara sync manual
        if ($action === '' && $method === 'POST') {
            $resultado = $this->service->runSync();
            Response::success(['sync' => $resultado]);
            return;
        }

        Response::error('Ruta no encontrada', 404);
    }
}