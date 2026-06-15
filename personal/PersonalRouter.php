<?php
require_once ROOT_PATH . '/personal/PersonalRepository.php';
require_once ROOT_PATH . '/personal/PersonalService.php';

class PersonalRouter {

    private $service;

    public function __construct($config) {
        $this->service = new PersonalService();
    }

    public function handle($action, $method) {

        if ($method !== 'GET') {
            Response::error('Método no permitido', 405);
            return;
        }

        // GET /api/v1/personal?matricula=XXXXXXXX  — búsqueda puntual
        if (!empty($_GET['matricula'])) {
            $personal = $this->service->getByMatricula(trim($_GET['matricula']));
            Response::success(array('personal' => $personal));
            return;
        }

        // GET /api/v1/personal?desde=1718000000  — sync incremental
        if (!empty($_GET['desde'])) {
            $data = $this->service->getDesde((int) $_GET['desde']);
            Response::success($data);
            return;
        }

        // GET /api/v1/personal?pagina=1&limite=500  — sync paginado
        $pagina = max(1, (int) (isset($_GET['pagina']) ? $_GET['pagina'] : 1));
        $limite = min(1000, max(1, (int) (isset($_GET['limite']) ? $_GET['limite'] : 500)));
        $data   = $this->service->getDirectorio($pagina, $limite);
        Response::success($data);
    }
}