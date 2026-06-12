<?php
class SyncService {

    private $config;
    private $repository;

    public function __construct($config) {
        $this->config     = $config;
        $this->repository = new SyncRepository();
    }

    public function getStatus() {
        $log = $this->repository->getLastLog();
        if (!$log) {
            return ['mensaje' => 'Ningún sync ejecutado todavía'];
        }
        return $log;
    }

    public function runSync() {
        set_time_limit(300); // 5 min máximo para sincronizar 3,232 usuarios

        $inicio = microtime(true);
        $stats  = ['total' => 0, 'nuevos' => 0, 'actualizados' => 0, 'errores' => 0];

        $ldap     = $this->connectLdap();
        $usuarios = $this->fetchAllUsers($ldap);

        $stats['total'] = count($usuarios);

        foreach ($usuarios as $usuario) {
            try {
                $resultado = $this->repository->upsert($usuario);
                $resultado === 'nuevo' ? $stats['nuevos']++ : $stats['actualizados']++;
            } catch (Exception $e) {
                $stats['errores']++;
            }
        }

        ldap_close($ldap);

        $duracion = round(microtime(true) - $inicio, 2);
        $this->repository->logSync($stats, $duracion);

        return array_merge($stats, ['duracion_seg' => $duracion]);
    }

    // ─── LDAP ────────────────────────────────────────────────────────────────

    private function connectLdap() {
        // 0x6006 = LDAP_OPT_X_TLS_REQUIRE_CERT (no definida en PHP 5.6)
        // 0      = LDAP_OPT_X_TLS_NEVER
        ldap_set_option(null, 0x6006, 0);

        $ldap = ldap_connect($this->config['ldap']['url']);
        if (!$ldap) {
            throw new Exception('No se pudo conectar al servidor LDAP');
        }

        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

        $bound = @ldap_bind(
            $ldap,
            $this->config['ldap']['bind_dn'],
            $this->config['ldap']['password']
        );

        if (!$bound) {
            throw new Exception('Credenciales LDAP: ' . ldap_error($ldap));
        }

        return $ldap;
    }

    private function fetchAllUsers($ldap) {
        $atributos = [
            'employeeid',
            'displayname',
            'mail',
            'othertelephone',           // ← extensión interna (608)
            'telephonenumber',          // ← teléfono completo (951 1325357)
            'department',
            'title',
            'physicaldeliveryofficename',
            'samaccountname',
            'useraccountcontrol',
            'delegacion',               // ← atributo real en el AD de IMSS
        ];

        $usuarios = [];
        $cookie   = '';

        do {
            ldap_control_paged_result($ldap, 300, true, $cookie);

            $result = ldap_search(
                $ldap,
                $this->config['ldap']['base_dn'],
                '(&(objectClass=user)(objectCategory=person))',
                $atributos
            );

            if (!$result) break;

            $entries = ldap_get_entries($ldap, $result);

            for ($i = 0; $i < $entries['count']; $i++) {
                $usuario = $this->mapEntry($entries[$i]);
                if ($usuario) {
                    $usuarios[] = $usuario;
                }
            }

            ldap_control_paged_result_response($ldap, $result, $cookie);

        } while ($cookie !== null && $cookie !== '');

        return $usuarios;
    }

    private function mapEntry($entry) {
        $get = function ($entry, $attr) {
            return isset($entry[$attr][0]) ? trim($entry[$attr][0]) : null;
        };

        $sam = $get($entry, 'samaccountname');
        if (!$sam) return null;

        $matricula = $get($entry, 'employeeid') ?: 'SAM-' . $sam;

        // bit 2 de userAccountControl = cuenta deshabilitada
        $uac    = (int)$get($entry, 'useraccountcontrol');
        $activo = ($uac & 2) ? 0 : 1;

        return [
            'matricula'       => $matricula,
            'nombre_completo' => $get($entry, 'displayname') ?: $sam,
            'correo'          => $get($entry, 'mail'),
            'extension'       => $get($entry, 'othertelephone'),
            'telefono'        => $get($entry, 'telephonenumber'),
            'departamento'    => $get($entry, 'department'),
            'categoria'       => $get($entry, 'title'),
            'adscripcion'     => $get($entry, 'physicaldeliveryofficename'),
            'delegacion'      => $get($entry, 'delegacion') ?: 'Delegación Oaxaca',
            'sam_account'     => $sam,
            'activo'          => $activo,
        ];
    }
}