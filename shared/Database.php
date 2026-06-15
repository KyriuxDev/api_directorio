<?php
class Database {

    private static $instances = array();
    private $pdo;

    private function __construct(array $config) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['dbname'],
            $config['charset']
        );

        $this->pdo = new PDO($dsn, $config['user'], $config['password'], array(
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ));
    }

    /**
     * @param array  $config  Configuración de la BD
     * @param string $name    Identificador de la instancia ('default', 'imss', etc.)
     */
    public static function getInstance(array $config = array(), $name = 'default') {
        if (!isset(self::$instances[$name])) {
            self::$instances[$name] = new self($config);
        }
        return self::$instances[$name];
    }

    public function getConnection() {
        return $this->pdo;
    }
}