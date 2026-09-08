<?php

session_start();

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$user_id = (int) $_GET['id'];

$database = new Database();
$pdo = $database->connect();

/*
|--------------------------------------------------------------------------
| Prevent deleting an Administrator
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT role_id
    FROM users
    WHERE user_id = ?
    LIMIT 1
");

$stmt->execute([$user_id]);

$targetRole = $stmt->fetchColumn();

if ((int)$targetRole === ROLE_ADMINISTRATOR) {
    $_SESSION['error'] = __('cannot_delete_administrator');
    header('Location: index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Soft Delete
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    UPDATE users
    SET
        is_deleted = 1,
        deleted_at = NOW()
    WHERE user_id = ?
");

$stmt->execute([$user_id]);

$_SESSION['success'] = __('member_deleted_successfully');

header("Location: index.php");
exit;