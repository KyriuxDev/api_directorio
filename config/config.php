<?php
return [
    'db' => [
        'host'     => '127.0.0.1',
        'port'     => '3306',
        'dbname'   => 'cdi_directorio',
        'user'     => 'root',
        'password' => 'admin',
        'charset'  => 'utf8mb4',
    ],
    'api' => [
        'token' => 'B1n4r10',
    ],
    'ldap' => [
        'url'       => 'ldaps://sur.imss.gob.mx:636',
        'base_dn'   => 'OU=Oaxaca,OU=Sur Usuarios,DC=sur,DC=imss,DC=gob,DC=mx',
        'bind_dn'   => 'pcsur.admin@imss.gob.mx',
        'password'  => '*4adm1n2012',
        'page_size' => 300,
    ],
];