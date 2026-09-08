<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/tenant.php';

/*
|--------------------------------------------------------------------------
| GET USER ROLES
|--------------------------------------------------------------------------
*/

function getUserRoles(int $userId): array
{
    static $cache = [];

    if (isset($cache[$userId])) {
        return $cache[$userId];
    }

    global $pdo;

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        $database = new Database();
        $pdo = $database->connect();
    }

    $clanId = getCurrentClanId();

    if ($clanId === null) {
        return [];
    }

    $stmt = $pdo->prepare("
        SELECT DISTINCT r.role_name
        FROM roles r
        INNER JOIN user_roles ur
            ON ur.role_id = r.role_id
            AND ur.user_id = ?
            AND ur.clan_id = ?
        WHERE r.status = 'active'
    ");

    $stmt->execute([$userId, $clanId]);

    $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);

    /*
     * Backward compatibility with users.role_id
     */

    if (!$roles) {

        $stmt = $pdo->prepare("
            SELECT r.role_name
            FROM users u
            INNER JOIN roles r
                ON r.role_id = u.role_id
            WHERE u.user_id = ?
              AND u.clan_id = ?
              AND u.is_deleted = 0
            LIMIT 1
        ");

        $stmt->execute([$userId, $clanId]);

        $role = $stmt->fetchColumn();

        if ($role !== false) {
            $roles[] = $role;
        }
    }

    $cache[$userId] = $roles;

    return $roles;
}


/*
|--------------------------------------------------------------------------
| HAS ROLE
|--------------------------------------------------------------------------
*/

function hasRole(string $roleName): bool
{
    if (empty($_SESSION['user_id'])) {
        return false;
    }

    return in_array(
        $roleName,
        getUserRoles((int) $_SESSION['user_id']),
        true
    );
}


/*
|--------------------------------------------------------------------------
| HAS ANY ROLE
|--------------------------------------------------------------------------
*/

function hasAnyRole(array $roles): bool
{
    foreach ($roles as $role) {

        if (hasRole($role)) {
            return true;
        }
    }

    return false;
}


/*
|--------------------------------------------------------------------------
| ROLE HELPERS
|--------------------------------------------------------------------------
*/

function isMember(): bool
{
    return hasRole('Member');
}

function isTreasurer(): bool
{
    return hasRole('Treasurer');
}

function isSecretary(): bool
{
    return hasRole('Secretary');
}

function isChairperson(): bool
{
    return hasRole('Chairperson');
}

function isAdministrator(): bool
{
    return hasRole('Administrator');
}


/*
|--------------------------------------------------------------------------
| REQUIRE ROLE
|--------------------------------------------------------------------------
*/

function requireRole(string $roleName): void
{
    if (!hasRole($roleName)) {

        http_response_code(403);

        exit('Access Denied');
    }
}


/*
|--------------------------------------------------------------------------
| REQUIRE ANY ROLE
|--------------------------------------------------------------------------
*/

function requireAnyRole(array $roles): void
{
    if (!hasAnyRole($roles)) {

        http_response_code(403);

        exit('Access Denied');
    }
}


/*
|--------------------------------------------------------------------------
| DASHBOARD REDIRECT
|--------------------------------------------------------------------------
*/

function redirectDashboard(): void
{
    if (isAdministrator()) {

        header(
            'Location: ' .
            BASE_URL .
            'admin/dashboard.php'
        );

    } elseif (isChairperson()) {

        header(
            'Location: ' .
            BASE_URL .
            'chairperson/dashboard.php'
        );

    } elseif (isTreasurer()) {

        header(
            'Location: ' .
            BASE_URL .
            'treasurer/dashboard.php'
        );

    } elseif (isSecretary()) {

        header(
            'Location: ' .
            BASE_URL .
            'secretary/dashboard.php'
        );

    } else {

        header(
            'Location: ' .
            BASE_URL .
            'member/dashboard.php'
        );
    }

    exit;
}