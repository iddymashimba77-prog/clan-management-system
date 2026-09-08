<?php

declare(strict_types=1);

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/session.php';

/*
|--------------------------------------------------------------------------
| TENANT (CLAN) CONTEXT
|--------------------------------------------------------------------------
|
| Central tenant-resolution helpers for multi-tenant SaaS architecture.
|
| Every tenant-owned table has a `clan_id` column. All tenant-sensitive
| database operations MUST be scoped by the authenticated user's clan_id.
|
| NEVER trust a clan_id supplied by the browser (GET/POST/cookie/hidden
| field). Only use values derived from the authenticated session.
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| GET CURRENT TENANT ID
|--------------------------------------------------------------------------
|
| Returns the clan_id from the authenticated session.
|
| @return int|null  The authenticated user's clan_id, or null if none.
|--------------------------------------------------------------------------
*/

function getCurrentClanId(): ?int
{
    if (empty($_SESSION['clan_id'])) {
        return null;
    }

    return (int) $_SESSION['clan_id'];
}


/*
|--------------------------------------------------------------------------
| REQUIRE TENANT CONTEXT
|--------------------------------------------------------------------------
|
| Ensures the authenticated session has an active clan context.
| Redirects to login if missing.
|--------------------------------------------------------------------------
*/

function requireTenantContext(): void
{
    if (getCurrentClanId() === null) {
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| BIND STATEMENT WITH CLAN ID
|--------------------------------------------------------------------------
|
| Convenience wrapper that appends the current clan_id to a parameter list
| for a tenant-scoped prepared statement.
|
| Usage example:
|
|   $stmt = $pdo->prepare("
|       SELECT * FROM users
|       WHERE user_id = ?
|         AND clan_id = ?
|   ");
|   $stmt->execute(bindClanId([$userId]));
|
| @param array $params  Existing parameters.
| @return array Parameters with clan_id appended.
|--------------------------------------------------------------------------
*/

function bindClanId(array $params = []): array
{
    $clanId = getCurrentClanId();

    if ($clanId === null) {
        throw new RuntimeException(
            'Tenant context is required for clan-scoped query.'
        );
    }

    $params[] = $clanId;

    return $params;
}


/*
|--------------------------------------------------------------------------
| GET CLAN NAME
|--------------------------------------------------------------------------
|
| Returns the name of the current tenant's clan.
|--------------------------------------------------------------------------
*/

function getClanName(): ?string
{
    $clanId = getCurrentClanId();

    if ($clanId === null) {
        return null;
    }

    global $pdo;

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        $database = new Database();
        $pdo = $database->connect();
    }

    $stmt = $pdo->prepare("
        SELECT clan_name
        FROM clans
        WHERE clan_id = ?
        LIMIT 1
    ");

    $stmt->execute([$clanId]);

    $name = $stmt->fetchColumn();

    return $name !== false ? (string) $name : null;
}


/*
|--------------------------------------------------------------------------
| VERIFY CLAN OWNERSHIP OF A RECORD
|--------------------------------------------------------------------------
|
| Helper that verifies a record in a given tenant-owned table belongs to
| the authenticated user's clan. This is the primary data-isolation guard.
|
| @param PDO    $pdo        Database connection.
| @param string $table      Table name (tenant-owned).
| @param string $idColumn   PK column of the table.
| @param int    $recordId   PK value being verified.
|
| @return bool True if the record belongs to the current clan.
|--------------------------------------------------------------------------
*/

function verifyClanOwnership(PDO $pdo, string $table, string $idColumn, int $recordId): bool
{
    $clanId = getCurrentClanId();

    if ($clanId === null) {
        return false;
    }

    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $idColumn = preg_replace('/[^a-zA-Z0-9_]/', '', $idColumn);

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM `{$table}`
        WHERE `{$idColumn}` = ?
          AND clan_id = ?
    ");

    $stmt->execute([$recordId, $clanId]);

    return (int) $stmt->fetchColumn() > 0;
}


/*
|--------------------------------------------------------------------------
| CHECK CLAN STATUS / SUBSCRIPTION
|--------------------------------------------------------------------------
|
| Verifies whether the current clan is active and not suspended/expired.
|
| @return array{active: bool, status: ?string, subscription: ?string}
|--------------------------------------------------------------------------
*/

function getClanStatus(): array
{
    $clanId = getCurrentClanId();

    if ($clanId === null) {
        return [
            'active'       => false,
            'status'       => null,
            'subscription' => null,
        ];
    }

    global $pdo;

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        $database = new Database();
        $pdo = $database->connect();
    }

    $stmt = $pdo->prepare("
        SELECT activation_status, subscription_status, subscription_expires_at
        FROM clans
        WHERE clan_id = ?
        LIMIT 1
    ");

    $stmt->execute([$clanId]);

    $clan = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$clan) {
        return [
            'active'       => false,
            'status'       => null,
            'subscription' => null,
        ];
    }

    $active = ($clan['activation_status'] ?? '') === 'active';

    if (
        ($clan['subscription_status'] ?? '') === 'expired' ||
        ($clan['subscription_status'] ?? '') === 'suspended' ||
        ($clan['subscription_status'] ?? '') === 'cancelled'
    ) {
        $active = false;
    }

    if (
        !empty($clan['subscription_expires_at']) &&
        strtotime($clan['subscription_expires_at']) < time()
    ) {
        $active = false;
    }

    return [
        'active'       => $active,
        'status'       => $clan['activation_status'] ?? null,
        'subscription' => $clan['subscription_status'] ?? null,
    ];
}


/*
|--------------------------------------------------------------------------
| REQUIRE ACTIVE CLAN
|--------------------------------------------------------------------------
|
| Guards tenant-facing pages against suspended/expired clans.
|--------------------------------------------------------------------------
*/

function requireActiveClan(): void
{
    $status = getClanStatus();

    if (!$status['active']) {
        http_response_code(403);
        exit('Your clan account is inactive or suspended. Please contact support.');
    }
}
