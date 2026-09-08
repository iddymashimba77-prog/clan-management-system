<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}


/* CSRF */

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals(
        $_SESSION['csrf_token'],
        $_POST['csrf_token']
    )
) {
    $_SESSION['error'] = 'Invalid security token.';
    header('Location: index.php');
    exit;
}


$roleId = (int)($_POST['role_id'] ?? 0);

$permissions = $_POST['permissions'] ?? [];

$permissions = array_values(
    array_unique(
        array_map('intval', $permissions)
    )
);


if ($roleId <= 0) {

    $_SESSION['error'] = 'Invalid role.';
    header('Location: index.php');
    exit;
}

$pdo = $database->connect();


/* Check role */

$stmt = $pdo->prepare("
    SELECT role_id, role_name
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


try {

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | Remove old permissions
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        DELETE FROM role_permissions
        WHERE role_id = ?
    ");

    $stmt->execute([$roleId]);


    /*
    |--------------------------------------------------------------------------
    | Add new permissions
    |--------------------------------------------------------------------------
    */

    if (!empty($permissions)) {

        $stmt = $pdo->prepare("
            INSERT INTO role_permissions
            (
                role_id,
                permission_id
            )
            VALUES
            (
                ?,
                ?
            )
        ");


        foreach ($permissions as $permissionId) {

            if ($permissionId <= 0) {
                continue;
            }

            $stmt->execute([
                $roleId,
                $permissionId
            ]);
        }
    }


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
        'UPDATE_PERMISSIONS',
        'role_permissions',
        $roleId,
        'Updated permissions for role: ' . $role['role_name'],
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);


    $pdo->commit();


    $_SESSION['success'] =
        'Permissions updated successfully.';

    header('Location: view.php?id=' . $roleId);
    exit;


} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $_SESSION['error'] =
        'Failed to update permissions.';

    header('Location: permissions.php?id=' . $roleId);
    exit;
}