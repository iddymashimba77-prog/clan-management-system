<?php

session_start();

require_once '../../config/database.php';
require_once '../../config/constants.php';
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

$_SESSION['success'] = "Member deleted successfully.";

header("Location: index.php");
exit;