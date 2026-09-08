<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

$database = new Database();
$pdo = $database->connect();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$title = trim($_POST['title'] ?? '');

$message = trim($_POST['message'] ?? '');

$notification_type = trim(
    $_POST['notification_type'] ?? 'General'
);

$recipient = $_POST['recipient'] ?? 'all';

if ($title === '' || $message === '') {
    header('Location: create.php?error=required');
    exit;
}

try {

    $pdo->beginTransaction();


    /*
     * SEND TO ALL ACTIVE MEMBERS
     */

    if ($recipient === 'all') {

        $stmt = $pdo->query("
            SELECT user_id
            FROM users
            WHERE is_deleted = 0
              AND account_status = 'active'
        ");

        $members = $stmt->fetchAll(PDO::FETCH_COLUMN);

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

        foreach ($members as $memberId) {

            $insert->execute([
                (int)$memberId,
                $title,
                $notification_type,
                $message
            ]);
        }


        $description =
            'Notification sent to all active members.';


    } else {

        /*
         * SEND TO ONE MEMBER
         */

        $memberId = (int)$recipient;

        if ($memberId <= 0) {

            $pdo->rollBack();

            header(
                'Location: create.php?error=invalid_recipient'
            );

            exit;
        }


        $check = $pdo->prepare("
            SELECT user_id
            FROM users
            WHERE user_id = ?
              AND is_deleted = 0
              AND account_status = 'active'
            LIMIT 1
        ");

        $check->execute([$memberId]);

        if (!$check->fetch()) {

            $pdo->rollBack();

            header(
                'Location: create.php?error=invalid_recipient'
            );

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
            $memberId,
            $title,
            $notification_type,
            $message
        ]);


        $description =
            'Notification sent to member ID ' .
            $memberId;
    }


    $pdo->commit();


    /*
     * ACTIVITY LOG
     */

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
        'SEND_NOTIFICATION',
        'notifications',
        null,
        $description,
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);


    header('Location: index.php?success=sent');
    exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header('Location: create.php?error=send_failed');
    exit;
}