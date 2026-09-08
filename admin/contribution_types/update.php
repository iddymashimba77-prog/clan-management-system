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


$id = (int)($_POST['contribution_type_id'] ?? 0);
$typeName = trim($_POST['type_name'] ?? '');
$description = trim($_POST['description'] ?? '');
$status = $_POST['status'] ?? 'active';


if ($id <= 0 || $typeName === '') {

    $_SESSION['error'] = 'Invalid contribution type data.';

    header('Location: index.php');
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
      AND contribution_type_id != ?
    LIMIT 1
");

$stmt->execute([
    $typeName,
    $id
]);

if ($stmt->fetch()) {

    $_SESSION['error'] = 'Another contribution type already uses this name.';

    header("Location: edit.php?id=$id");
    exit;
}


/* Update */

$stmt = $pdo->prepare("
    UPDATE contribution_types

    SET
        type_name = ?,
        description = ?,
        status = ?

    WHERE contribution_type_id = ?
");

$stmt->execute([
    $typeName,
    $description !== '' ? $description : null,
    $status,
    $id
]);


$_SESSION['success'] = 'Contribution type updated successfully.';

header('Location: index.php');
exit;