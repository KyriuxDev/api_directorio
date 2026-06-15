<?php
class PersonalService {

    private $repository;

    public function __construct() {
        $this->repository = new PersonalRepository();
    }

    public function getDirectorio($pagina, $limite) {
        return array(
            'personal' => array_map(array($this, 'format'), $this->repository->findPage($pagina, $limite)),
            'total'    => $this->repository->countPersonal(),
            'pagina'   => (int) $pagina,
            'limite'   => (int) $limite,
        );
    }

    public function getDesde($timestamp) {
        $rows = $this->repository->findSince($timestamp);
        return array(
            'personal' => array_map(array($this, 'format'), $rows),
            'total'    => count($rows),
            'desde'    => (int) $timestamp,
        );
    }

    public function getByMatricula($matricula) {
        $row = $this->repository->findByMatricula($matricula);
        if (!$row) {
            Response::error('Trabajador no encontrado', 404);
            exit;
        }
        return $this->format($row);
    }

    private function format($row) {
        $row['nombre_completo'] = trim(
            $row['nombres'] . ' ' . $row['ap_paterno'] . ' ' . $row['ap_materno']
        );
        $row['activo'] = isset($row['activo']) ? (bool) $row['activo'] : true;
        return $row;
    }
}