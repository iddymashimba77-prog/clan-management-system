<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

// Same central database (kept for compatibility).
final class OwnerClanDatabase
{
    private PDO $pdo;

    public function __construct(?string $databaseName = null)
    {
        $this->pdo = (new OwnerDatabase())->connect();
    }

    public function connect(): PDO
    {
        return $this->pdo;
    }
}
