<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$notification_id = (int)$_GET['id'] ?? 0;
$user_id = (int)$_SESSION['user_id'];

// Check if notification exists and belongs to user
$stmt = $pdo->prepare("
    SELECT notification_id 
    FROM notifications 
    WHERE notification_id = ? AND user_id = ?
");
$stmt->execute([$notification_id, $user_id]);
$notification = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$notification){
    die(__('notification_not_found_permission'));
}

// Delete notification
$stmt = $pdo->prepare("DELETE FROM notifications WHERE notification_id = ? AND user_id = ?");
$stmt->execute([$notification_id, $user_id]);

header("Location: index.php");
exit;