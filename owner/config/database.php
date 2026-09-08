<?php

declare(strict_types=1);

final class OwnerDatabase
{
    private PDO $pdo;

    public function __construct()
    {
        $host = 'localhost';
        $db   = 'clan_owner';
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