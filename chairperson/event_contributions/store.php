<?php


ini_set('display_startup_errors',1);

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database = new Database();
$pdo = $database->connect();

$clanId = (int)($_SESSION['clan_id'] ?? 1);

$event_id        = (int)$_POST['event_id'];
$title           = trim($_POST['title']);
$description     = trim($_POST['description']);
$required_amount = $_POST['required_amount'];
$deadline        = $_POST['deadline'];
$status          = $_POST['status'];

$stmt = $pdo->prepare("
INSERT INTO event_contributions(

clan_id,
event_id,
title,
description,
required_amount,
deadline,
status,
created_by

)

VALUES(

?,?,?,?,?,?,?,?

)
");

$stmt->execute([

    $clanId,
    $event_id,
    $title,
    $description,
    $required_amount,
    $deadline,
    $status,
    $_SESSION['user_id']
    
    ]);

    $contribution_id = $pdo->lastInsertId();

// Payment methods are now handled directly from payment_methods table
// No need to store links in contribution_method_links for event contributions

$stmtUsers = $pdo->query("
SELECT user_id
FROM users
WHERE account_status='active'
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
VALUES
(?,?,?,?,?)
");

foreach($users as $u){

    $notify->execute([

        $clanId,
        $u['user_id'],

        __('new_contribution_notification'),

        "contribution",

        __('new_contribution_message_prefix') . $title . __('new_contribution_message_suffix')

    ]);

}

header("Location:index.php?success=1");
exit;

