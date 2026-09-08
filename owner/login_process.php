<?php

declare(strict_types=1);

require_once __DIR__ . '/config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: login.php');

    exit;
}

if (!verifyOwnerCsrf()) {

    http_response_code(419);

    exit('Invalid security token.');
}

$login =
    trim($_POST['login'] ?? '');

$password =
    $_POST['password'] ?? '';

if (
    $login === '' ||
    $password === ''
) {

    $_SESSION['owner_error'] =
        'Username and password are required.';

    header('Location: login.php');

    exit;
}

if (
    !ownerLogin(
        $login,
        $password
    )
) {

    $_SESSION['owner_error'] =
        'Invalid owner credentials.';

    header('Location: login.php');

    exit;
}

header('Location: index.php');

exit;