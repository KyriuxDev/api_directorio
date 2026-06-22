<?php
require_once ROOT_PATH . '/usuarios/UsuarioRepository.php';
require_once ROOT_PATH . '/usuarios/UsuarioService.php';

class UsuarioRouter {

    private $service;

    public function __construct($config) {
        $this->service = new UsuarioService();
    }

    public function handle($action, $method) {

        if ($method !== 'GET') {
            Response::error('Método no permitido', 405);
            return;
        }

        // GET /api/v1/usuarios?matricula=XXXXXXXX  — búsqueda puntual
        if (!empty($_GET['matricula'])) {
            $usuario = $this->service->getByMatricula(trim($_GET['matricula']));
            Response::success(array('usuario' => $usuario));
            return;
        }

        // GET /api/v1/usuarios?desde=1718000000  — sync incremental
        if (!empty($_GET['desde'])) {
            $data = $this->service->getDesde((int) $_GET['desde']);
            Response::success($data);
            return;
        }

        // GET /api/v1/usuarios?pagina=1&limite=500  — sync paginado completo
        $pagina = max(1, (int) (isset($_GET['pagina']) ? $_GET['pagina'] : 1));
        $limite = min(1000, max(1, (int) (isset($_GET['limite']) ? $_GET['limite'] : 500)));
        $data   = $this->service->getUsuarios($pagina, $limite);
        Response::success($data);
    }
}
