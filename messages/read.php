<?php

require_once '../config/database.php';
require_once '../config/auth.php';

requireLogin();

header(
    'Content-Type: application/json'
);

$database = new Database();
$pdo = $database->connect();

$user_id =
    (int)$_SESSION['user_id'];

$clanId = getCurrentClanId();

$message_id =
    (int)(
        $_POST['message_id']
        ?? 0
    );

if ($message_id <= 0) {

    echo json_encode([
        'success' => false
    ]);

    exit;
}


$stmt = $pdo->prepare("
    INSERT IGNORE INTO family_message_reads
    (
        message_id,
        user_id,
        clan_id
    )

    VALUES
    (
        ?, ?, ?
    )
");


$stmt->execute([

    $message_id,

    $user_id,

    $clanId

]);


echo json_encode([
    'success' => true
]);

exit;