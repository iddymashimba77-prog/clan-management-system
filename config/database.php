<?php

require_once __DIR__ . '/constants.php';

class Database
{
    private string $host = 'localhost';

    private string $database_name = 'clan_production_test';

    private string $username = 'root';

    private string $password = '';

    private ?PDO $connection = null;

    public function __construct(?string $databaseName = null)
    {
        $resolved = $databaseName;

        if ($resolved === null || $resolved === '') {
            // First check if there's a session database name
            $resolved = getTenantDatabaseName();
            
            // If no session, try to resolve by domain
            if ($resolved === null || $resolved === '') {
                require_once __DIR__ . '/clan_db.php';
                $resolved = getCurrentDomainDatabase();
            }
        }

        if ($resolved !== null && $resolved !== '') {
            $this->database_name = $resolved;
        }
    }

    public function connect(): PDO
    {
        if ($this->connection === null) {

            try {

                $dsn =
                    "mysql:host={$this->host};" .
                    "dbname={$this->database_name};" .
                    "charset=utf8mb4";


                $this->connection = new PDO(
                    $dsn,
                    $this->username,
                    $this->password,
                    [
                        PDO::ATTR_ERRMODE =>
                            PDO::ERRMODE_EXCEPTION,

                        PDO::ATTR_DEFAULT_FETCH_MODE =>
                            PDO::FETCH_ASSOC,

                        PDO::ATTR_EMULATE_PREPARES =>
                            false
                    ]
                );


            } catch (PDOException $e) {
                // If the specific database doesn't exist, fall back to default
                if (strpos($e->getMessage(), 'Unknown database') !== false && $this->database_name !== 'clan_production_test') {
                    $this->database_name = 'clan_production_test';
                    $this->connection = null;
                    return $this->connect();
                }
                
                die(
                    "Database Connection Failed: " .
                    $e->getMessage()
                );

            }
        }

        return $this->connection;
    }

    /*
    |--------------------------------------------------------------------------
    | SWITCH DATABASE (per clan / tenant)
    |--------------------------------------------------------------------------
    |
    | Enables a fresh connection to a different database while reusing this
    | class instance. Used so each clan can have its own database.
    |--------------------------------------------------------------------------
    */

    public function switchDatabase(string $databaseName): void
    {
        if ($databaseName === '') {
            return;
        }

        $this->database_name = $databaseName;
        $this->connection = null;
    }

    /*
    |--------------------------------------------------------------------------
    | DATABASE INFORMATION
    |--------------------------------------------------------------------------
    */

    public function getHost(): string
    {
        return $this->host;
    }


    public function getDatabaseName(): string
    {
        return $this->database_name;
    }


    public function getUsername(): string
    {
        return $this->username;
    }


    public function getPassword(): string
    {
        return $this->password;
    }
}


/*
|--------------------------------------------------------------------------
| TENANT DATABASE NAME RESOLUTION
|--------------------------------------------------------------------------
|
| Database-per-clan: once a user logs in, the resolved tenant database name
| is stored in the session. Every subsequent new Database() will connect to
| that clan's own database so each client uses their own isolated database.
|
| The default (no session / legacy) remains clan_production_test.
|--------------------------------------------------------------------------
*/

function getTenantDatabaseName(): ?string
{
    if (PHP_SAPI === 'cli') {
        return null;
    }

    $status = session_status();

    if ($status === PHP_SESSION_NONE) {
        session_start();
    }

    if ($status === PHP_SESSION_DISABLED) {
        return null;
    }

    $name = $_SESSION['database_name'] ?? null;

    if ($name === null || $name === '') {
        return null;
    }

    return (string) $name;
}


$database = new Database();

$pdo = $database->connect();
