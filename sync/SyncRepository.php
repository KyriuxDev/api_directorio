<?php
class SyncRepository {

    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function upsert($data) {
        $sql = "INSERT INTO trabajadores
                    (matricula, nombre_completo, correo, extension, telefono,
                     departamento, categoria, adscripcion, delegacion,
                     sam_account, activo, ultima_sync)
                VALUES
                    (:matricula, :nombre_completo, :correo, :extension, :telefono,
                     :departamento, :categoria, :adscripcion, :delegacion,
                     :sam_account, :activo, NOW())
                ON DUPLICATE KEY UPDATE
                    nombre_completo = VALUES(nombre_completo),
                    correo          = VALUES(correo),
                    extension       = VALUES(extension),
                    telefono        = VALUES(telefono),
                    departamento    = VALUES(departamento),
                    categoria       = VALUES(categoria),
                    adscripcion     = VALUES(adscripcion),
                    delegacion      = VALUES(delegacion),
                    sam_account     = VALUES(sam_account),
                    activo          = VALUES(activo),
                    ultima_sync     = NOW()";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':matricula'       => $data['matricula'],
            ':nombre_completo' => $data['nombre_completo'],
            ':correo'          => $data['correo'],
            ':extension'       => $data['extension'],
            ':telefono'        => $data['telefono'],
            ':departamento'    => $data['departamento'],
            ':categoria'       => $data['categoria'],
            ':adscripcion'     => $data['adscripcion'],
            ':delegacion'      => $data['delegacion'],
            ':sam_account'     => $data['sam_account'],
            ':activo'          => $data['activo'],
        ]);

        // rowCount: 1 = INSERT nuevo, 2 = UPDATE con cambio, 0 = sin cambio
        return $stmt->rowCount() === 1 ? 'nuevo' : 'actualizado';
    }

    public function logSync($stats, $duracion) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO sync_log (total, nuevos, actualizados, errores, duracion_seg)
             VALUES (:total, :nuevos, :actualizados, :errores, :duracion)"
        );
        $stmt->execute([
            ':total'        => $stats['total'],
            ':nuevos'       => $stats['nuevos'],
            ':actualizados' => $stats['actualizados'],
            ':errores'      => $stats['errores'],
            ':duracion'     => $duracion,
        ]);
    }

    public function getLastLog() {
        $stmt = $this->pdo->query(
            "SELECT * FROM sync_log ORDER BY fecha DESC LIMIT 1"
        );
        return $stmt->fetch();
    }
}