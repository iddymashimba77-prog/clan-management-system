<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| CLAN DATABASE MANAGER
|--------------------------------------------------------------------------
|
| Creates a dedicated MySQL database for every clan/tenant so that each
| clan keeps its data fully isolated from every other clan.
|
| Every created database gets:
|   - the base application schema
|   - tenant migrations (clans table, clan_id columns, clans fix)
|   - the activation table
|   - system seed data (roles, permissions)
|   - the clan row
|   - the clan administrator (role_id = 5)
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../install/seed.php';

function clanDbConnection(
    string $host,
    string $user,
    string $pass,
    ?string $dbName = null
): PDO {

    $isCli = (PHP_SAPI === 'cli');

    if ($isCli) {

        $dsn = "mysql:unix_socket=/opt/lampp/var/mysql/mysql.sock;charset=utf8mb4";

        if ($dbName !== null && $dbName !== '') {
            $dsn = "mysql:unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname={$dbName};charset=utf8mb4";
        }

    } else {

        $dsn = "mysql:host={$host};charset=utf8mb4";

        if ($dbName !== null && $dbName !== '') {
            $dsn = "mysql:host={$host};dbname={$dbName};charset=utf8mb4";
        }
    }

    return new PDO(
        $dsn,
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
}

function runSqlFile(PDO $pdo, string $file): void
{
    if (!is_file($file)) {
        throw new RuntimeException("SQL file not found: {$file}");
    }

    $sql = file_get_contents($file);

    if ($sql === false || trim($sql) === '') {
        throw new RuntimeException("SQL file is empty: {$file}");
    }

    $pdo->exec($sql);
}

function clanDbExists(string $dbName): bool
{
    try {

        $server = clanDbConnection('localhost', 'root', '');

        $stmt = $server->query(
            "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = " .
            $server->quote($dbName)
        );

        return (bool) $stmt->fetchColumn();

    } catch (Throwable $e) {

        return false;
    }
}

/*
|--------------------------------------------------------------------------
| RESOLVE DATABASE BY DOMAIN
|--------------------------------------------------------------------------
|
| Returns the database name for a given domain from owner_installations.
| This enables multi-tenant routing where each clan has its own database.
|--------------------------------------------------------------------------
*/

function resolveDatabaseByDomain(string $domain): ?string
{
    $domain = strtolower(trim($domain));
    
    // Remove port if present
    $domain = preg_replace('/:\d+$/', '', $domain);
    
    if ($domain === '' || $domain === 'localhost') {
        return null;
    }

    try {
        $ownerDb = new PDO(
            (PHP_SAPI === 'cli')
                ? 'mysql:unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=clan_owner;charset=utf8mb4'
                : 'mysql:host=localhost;dbname=clan_owner;charset=utf8mb4',
            'root',
            '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );

        $stmt = $ownerDb->prepare("
            SELECT database_name
            FROM owner_installations
            WHERE domain_name = ?
              AND database_name IS NOT NULL
              AND database_name <> ''
            LIMIT 1
        ");
        
        $stmt->execute([$domain]);
        $result = $stmt->fetchColumn();
        
        return $result !== false ? (string)$result : null;
        
    } catch (Throwable $e) {
        return null;
    }
}

/*
|--------------------------------------------------------------------------
| GET CURRENT DOMAIN DATABASE
|--------------------------------------------------------------------------
|
| Returns the database name for the current request's domain.
|--------------------------------------------------------------------------
*/

function getCurrentDomainDatabase(): ?string
{
    $host = $_SERVER['HTTP_HOST'] ?? '';
    return resolveDatabaseByDomain($host);
}

/*
|--------------------------------------------------------------------------
| TENANT DATABASE CANDIDATES
|--------------------------------------------------------------------------
|
| Returns the ordered list of databases to search at login time, so a user
| can log into their own dedicated clan database. The legacy shared DB is
| always tried first, then every database_name registered in the owner DB.
|--------------------------------------------------------------------------
*/

function getTenantDatabaseCandidates(): array
{
    $candidates = ['clan_production_test'];

    try {

        $ownerDb = new PDO(
            (PHP_SAPI === 'cli')
                ? 'mysql:unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=clan_owner;charset=utf8mb4'
                : 'mysql:host=localhost;dbname=clan_owner;charset=utf8mb4',
            'root',
            '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );

        $rows = $ownerDb->query(
            "SELECT DISTINCT database_name
             FROM owner_installations
             WHERE database_name IS NOT NULL AND database_name <> ''"
        )->fetchAll();

        foreach ($rows as $row) {

            $name = (string)$row['database_name'];

            if ($name !== '' && !in_array($name, $candidates, true)) {
                $candidates[] = $name;
            }
        }

    } catch (Throwable $e) {
        // Fall back to the default shared database only.
    }

    return $candidates;
}

/*
|--------------------------------------------------------------------------
| CREATE CLAN DATABASE
|--------------------------------------------------------------------------
|
| @param array $config Supported keys:
|   - db_name: string        (database name to create)
|   - clan_id: int
|   - clan_name: string
|   - clan_code: string
|   - contact_email: string  (optional clan contact email)
|   - activation_status: string (pending|active|suspended|revoked)
|   - subscription_status: string (trial|active|...)
|   - admin_name: string
|   - admin_username: string
|   - admin_password: string
|   - admin_email: string    (optional)
|
| @return void
|--------------------------------------------------------------------------
*/

function createClanDatabase(array $config): void
{
    $dbName           = (string)($config['db_name'] ?? '');
    $clanId           = (int)($config['clan_id'] ?? 0);
    $clanName         = (string)($config['clan_name'] ?? '');
    $clanCode         = (string)($config['clan_code'] ?? '');
    $contactEmail     = (string)($config['contact_email'] ?? '');
    $activationStatus = (string)($config['activation_status'] ?? 'active');
    $subscription     = (string)($config['subscription_status'] ?? 'active');
    $adminName        = (string)($config['admin_name'] ?? $clanName);
    $adminUsername    = (string)($config['admin_username'] ?? '');
    $adminPassword    = (string)($config['admin_password'] ?? '');
    $adminEmail       = (string)($config['admin_email'] ?? '');

    if ($dbName === '' || $clanId <= 0) {
        throw new RuntimeException('Invalid clan database configuration.');
    }

    if (clanDbExists($dbName)) {
        return;
    }

    $baseDir = dirname(__DIR__);

    $server = clanDbConnection('localhost', 'root', '');

    $safeDbName = str_replace('`', '``', $dbName);

    $server->exec(
        "CREATE DATABASE IF NOT EXISTS `{$safeDbName}`
         CHARACTER SET utf8mb4
         COLLATE utf8mb4_unicode_ci"
    );

    $pdo = clanDbConnection('localhost', 'root', '', $dbName);

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    runSqlFile($pdo, $baseDir . '/database/clan_management_system.sql');

    runSqlFile($pdo, $baseDir . '/database/activation.sql');

    runSqlFile($pdo, $baseDir . '/database/updates/001_create_clans_table.sql');

    $pdo->exec("ALTER TABLE `clans` MODIFY `clan_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT");

    runSqlFile($pdo, $baseDir . '/database/updates/002_add_clan_id_to_tables.sql');

    runSqlFile($pdo, $baseDir . '/database/updates/003_fix_clans_schema.sql');

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    seedSystemData($pdo);

    $nameParts = preg_split('/\s+/', trim($adminName));
    $firstName = ucwords(strtolower($nameParts[0] ?? $adminUsername));
    $lastName  = isset($nameParts[1])
        ? ucwords(strtolower(implode(' ', array_slice($nameParts, 1))))
        : $adminUsername;

    $memberCode = 'CLAN-' . str_pad((string)$clanId, 7, '0', STR_PAD_LEFT);

    $stmt = $pdo->prepare("
        INSERT INTO clans
        (
            clan_id,
            clan_name,
            clan_code,
            contact_email,
            activation_status,
            subscription_status
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?
        )
    ");

    $stmt->execute([
        $clanId,
        $clanName,
        $clanCode,
        $contactEmail !== '' ? $contactEmail : null,
        $activationStatus,
        $subscription
    ]);

    $stmt = $pdo->prepare("
        INSERT INTO users
        (
            clan_id,
            member_code,
            first_name,
            last_name,
            gender,
            email,
            username,
            password_hash,
            role_id,
            account_status,
            family_member_type,
            is_deceased
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            'Male',
            ?,
            ?,
            ?,
            5,
            'active',
            'blood',
            0
        )
    ");

    $stmt->execute([
        $clanId,
        $memberCode,
        $firstName,
        $lastName,
        $adminEmail !== '' ? $adminEmail : null,
        $adminUsername,
        password_hash($adminPassword, PASSWORD_DEFAULT)
    ]);

    $userId = (int)$pdo->lastInsertId();

    $stmt = $pdo->prepare("
        INSERT INTO user_roles
        (
            clan_id,
            user_id,
            role_id
        )
        VALUES
        (
            ?,
            ?,
            5
        )
    ");

    $stmt->execute([
        $clanId,
        $userId
    ]);
}
