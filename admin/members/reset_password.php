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
            last_name
        FROM users
        WHERE user_id = ?
        AND is_deleted = 0
        LIMIT 1
    ");

    $stmt->execute([$userId]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$member) {
        header('Location: index.php?error=member_not_found');
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Generate temporary password
    |--------------------------------------------------------------------------
    */

    $temporaryPassword =
        strtoupper(substr(
            bin2hex(random_bytes(4)),
            0,
            8
        ));

    $passwordHash = password_hash(
        $temporaryPassword,
        PASSWORD_DEFAULT
    );

    $stmt = $pdo->prepare("
        UPDATE users
        SET
            password_hash = ?
        WHERE user_id = ?
    ");

    $stmt->execute([
        $passwordHash,
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
        'PASSWORD_RESET',
        'users',
        $userId,
        "Password reset for {$member['member_code']} - {$fullName}.",
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);

    /*
    |--------------------------------------------------------------------------
    | Return temporary password to index
    |--------------------------------------------------------------------------
    */

    header(
        'Location: index.php?success=password_reset'
        . '&password=' . urlencode($temporaryPassword)
        . '&member=' . urlencode($member['member_code'])
    );

    exit;

} catch (Exception $e) {

    header(
        'Location: index.php?error=password_reset_failed'
    );
    exit;
}