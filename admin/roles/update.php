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
$roleName = trim($_POST['role_name'] ?? '');
$description = trim($_POST['description'] ?? '');
$status = $_POST['status'] ?? 'active';


if ($roleId <= 0 || $roleName === '') {

    $_SESSION['error'] = 'Invalid role information.';
    header('Location: index.php');
    exit;
}


if (!in_array($status, ['active', 'inactive'], true)) {
    $status = 'active';
}

$pdo = $database->connect();


$stmt = $pdo->prepare("
    SELECT role_id
    FROM roles
    WHERE LOWER(role_name) = LOWER(?)
      AND role_id <> ?
    LIMIT 1
");

$stmt->execute([
    $roleName,
    $roleId
]);

if ($stmt->fetch()) {

    $_SESSION['error'] = 'Another role already uses this name.';
    header('Location: edit.php?id=' . $roleId);
    exit;
}


try {

    $pdo->beginTransaction();


    $stmt = $pdo->prepare("
        UPDATE roles
        SET
            role_name = ?,
            description = ?,
            status = ?
        WHERE role_id = ?
    ");

    $stmt->execute([
        $roleName,
        $description ?: null,
        $status,
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
        'UPDATE',
        'roles',
        $roleId,
        'Updated role: ' . $roleName,
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);


    $pdo->commit();


    $_SESSION['success'] = 'Role updated successfully.';

    header('Location: view.php?id=' . $roleId);
    exit;


} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $_SESSION['error'] = 'Failed to update role.';

    header('Location: edit.php?id=' . $roleId);
    exit;
}