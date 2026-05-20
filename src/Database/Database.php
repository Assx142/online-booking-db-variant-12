<?php
namespace App\Database;
use App\Exceptions\RepositoryException;

class Database {
    private static ?Database $instance = null;
    private \PDO $connection;

    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->connection = new \PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (\PDOException $e) {
            throw new RepositoryException("Ошибка подключения к БД: " . $e->getMessage());
        }
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): \PDO {
        return $this->connection;
    }
}
