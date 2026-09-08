<?php

declare(strict_types=1);

final class OwnerClanDatabase
{
    private PDO $pdo;

    public function __construct(?string $databaseName = null)
    {
        $host = 'localhost';

        $resolved = $databaseName;

        if ($resolved === null || $resolved === '') {

            if (PHP_SAPI !== 'cli') {

                $status = session_status();

                if ($status === PHP_SESSION_NONE) {
                    session_start();
                }

                if (!empty($_SESSION['database_name'])) {
                    $resolved = (string) $_SESSION['database_name'];
                }
            }
        }

        $db = $resolved !== null && $resolved !== ''
            ? $resolved
            : 'clan_production_test';

        $user = 'root';
        $pass = '';

        $this->pdo = new PDO(
            "mysql:host={$host};dbname={$db};charset=utf8mb4",
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE =>
                    PDO::ERRMODE_EXCEPTION,

                PDO::ATTR_DEFAULT_FETCH_MODE =>
                    PDO::FETCH_ASSOC,

                PDO::ATTR_EMULATE_PREPARES =>
                    false
            ]
        );
    }

    public function connect(): PDO
    {
        return $this->pdo;
    }
}
