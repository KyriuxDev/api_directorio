<?php
require_once ROOT_PATH . '/trabajadores/TrabajadorSchema.php';
require_once ROOT_PATH . '/trabajadores/TrabajadorRepository.php';
require_once ROOT_PATH . '/trabajadores/TrabajadorService.php';

class TrabajadorRouter {

    private $service;

    public function __construct($config) {
        $this->service = new TrabajadorService();
    }

    public function handle($resource, $method) {
        if ($method !== 'GET') {
            Response::error('Método no permitido', 405);
            return;
        }

        switch ($resource) {
            case 'trabajador':
                $this->handleTrabajador();
                break;
            case 'directorio':
                $this->handleDirectorio();
                break;
        }
    }

    private function handleTrabajador() {
        TrabajadorSchema::validateTrabajadorQuery($_GET);

        if (!empty($_GET['correo'])) {
            $trabajador = $this->service->getByCorreo($_GET['correo']);
        } else {
            $trabajador = $this->service->getByMatricula($_GET['matricula']);
        }

        Response::success(['trabajador' => $trabajador]);
    }

    private function handleDirectorio() {
        if (!empty($_GET['desde'])) {
            $data = $this->service->getDirectorioDesde($_GET['desde']);
        } else {
            $params = TrabajadorSchema::validateDirectorioQuery($_GET);
            $data   = $this->service->getDirectorio($params['pagina'], $params['limite']);
        }

        Response::success($data);
    }
}