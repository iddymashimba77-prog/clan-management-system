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

$id = isset($_POST['meeting_id'])
    ? (int) $_POST['meeting_id']
    : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("
    UPDATE meetings
    SET meeting_status = 'live'
    WHERE meeting_id = ?
      AND clan_id = ?
      AND meeting_status = 'upcoming'
");

$stmt->execute([$id, $clanId]);

if ((int) $stmt->rowCount() === 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT *
    FROM meetings
    WHERE meeting_id = ?
      AND clan_id = ?
    LIMIT 1
");

$stmt->execute([$id, $clanId]);

$meeting = $stmt->fetch(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Notify all active members that the meeting is live now
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

$meetingTitle = $meeting['title'] ?? '';

foreach ($users as $u) {
    $notify->execute([
        $clanId,
        $u['user_id'],
        __('meeting_live_notification'),
        __('meeting_live_message_prefix')
            . $meetingTitle
            . __('meeting_live_message_suffix')
    ]);
}

header('Location: index.php?started=1');
exit;