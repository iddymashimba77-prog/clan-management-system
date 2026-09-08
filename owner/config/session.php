<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {

    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_trans_sid', '0');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/Clan_Management_System/owner',
        'domain'   => '',
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);

    session_start();
}

if (
    isset($_SESSION['owner_last_activity']) &&
    time() - (int) $_SESSION['owner_last_activity'] > 1800
) {

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

    header(
        'Location: /Clan_Management_System/owner/login.php?timeout=1'
    );

    exit;
}

$_SESSION['owner_last_activity'] = time();