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
    header('Location: create.php');
    exit;
}


$roleName = trim($_POST['role_name'] ?? '');
$description = trim($_POST['description'] ?? '');
$status = $_POST['status'] ?? 'active';


if ($roleName === '') {

    $_SESSION['error'] = 'Role name is required.';
    header('Location: create.php');
    exit;
}


if (!in_array($status, ['active', 'inactive'], true)) {
    $status = 'active';
}

$pdo = $database->connect();


/* Check duplicate */

$stmt = $pdo->prepare("
    SELECT role_id
    FROM roles
    WHERE LOWER(role_name) = LOWER(?)
    LIMIT 1
");

$stmt->execute([$roleName]);

if ($stmt->fetch()) {

    $_SESSION['error'] = 'This role already exists.';
    header('Location: create.php');
    exit;
}


try {

    $pdo->beginTransaction();


    $stmt = $pdo->prepare("
        INSERT INTO roles
        (
            role_name,
            description,
            status
        )
        VALUES
        (
            ?,
            ?,
            ?
        )
    ");

    $stmt->execute([
        $roleName,
        $description ?: null,
        $status
    ]);


    $roleId = (int)$pdo->lastInsertId();


    /* Activity log */

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
        'CREATE',
        'roles',
        $roleId,
        'Created role: ' . $roleName,
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);


    $pdo->commit();


    $_SESSION['success'] = 'Role created successfully.';

    header('Location: view.php?id=' . $roleId);
    exit;


} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $_SESSION['error'] = 'Failed to create role.';

    header('Location: create.php');
    exit;
}