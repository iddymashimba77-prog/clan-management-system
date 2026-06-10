<?php

require_once __DIR__ . '/constants.php';

/*
|--------------------------------------------------------------------------
| Secure Session Configuration
|--------------------------------------------------------------------------
*/

ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => false, // Badilisha kuwa true ukitumia HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| Session Timeout (30 Minutes)
|--------------------------------------------------------------------------
*/

$session_timeout = 1800;

if (isset($_SESSION['last_activity'])) {

    if ((time() - $_SESSION['last_activity']) > $session_timeout) {

        session_unset();
        session_destroy();

        header("Location: ../login.php");
        exit;
    }
}

$_SESSION['last_activity'] = time();