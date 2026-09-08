<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

$database = new Database();
$pdo = $database->connect();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Location: index.php');
    exit;
}

$id = isset($_GET['id'])
    ? (int)$_GET['id']
    : 0;

if ($id <= 0) {
    header('Location: index.php?error=invalid_id');
    exit;
}

try {

    $check = $pdo->prepare("
        SELECT notification_id, title
        FROM notifications
        WHERE notification_id = ?
          AND user_id = ?
        LIMIT 1
    ");

    $check->execute([$id, $_SESSION['user_id']]);

    $notification = $check->fetch(PDO::FETCH_ASSOC);

    if (!$notification) {
        header('Location: index.php?error=not_found');
        exit;
    }


    $stmt = $pdo->prepare("
        DELETE FROM notifications
        WHERE notification_id = ?
          AND user_id = ?
        LIMIT 1
    ");

    $stmt->execute([$id, $_SESSION['user_id']]);


    $log = $pdo->prepare("
        INSERT INTO activity_logs
        (
            user_id,
            action,
            table_name,
            record_id,
            description,
            ip_address
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $log->execute([
        $_SESSION['user_id'] ?? null,
        'DELETE_NOTIFICATION',
        'notifications',
        $id,
        'Notification deleted: ' .
        ($notification['title'] ?? ''),
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);


    header('Location: index.php?success=deleted');
    exit;

} catch (PDOException $e) {

    header('Location: index.php?error=delete_failed');
    exit;
}