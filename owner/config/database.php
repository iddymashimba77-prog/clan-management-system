<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/env.php';

// Owner tables now live in the central database.
final class OwnerDatabase
{
    private PDO $pdo;

    public function __construct()
    {
        $name = envValue('DB_NAME', 'clan_production_test');

        if (PHP_SAPI === 'cli' && getenv('DB_HOST') === false) {
            $dsn = "mysql:unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname={$name};charset=utf8mb4";
        } else {
            $dsn = "mysql:host=" . envValue('DB_HOST', 'localhost')
                 . ";port=" . envValue('DB_PORT', '3306')
                 . ";dbname={$name};charset=utf8mb4";
        }

        $this->pdo = new PDO($dsn, envValue('DB_USER', 'root'), envValue('DB_PASS', ''), [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    public function connect(): PDO
    {
        return $this->pdo;
    }
}
