<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

$pdo = $database->connect();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

try {

    $stmt = $pdo->prepare("
        UPDATE users
        SET
            is_deleted = 0,
            deleted_at = NULL,
            account_status = 'active'
        WHERE user_id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);

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
        'RESTORE_FAMILY_MEMBER',
        'users',
        $id,
        'Family member restored from deleted records.',
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);

    header('Location: index.php?success=restored');
    exit;

} catch (PDOException $e) {

    header('Location: index.php?error=restore_failed');
    exit;
}