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
        SELECT
            user_id,
            member_code,
            first_name,
            middle_name,
            last_name,
            is_deleted
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

    if (empty($member['is_deleted'])) {
        header('Location: index.php?error=member_not_deleted');
        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE users
        SET
            is_deleted = 0,
            deleted_at = NULL,
            account_status = 'active'
        WHERE user_id = ?
    ");

    $stmt->execute([$userId]);

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
        'MEMBER_RESTORED',
        'users',
        $userId,
        "Member {$member['member_code']} - {$fullName} was restored.",
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);

    header(
        'Location: index.php?success=member_restored'
    );
    exit;

} catch (PDOException $e) {

    header(
        'Location: index.php?error=restore_failed'
    );
    exit;
}