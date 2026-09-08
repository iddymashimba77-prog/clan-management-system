<?php

declare(strict_types=1);

require_once __DIR__ . '/constants.php';

/*
|--------------------------------------------------------------------------
| SECURE SESSION CONFIGURATION
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {

    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_trans_sid', '0');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

/*
|--------------------------------------------------------------------------
| SESSION TIMEOUT
|--------------------------------------------------------------------------
*/

$sessionTimeout = 1800; // 30 minutes

if (
    isset($_SESSION['last_activity']) &&
    (time() - (int) $_SESSION['last_activity']) > $sessionTimeout
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

    header('Location: ' . BASE_URL . 'login.php?timeout=1');
    exit;
}

$_SESSION['last_activity'] = time();