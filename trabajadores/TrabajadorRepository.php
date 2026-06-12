<?php
class TrabajadorRepository {

    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function findByCorreo($correo) {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM trabajadores WHERE correo = :correo LIMIT 1'
        );
        $stmt->execute([':correo' => $correo]);
        return $stmt->fetch();
    }

    public function findByMatricula($matricula) {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM trabajadores WHERE matricula = :matricula LIMIT 1'
        );
        $stmt->execute([':matricula' => $matricula]);
        return $stmt->fetch();
    }

    public function findAll($pagina, $limite) {
        $offset = ($pagina - 1) * $limite;
        $stmt   = $this->pdo->prepare(
            'SELECT * FROM trabajadores
             WHERE activo = 1
             ORDER BY nombre_completo
             LIMIT :limite OFFSET :offset'
        );
        $stmt->bindValue(':limite',  (int)$limite,  PDO::PARAM_INT);
        $stmt->bindValue(':offset',  (int)$offset,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countActivos() {
        $stmt = $this->pdo->query(
            'SELECT COUNT(*) AS total FROM trabajadores WHERE activo = 1'
        );
        return (int)$stmt->fetch()['total'];
    }

    public function findSince($timestamp) {
        $fecha = date('Y-m-d H:i:s', (int)$timestamp);
        $stmt  = $this->pdo->prepare(
            'SELECT * FROM trabajadores
             WHERE ultima_sync >= :fecha
             ORDER BY ultima_sync'
        );
        $stmt->execute([':fecha' => $fecha]);
        return $stmt->fetchAll();
    }
}