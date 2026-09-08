<?php

require_once '../config/database.php';
require_once '../config/language.php';
require_once '../config/auth.php';

requireLogin();

header(
    'Content-Type: application/json'
);

if (
    $_SERVER['REQUEST_METHOD']
    !== 'POST'
) {

    echo json_encode([
        'success' => false,
        'message' => __('invalid_request')
    ]);

    exit;
}


$database = new Database();
$pdo = $database->connect();

$user_id =
    (int)$_SESSION['user_id'];

$clanId = getCurrentClanId();


$message =
    trim(
        $_POST['message_text']
        ?? ''
    );


$reply_to =
    (int)(
        $_POST['reply_to_message_id']
        ?? 0
    );


if ($message === '') {

    echo json_encode([
        'success' => false,
        'message' => __('message_cannot_be_empty')
    ]);

    exit;
}


if (strlen($message) > 5000) {

    echo json_encode([
        'success' => false,
        'message' => __('message_too_long')
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Find Mention
|--------------------------------------------------------------------------
*/

$mentioned_user_id = null;


/*
|--------------------------------------------------------------------------
| @username
|--------------------------------------------------------------------------
*/

if (
    preg_match(
        '/@([a-zA-Z0-9_.-]+)/',
        $message,
        $matches
    )
) {

    $mentionedUsername =
        $matches[1];


    $stmt = $pdo->prepare("
        SELECT user_id

        FROM users

        WHERE username = ?

        AND account_status = 'active'

        AND clan_id = ?

        LIMIT 1
    ");

    $stmt->execute([
        $mentionedUsername,
        $clanId
    ]);


    $mentioned_user_id =
        $stmt->fetchColumn();


    if (!$mentioned_user_id) {
        $mentioned_user_id = null;
    }

}


/*
|--------------------------------------------------------------------------
| Validate Reply
|--------------------------------------------------------------------------
*/

if ($reply_to > 0) {

    $stmt = $pdo->prepare("
        SELECT message_id

        FROM family_messages

        WHERE message_id = ?

        AND clan_id = ?

        LIMIT 1
    ");

    $stmt->execute([
        $reply_to,
        $clanId
    ]);


    if (!$stmt->fetch()) {
        $reply_to = null;
    }

} else {

    $reply_to = null;

}


/*
|--------------------------------------------------------------------------
| Insert Message
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    INSERT INTO family_messages
    (
        sender_id,
        message_text,
        reply_to_message_id,
        mentioned_user_id,
        clan_id
    )

    VALUES
    (
        ?, ?, ?, ?, ?
    )
");

$stmt->execute([

    $user_id,

    $message,

    $reply_to,

    $mentioned_user_id,

    $clanId

]);


$message_id =
    $pdo->lastInsertId();


/*
|--------------------------------------------------------------------------
| Send Mention Notification
|--------------------------------------------------------------------------
*/

if (
    $mentioned_user_id
    &&
    (int)$mentioned_user_id !== $user_id
) {

    $stmt = $pdo->prepare("
        SELECT username
        FROM users
        WHERE user_id = ?
          AND clan_id = ?
        LIMIT 1
    ");

    $stmt->execute([
        $user_id,
        $clanId
    ]);

    $senderUsername =
        $stmt->fetchColumn();


    $notify = $pdo->prepare("
        INSERT INTO notifications
        (
            user_id,
            title,
            notification_type,
            message,
            clan_id
        )

        VALUES
        (?, ?, ?, ?, ?)
    ");


    $notify->execute([

        $mentioned_user_id,

        __('you_were_mentioned'),

        'message',

        $senderUsername
        . ' ' . __('mentioned_you_in_family_group'),

        $clanId

    ]);

}


echo json_encode([

    'success' => true,

    'message_id' => $message_id

]);

exit;