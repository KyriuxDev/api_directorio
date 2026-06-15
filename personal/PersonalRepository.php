<?php
class PersonalRepository {

    private $pdoImss;   // cdi_imss       → cdi_cat_personal
    private $pdoDir;    // cdi_directorio → trabajadores

    public function __construct() {
        $this->pdoImss = Database::getInstance(array(), 'imss')->getConnection();
        $this->pdoDir  = Database::getInstance(array(), 'default')->getConnection();
    }

    // ─── SQL base reutilizable ────────────────────────────────────────────────

    private function sqlBase() {
        return "
            SELECT
                p.Matricula         AS matricula,
                p.Nombres           AS nombres,
                p.ApPaterno         AS ap_paterno,
                p.ApMaterno         AS ap_materno,
                p.ClaveAdscripcion  AS clave_adscripcion,
                p.ClaveCategoria    AS clave_categoria,
                t.correo            AS correo,
                t.extension         AS extension,
                t.telefono          AS telefono,
                t.departamento      AS departamento,
                t.adscripcion       AS adscripcion,
                t.activo            AS activo
            FROM cdi_imss.cdi_cat_personal p
            LEFT JOIN cdi_directorio.trabajadores t
                ON t.matricula = p.Matricula
        ";
    }

    // ─── Métodos públicos ─────────────────────────────────────────────────────

    public function countPersonal() {
        $stmt = $this->pdoImss->query(
            "SELECT COUNT(*) AS total FROM cdi_cat_personal"
        );
        return (int) $stmt->fetch()['total'];
    }

    public function findPage($pagina, $limite) {
        $offset = ($pagina - 1) * $limite;

        $sql  = $this->sqlBase();
        $sql .= " ORDER BY p.Matricula LIMIT :limite OFFSET :offset";

        $stmt = $this->pdoImss->prepare($sql);
        $stmt->bindValue(':limite', (int) $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findSince($timestamp) {
        $fecha = date('Y-m-d H:i:s', (int) $timestamp);

        $sql  = $this->sqlBase();
        $sql .= " WHERE t.ultima_sync >= :fecha ORDER BY t.ultima_sync";

        $stmt = $this->pdoImss->prepare($sql);
        $stmt->execute(array(':fecha' => $fecha));
        return $stmt->fetchAll();
    }

    public function findByMatricula($matricula) {
        $sql  = $this->sqlBase();
        $sql .= " WHERE p.Matricula = :matricula LIMIT 1";

        $stmt = $this->pdoImss->prepare($sql);
        $stmt->execute(array(':matricula' => $matricula));
        $row = $stmt->fetch();
        return $row ? $row : null;
    }
}