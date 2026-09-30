<?php

require_once __DIR__ . '/env.php';
require_once __DIR__ . '/constants.php';

class Database
{
    private string $host;
    private string $port;
    private string $database_name;
    private string $username;
    private string $password;
    private ?PDO $connection = null;

    // $databaseName is ignored: one central database for all clans.
    // Isolation is done with clan_id (see config/tenant.php).
    public function __construct(?string $databaseName = null)
    {
        $this->host          = envValue('DB_HOST', 'localhost');
        $this->port          = envValue('DB_PORT', '3306');
        $this->database_name = envValue('DB_NAME', 'clan_production_test');
        $this->username      = envValue('DB_USER', 'root');
        $this->password      = envValue('DB_PASS', '');
    }

    public function connect(): PDO
    {
        if ($this->connection === null) {
            try {
                if (PHP_SAPI === 'cli' && getenv('DB_HOST') === false) {
                    $dsn = "mysql:unix_socket=/opt/lampp/var/mysql/mysql.sock;"
                         . "dbname={$this->database_name};charset=utf8mb4";
                } else {
                    $dsn = "mysql:host={$this->host};port={$this->port};"
                         . "dbname={$this->database_name};charset=utf8mb4";
                }

                $this->connection = new PDO($dsn, $this->username, $this->password, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                error_log('DB connection failed: ' . $e->getMessage());
                die('Database connection failed.');
            }
        }
        return $this->connection;
    }

    // Compatibility: no-op in central-DB mode.
    public function switchDatabase(string $databaseName): void {}

    public function getHost(): string         { return $this->host; }
    public function getDatabaseName(): string { return $this->database_name; }
    public function getUsername(): string     { return $this->username; }
    public function getPassword(): string     { return $this->password; }
}

function getTenantDatabaseName(): ?string
{
    return envValue('DB_NAME', 'clan_production_test');
}

$database = new Database();

$pdo = $database->connect();
