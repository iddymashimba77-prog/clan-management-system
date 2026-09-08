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

$database = new Database();
$pdo = $database->connect();


$typeName = trim($_POST['type_name'] ?? '');
$description = trim($_POST['description'] ?? '');
$status = $_POST['status'] ?? 'active';


if ($typeName === '') {

    $_SESSION['error'] = 'Contribution type name is required.';

    header('Location: create.php');
    exit;
}


if (!in_array($status, ['active', 'inactive'], true)) {
    $status = 'active';
}


/* Check duplicate */

$stmt = $pdo->prepare("
    SELECT contribution_type_id
    FROM contribution_types
    WHERE type_name = ?
    LIMIT 1
");

$stmt->execute([$typeName]);

if ($stmt->fetch()) {

    $_SESSION['error'] = 'This contribution type already exists.';

    header('Location: create.php');
    exit;
}


/* Logged in user */

$createdBy = $_SESSION['user_id'] ?? null;


/* Insert */

$stmt = $pdo->prepare("
    INSERT INTO contribution_types
    (
        type_name,
        description,
        status,
        created_by_user_id
    )
    VALUES
    (?, ?, ?, ?)
");

$stmt->execute([
    $typeName,
    $description !== '' ? $description : null,
    $status,
    $createdBy
]);


$_SESSION['success'] = 'Contribution type created successfully.';

header('Location: index.php');
exit;