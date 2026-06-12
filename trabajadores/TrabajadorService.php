<?php
class TrabajadorService {

    private $repository;

    public function __construct() {
        $this->repository = new TrabajadorRepository();
    }

    public function getByCorreo($correo) {
        $row = $this->repository->findByCorreo($correo);
        if (!$row) {
            Response::error('Trabajador no encontrado', 404);
            exit;
        }
        return $this->format($row);
    }

    public function getByMatricula($matricula) {
        $row = $this->repository->findByMatricula($matricula);
        if (!$row) {
            Response::error('Trabajador no encontrado', 404);
            exit;
        }
        return $this->format($row);
    }

    public function getDirectorio($pagina, $limite) {
        return [
            'trabajadores' => array_map([$this, 'format'], $this->repository->findAll($pagina, $limite)),
            'total'        => $this->repository->countActivos(),
            'pagina'       => (int)$pagina,
            'limite'       => (int)$limite,
        ];
    }

    public function getDirectorioDesde($timestamp) {
        $rows = $this->repository->findSince($timestamp);
        return [
            'trabajadores' => array_map([$this, 'format'], $rows),
            'total'        => count($rows),
            'desde'        => (int)$timestamp,
        ];
    }

    // Normaliza tipos antes de enviar al cliente
    private function format($row) {
        $row['activo'] = (bool)$row['activo'];
        return $row;
    }
}