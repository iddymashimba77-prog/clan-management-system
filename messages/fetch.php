<?php

require_once '../config/database.php';
require_once '../config/auth.php';

requireLogin();

header(
    'Content-Type: application/json'
);

$database = new Database();
$pdo = $database->connect();

$last_id =
    (int)(
        $_GET['last_id']
        ?? 0
    );

$current_user_id = (int)$_SESSION['user_id'];
$clanId = getCurrentClanId();


$stmt = $pdo->prepare("
    SELECT

        fm.message_id,

        fm.sender_id,

        fm.message_text,

        fm.reply_to_message_id,

        fm.created_at,

        u.username,

        u.profile_picture,

        r.role_name,

        reply.message_text
            AS reply_message,

        replyUser.username
            AS reply_username

    FROM family_messages fm

    INNER JOIN users u
        ON fm.sender_id = u.user_id

    LEFT JOIN roles r
        ON u.role_id = r.role_id

    LEFT JOIN family_messages reply
        ON fm.reply_to_message_id
        = reply.message_id
        AND reply.clan_id = ?

    LEFT JOIN users replyUser
        ON reply.sender_id
        = replyUser.user_id

    LEFT JOIN family_message_deletions fmd
        ON fm.message_id = fmd.message_id
        AND fmd.user_id = ?
        AND fmd.clan_id = ?

    WHERE
        fm.is_deleted = 0

    AND fmd.deletion_id IS NULL

    AND fm.message_id > ?

    AND fm.clan_id = ?

    ORDER BY
        fm.message_id ASC

    LIMIT 100
");


$stmt->execute([
    $clanId,
    $current_user_id,
    $clanId,
    $last_id,
    $clanId
]);


$messages =
    $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );


echo json_encode([

    'success' => true,

    'messages' => $messages

]);

exit;