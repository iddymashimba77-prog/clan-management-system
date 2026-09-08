<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

$database = new Database();
$pdo = $database->connect();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: create.php');
    exit;
}

$user_id = (int)($_POST['user_id'] ?? 0);

$title = trim($_POST['title'] ?? '');

$notification_type = trim(
    $_POST['notification_type'] ?? 'General'
);

$message = trim($_POST['message'] ?? '');

if ($title === '' || $message === '') {

    header('Location: create.php?error=required');
    exit;
}

try {

    /*
     * user_id = 0 means notification for all members.
     * Database column is NOT NULL, therefore for broadcast
     * notifications we create one notification per active member.
     */

    if ($user_id === 0) {

        $stmt = $pdo->query("
            SELECT user_id
            FROM users
            WHERE is_deleted = 0
              AND account_status = 'active'
        ");

        $users = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $insert = $pdo->prepare("
            INSERT INTO notifications
            (
                user_id,
                title,
                notification_type,
                message,
                is_read
            )
            VALUES (?, ?, ?, ?, 0)
        ");

        $pdo->beginTransaction();

        foreach ($users as $memberId) {

            $insert->execute([
                (int)$memberId,
                $title,
                $notification_type,
                $message
            ]);
        }

        $pdo->commit();

        $description =
            'Broadcast notification sent to all active members.';

    } else {

        $check = $pdo->prepare("
            SELECT user_id
            FROM users
            WHERE user_id = ?
              AND is_deleted = 0
              AND account_status = 'active'
            LIMIT 1
        ");

        $check->execute([$user_id]);

        if (!$check->fetch()) {

            header('Location: create.php?error=invalid_member');
            exit;
        }

        $insert = $pdo->prepare("
            INSERT INTO notifications
            (
                user_id,
                title,
                notification_type,
                message,
                is_read
            )
            VALUES (?, ?, ?, ?, 0)
        ");

        $insert->execute([
            $user_id,
            $title,
            $notification_type,
            $message
        ]);

        $description =
            'Notification created for member ID ' . $user_id;
    }


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
        'CREATE_NOTIFICATION',
        'notifications',
        null,
        $description,
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);

    header('Location: index.php?success=created');
    exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header('Location: create.php?error=save_failed');
    exit;
}