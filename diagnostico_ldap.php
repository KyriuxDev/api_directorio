<?php
putenv('LDAPTLS_REQCERT=never');

$l = ldap_connect('ldaps://sur.imss.gob.mx:636');
ldap_set_option($l, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($l, LDAP_OPT_REFERRALS, 0);
ldap_bind($l, 'pcsur.admin@imss.gob.mx', '*4adm1n2012');

$r = ldap_search(
    $l,
    'OU=Oaxaca,OU=Sur Usuarios,DC=sur,DC=imss,DC=gob,DC=mx',
    '(&(objectClass=user)(objectCategory=person)(mail=*))',
    ['*']
);

$e = ldap_get_entries($l, $r);

if ($e['count'] > 0) {
    $u = $e[0];
    for ($i = 0; $i < $u['count']; $i++) {
        $attr  = $u[$i];
        $valor = isset($u[$attr][0]) ? $u[$attr][0] : '(vacío)';
        echo $attr . ': ' . $valor . PHP_EOL;
    }
}