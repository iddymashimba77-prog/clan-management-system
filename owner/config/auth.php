<?php

declare(strict_types=1);

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/database.php';

function ownerIsLoggedIn(): bool
{
    return !empty($_SESSION['owner_id']);
}

function requireOwnerLogin(): void
{
    if (!ownerIsLoggedIn()) {

        header(
            'Location: /Clan_Management_System/owner/login.php'
        );

        exit;
    }
}

function ownerLogin(
    string $username,
    string $password
): bool {

    $database = new OwnerDatabase();
    $pdo = $database->connect();

    $stmt = $pdo->prepare("
        SELECT
            owner_id,
            username,
            password_hash,
            full_name,
            account_status
        FROM owner_users
        WHERE
            username = ?
            OR email = ?
        LIMIT 1
    ");

    $stmt->execute([
        $username,
        $username
    ]);

    $owner = $stmt->fetch();

    if (!$owner) {
        return false;
    }

    if ($owner['account_status'] !== 'active') {
        return false;
    }

    if (
        !password_verify(
            $password,
            $owner['password_hash']
        )
    ) {
        return false;
    }

    session_regenerate_id(true);

    $_SESSION['owner_id'] =
        (int) $owner['owner_id'];

    $_SESSION['owner_username'] =
        $owner['username'];

    $_SESSION['owner_name'] =
        $owner['full_name'];

    $stmt = $pdo->prepare("
        UPDATE owner_users
        SET last_login_at = NOW()
        WHERE owner_id = ?
    ");

    $stmt->execute([
        $owner['owner_id']
    ]);

    return true;
}

function ownerLogout(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {

        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            (bool) $params['secure'],
            (bool) $params['httponly']
        );
    }

    session_destroy();
}