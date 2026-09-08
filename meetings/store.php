<?php

require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/language.php';
require_once '../config/auth.php';

requireAnyRoleAccess(['Administrator', 'Chairperson']);

$database = new Database();
$pdo = $database->connect();

$clanId = getCurrentClanId();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$title        = trim($_POST['title'] ?? '');
$description  = trim($_POST['description'] ?? '');
$meeting_date = trim($_POST['meeting_date'] ?? '');
$start_time   = trim($_POST['start_time'] ?? '');
$end_time     = trim($_POST['end_time'] ?? '');

if ($title === '' || $meeting_date === '') {
    header('Location: create.php?error=required');
    exit;
}

/*
|--------------------------------------------------------------------------
| Generate unique room code
|--------------------------------------------------------------------------
*/

function generateRoomCode(PDO $pdo): string
{
    do {

        $code = 'MK-'
            . strtoupper(
                substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8)
            );

        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM meetings
            WHERE room_code = ?
        ");

        $stmt->execute([$code]);

    } while ((int) $stmt->fetchColumn() > 0);

    return $code;
}

$roomCode = generateRoomCode($pdo);

$stmt = $pdo->prepare("
    INSERT INTO meetings
    (
        clan_id,
        title,
        description,
        meeting_date,
        start_time,
        end_time,
        room_code,
        meeting_status,
        created_by_user_id
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, 'upcoming', ?)
");

$stmt->execute([
    $clanId,
    $title,
    $description,
    $meeting_date,
    $start_time ?: null,
    $end_time ?: null,
    $roomCode,
    $_SESSION['user_id']
]);

/*
|--------------------------------------------------------------------------
| Notify all active members
|--------------------------------------------------------------------------
*/

$stmtUsers = $pdo->query("
    SELECT user_id
    FROM users
    WHERE is_deleted = 0
      AND account_status = 'active'
");

$users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

$notify = $pdo->prepare("
    INSERT INTO notifications
    (
        clan_id,
        user_id,
        title,
        notification_type,
        message
    )
    VALUES (?, ?, ?, 'Meeting', ?)
");

foreach ($users as $u) {
    $notify->execute([
        $clanId,
        $u['user_id'],
        __('new_meeting_notification'),
        __('new_meeting_message_prefix')
            . $title
            . __('new_meeting_message_suffix')
            . ' ' . $meeting_date
            . ($start_time ? ' ' . $start_time : '')
    ]);
}

header('Location: index.php?success=1');
exit;