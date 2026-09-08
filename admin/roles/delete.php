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
        role_name
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
| Protect default roles
|--------------------------------------------------------------------------
*/

$protectedRoles = [
    'Admin',
    'Administrator',
    'Member'
];


if (
    in_array(
        strtolower($role['role_name']),
        array_map('strtolower', $protectedRoles),
        true
    )
) {

    $_SESSION['error'] =
        'This system role is protected and cannot be deleted.';

    header('Location: index.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Check assigned users
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM user_roles
    WHERE role_id = ?
");

$stmt->execute([$roleId]);

$totalUsers = (int)$stmt->fetchColumn();


if ($totalUsers > 0) {

    $_SESSION['error'] =
        'This role cannot be deleted because users are assigned to it.';

    header('Location: index.php');
    exit;
}


try {

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | Delete role permissions
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        DELETE FROM role_permissions
        WHERE role_id = ?
    ");

    $stmt->execute([$roleId]);


    /*
    |--------------------------------------------------------------------------
    | Delete role
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        DELETE FROM roles
        WHERE role_id = ?
    ");

    $stmt->execute([$roleId]);


    /*
    |--------------------------------------------------------------------------
    | Activity log
    |--------------------------------------------------------------------------
    */

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
        'DELETE',
        'roles',
        $roleId,
        'Deleted role: ' . $role['role_name'],
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);


    $pdo->commit();


    $_SESSION['success'] =
        'Role deleted successfully.';


} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $_SESSION['error'] =
        'Failed to delete role.';
}


header('Location: index.php');
exit;