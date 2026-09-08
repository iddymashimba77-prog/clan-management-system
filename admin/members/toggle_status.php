<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$userId = (int)($_POST['user_id'] ?? 0);

if ($userId <= 0) {
    header('Location: index.php?error=invalid_member');
    exit;
}

$database = new Database();
$pdo = $database->connect();

try {

    $stmt = $pdo->prepare("
        SELECT user_id, first_name, middle_name, last_name, account_status
        FROM users
        WHERE user_id = ?
        LIMIT 1
    ");

    $stmt->execute([$userId]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$member) {
        header('Location: index.php?error=member_not_found');
        exit;
    }

    $newStatus =
        ($member['account_status'] === 'active')
        ? 'inactive'
        : 'active';

    $stmt = $pdo->prepare("
        UPDATE users
        SET account_status = ?
        WHERE user_id = ?
    ");

    $stmt->execute([
        $newStatus,
        $userId
    ]);

    $fullName = trim(
        $member['first_name'] . ' ' .
        ($member['middle_name'] ?? '') . ' ' .
        $member['last_name']
    );

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
        'ACCOUNT_STATUS_CHANGED',
        'users',
        $userId,
        "Account status for {$fullName} changed to {$newStatus}.",
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);

    header(
        'Location: index.php?success=status_updated'
    );
    exit;

} catch (PDOException $e) {

    header(
        'Location: index.php?error=status_update_failed'
    );
    exit;
}