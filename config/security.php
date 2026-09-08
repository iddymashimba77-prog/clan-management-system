<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| CSRF TOKEN
|--------------------------------------------------------------------------
*/

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}


function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' .
        htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') .
        '">';
}


function verify_csrf()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return false;
    }

    $token = $_POST['csrf_token'] ?? '';

    return !empty($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}


/*
|--------------------------------------------------------------------------
| SECURITY REDIRECT
|--------------------------------------------------------------------------
*/

function securityError($message)
{
    $_SESSION['security_error'] = $message;

    header('Location: index.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| FLASH MESSAGE
|--------------------------------------------------------------------------
*/

function securitySuccess($message)
{
    $_SESSION['security_success'] = $message;
}


/*
|--------------------------------------------------------------------------
| ADMIN PASSWORD VERIFICATION
|--------------------------------------------------------------------------
*/

function verifyAdminPassword(PDO $pdo, $userId, $password)
{
    if (empty($password)) {
        return false;
    }

    $stmt = $pdo->prepare("
        SELECT password_hash
        FROM users
        WHERE user_id = ?
          AND is_deleted = 0
        LIMIT 1
    ");

    $stmt->execute([$userId]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || empty($user['password_hash'])) {
        return false;
    }

    return password_verify($password, $user['password_hash']);
}


/*
|--------------------------------------------------------------------------
| REQUIRE CSRF
|--------------------------------------------------------------------------
*/

function requireCsrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    if (!verify_csrf()) {

        http_response_code(419);

        exit('Invalid or expired security token.');
    }
}