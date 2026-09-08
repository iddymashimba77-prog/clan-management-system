<?php

declare(strict_types=1);

require_once __DIR__ . '/session.php';

function ownerCsrfToken(): string
{
    if (empty($_SESSION['owner_csrf_token'])) {

        $_SESSION['owner_csrf_token'] =
            bin2hex(random_bytes(32));
    }

    return $_SESSION['owner_csrf_token'];
}

function ownerCsrfField(): string
{
    return
        '<input type="hidden" name="csrf_token" value="' .
        htmlspecialchars(
            ownerCsrfToken(),
            ENT_QUOTES,
            'UTF-8'
        ) .
        '">';
}

function verifyOwnerCsrf(): bool
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return false;
    }

    $token =
        $_POST['csrf_token'] ?? '';

    return
        !empty($token) &&
        !empty($_SESSION['owner_csrf_token']) &&
        hash_equals(
            $_SESSION['owner_csrf_token'],
            $token
        );
}

function requireOwnerCsrf(): void
{
    if (!verifyOwnerCsrf()) {

        http_response_code(419);

        exit('Invalid security token.');
    }
}