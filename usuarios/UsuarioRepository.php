<?php
class UsuarioRepository {

    private $pdoImss;   // cdi_imss → cdi_usuarios, cdi_cat_personal

    public function __construct() {
        $this->pdoImss = Database::getInstance(array(), 'imss')->getConnection();
    }

    // ─── SQL base reutilizable ────────────────────────────────────────────────

    private function sqlBase() {
        return "
            SELECT
                u.Matricula  AS matricula,
                u.Password   AS password,
                u.Email      AS email,
                u.Rol        AS rol,
                u.IsApproved AS is_approved,
                u.IsLockedOut AS is_locked_out,
                p.Nombres    AS nombres,
                p.ApPaterno  AS ap_paterno,
                p.ApMaterno  AS ap_materno
            FROM cdi_imss.cdi_usuarios u
            LEFT JOIN cdi_imss.cdi_cat_personal p
                ON p.Matricula = u.Matricula
        ";
    }

    // Solo cuentas habilitadas para login: aprobadas y no bloqueadas
    private function condicionHabilitado() {
        return " u.IsApproved = 1 AND u.IsLockedOut = 0 ";
    }

    // ─── Métodos públicos ─────────────────────────────────────────────────────

    public function countHabilitados() {
        $sql  = "SELECT COUNT(*) AS total FROM cdi_imss.cdi_usuarios u WHERE " . $this->condicionHabilitado();
        $stmt = $this->pdoImss->query($sql);
        return (int) $stmt->fetch()['total'];
    }

    public function findPage($pagina, $limite) {
        $offset = ($pagina - 1) * $limite;

        $sql  = $this->sqlBase();
        $sql .= " WHERE " . $this->condicionHabilitado();
        $sql .= " ORDER BY u.Matricula LIMIT :limite OFFSET :offset";

        $stmt = $this->pdoImss->prepare($sql);
        $stmt->bindValue(':limite', (int) $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Sync incremental: requiere columna LastPasswordChangedDate u otra de auditoría.
    // Usamos LastPasswordChangedDate como proxy de "cambios recientes"; si el usuario
    // se crea/aprueba sin cambiar password, también cae CreateDate.
    public function findSince($timestamp) {
        $fecha = date('Y-m-d H:i:s', (int) $timestamp);

        $sql  = $this->sqlBase();
        $sql .= " WHERE " . $this->condicionHabilitado();
        $sql .= " AND (u.LastPasswordChangedDate >= :fecha OR u.CreateDate >= :fecha2)";
        $sql .= " ORDER BY u.LastPasswordChangedDate";

        $stmt = $this->pdoImss->prepare($sql);
        $stmt->execute(array(':fecha' => $fecha, ':fecha2' => $fecha));
        return $stmt->fetchAll();
    }

    public function findByMatricula($matricula) {
        $sql  = $this->sqlBase();
        $sql .= " WHERE u.Matricula = :matricula AND " . $this->condicionHabilitado();
        $sql .= " LIMIT 1";

        $stmt = $this->pdoImss->prepare($sql);
        $stmt->execute(array(':matricula' => $matricula));
        $row = $stmt->fetch();
        return $row ? $row : null;
    }
}
