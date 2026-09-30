<?php

declare(strict_types=1);

require_once __DIR__ . '/env.php';
require_once __DIR__ . '/../install/seed.php';

/*
| CENTRAL-DB MODE
| All clans live in ONE database. A new clan = one row in `clans`
| plus its administrator in `users` / `user_roles`. Isolation by clan_id.
*/

// Host/user/pass/dbName arguments are ignored; kept for compatibility.
function clanDbConnection(
    string $host = '',
    string $user = '',
    string $pass = '',
    ?string $dbName = null
): PDO {
    $name = envValue('DB_NAME', 'clan_production_test');

    if (PHP_SAPI === 'cli' && getenv('DB_HOST') === false) {
        $dsn = "mysql:unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname={$name};charset=utf8mb4";
    } else {
        $dsn = "mysql:host=" . envValue('DB_HOST', 'localhost')
             . ";port=" . envValue('DB_PORT', '3306')
             . ";dbname={$name};charset=utf8mb4";
    }

    return new PDO($dsn, envValue('DB_USER', 'root'), envValue('DB_PASS', ''), [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
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
    return false; // no per-clan database any more
}

function resolveDatabaseByDomain(string $domain): ?string
{
    return null; // one central database
}

function getCurrentDomainDatabase(): ?string
{
    return null;
}

function getTenantDatabaseCandidates(): array
{
    return [envValue('DB_NAME', 'clan_production_test')];
}

function createClanDatabase(array $config): void
{
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

    if ($clanId <= 0 || $adminUsername === '') {
        throw new RuntimeException('Invalid clan configuration.');
    }

    $pdo = clanDbConnection();

    $chk = $pdo->prepare("SELECT COUNT(*) FROM clans WHERE clan_id = ?");
    $chk->execute([$clanId]);
    if ((int)$chk->fetchColumn() > 0) {
        throw new RuntimeException("clan_id {$clanId} already exists.");
    }

    // Seed roles/permissions only on a completely empty database.
    if ((int)$pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn() === 0) {
        seedSystemData($pdo);
    }

    $nameParts = preg_split('/\s+/', trim($adminName));
    $firstName = ucwords(strtolower($nameParts[0] ?? $adminUsername));
    $lastName  = isset($nameParts[1])
        ? ucwords(strtolower(implode(' ', array_slice($nameParts, 1))))
        : $adminUsername;

    $memberCode = 'CLAN-' . str_pad((string)$clanId, 7, '0', STR_PAD_LEFT);

    $pdo->beginTransaction();
    try {
        $pdo->prepare("
            INSERT INTO clans
              (clan_id, clan_name, clan_code, contact_email, activation_status, subscription_status)
            VALUES (?, ?, ?, ?, ?, ?)
        ")->execute([
            $clanId, $clanName, $clanCode,
            $contactEmail !== '' ? $contactEmail : null,
            $activationStatus, $subscription,
        ]);

        $pdo->prepare("
            INSERT INTO users
              (clan_id, member_code, first_name, last_name, gender, email, username,
               password_hash, role_id, account_status, family_member_type, is_deceased)
            VALUES (?, ?, ?, ?, 'Male', ?, ?, ?, 5, 'active', 'blood', 0)
        ")->execute([
            $clanId, $memberCode, $firstName, $lastName,
            $adminEmail !== '' ? $adminEmail : null,
            $adminUsername,
            password_hash($adminPassword, PASSWORD_DEFAULT),
        ]);

        $userId = (int)$pdo->lastInsertId();

        $pdo->prepare("INSERT INTO user_roles (clan_id, user_id, role_id) VALUES (?, ?, 5)")
            ->execute([$clanId, $userId]);

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}
