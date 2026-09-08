<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$roleId = (int)($_GET['id'] ?? 0);

if ($roleId <= 0) {
    header('Location: index.php');
    exit;
}


$stmt = $pdo->prepare("
    SELECT
        role_id,
        role_name,
        status
    FROM roles
    WHERE role_id = ?
    LIMIT 1
");

$stmt->execute([$roleId]);

$role = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$role) {

    $_SESSION['error'] = 'Role not found.';
    header('Location: index.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Protect default Member role
|--------------------------------------------------------------------------
*/

if (strcasecmp($role['role_name'], 'Member') === 0) {

    $_SESSION['error'] =
        'The default Member role cannot be deactivated.';

    header('Location: index.php');
    exit;
}


$newStatus =
    ($role['status'] === 'active')
        ? 'inactive'
        : 'active';


try {

    $stmt = $pdo->prepare("
        UPDATE roles
        SET status = ?
        WHERE role_id = ?
    ");

    $stmt->execute([
        $newStatus,
        $roleId
    ]);


    $stmt = $pdo->prepare("
        INSERT INTO activity_logs
        (
            user_id,
            action,
            table_name,
            record_id,
            description,
            ip_address
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?
        )
    ");

    $stmt->execute([
        $_SESSION['user_id'] ?? null,
        'TOGGLE_STATUS',
        'roles',
        $roleId,
        'Changed role "' .
        $role['role_name'] .
        '" status to ' .
        $newStatus,
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);


    $_SESSION['success'] =
        'Role status changed successfully.';


} catch (Throwable $e) {

    $_SESSION['error'] =
        'Failed to change role status.';
}


header('Location: index.php');
exit;