<?php
class UsuarioService {

    private $repository;

    public function __construct() {
        $this->repository = new UsuarioRepository();
    }

    public function getUsuarios($pagina, $limite) {
        return array(
            'usuarios' => array_map(array($this, 'format'), $this->repository->findPage($pagina, $limite)),
            'total'    => $this->repository->countHabilitados(),
            'pagina'   => (int) $pagina,
            'limite'   => (int) $limite,
        );
    }

    public function getDesde($timestamp) {
        $rows = $this->repository->findSince($timestamp);
        return array(
            'usuarios' => array_map(array($this, 'format'), $rows),
            'total'    => count($rows),
            'desde'    => (int) $timestamp,
        );
    }

    public function getByMatricula($matricula) {
        $row = $this->repository->findByMatricula($matricula);
        if (!$row) {
            Response::error('Usuario no encontrado', 404);
            exit;
        }
        return $this->format($row);
    }

    private function format($row) {
        // password (sha1) se manda tal cual: el cliente nunca debe re-hashear esto.
        $row['nombres']    = $row['nombres']    ?: '';
        $row['ap_paterno'] = $row['ap_paterno'] ?: '';
        $row['ap_materno'] = $row['ap_materno'] ?: '';
        unset($row['is_approved'], $row['is_locked_out']); // ya filtrado en el WHERE, no hace falta exponerlo
        return $row;
    }
}
