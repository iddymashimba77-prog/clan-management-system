<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireAdministrator();

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

$clanId = getCurrentClanId();

try {

    $stmt = $pdo->prepare("
        SELECT
            user_id,
            member_code,
            first_name,
            middle_name,
            last_name
        FROM users
        WHERE user_id = ?
          AND clan_id = ?
        LIMIT 1
    ");

    $stmt->execute([$userId, $clanId]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$member) {
        header('Location: index.php?error=member_not_found');
        exit;
    }

    if (!empty($member['is_deleted'])) {
        header('Location: index.php?error=already_deleted');
        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE users
        SET
            is_deleted = 1,
            deleted_at = NOW(),
            account_status = 'inactive'
        WHERE user_id = ?
          AND clan_id = ?
    ");

    $stmt->execute([$userId, $clanId]);

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
            ip_address,
            clan_id
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $log->execute([
        $_SESSION['user_id'] ?? null,
        'MEMBER_DELETED',
        'users',
        $userId,
        "Member {$member['member_code']} - {$fullName} was moved to deleted records.",
        $_SERVER['REMOTE_ADDR'] ?? null,
        $clanId
    ]);

    header(
        'Location: index.php?success=member_deleted'
    );
    exit;

} catch (PDOException $e) {

    header(
        'Location: index.php?error=delete_failed'
    );
    exit;
}