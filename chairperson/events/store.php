<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database = new Database();
$pdo = $database->connect();

$clanId = (int)($_SESSION['clan_id'] ?? 1);

$title        = trim($_POST['title']);
$description  = trim($_POST['description']);
$event_date   = $_POST['event_date'];
$start_time   = $_POST['start_time'] ?: null;
$end_time     = $_POST['end_time'] ?: null;
$location     = trim($_POST['location']);
$event_status = $_POST['event_status'];

$event_image = null;

if (
    isset($_FILES['event_image']) &&
    $_FILES['event_image']['error'] == 0
) {

    $uploadDir = __DIR__ . "/../../uploads/events/";

    if(!is_dir($uploadDir)){
        mkdir($uploadDir,0777,true);
    }

    $extension = strtolower(
        pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION)
    );

    $allowed = ['jpg','jpeg','png'];

    if (!in_array($extension, $allowed)) {
        die(__('only_jpg_jpeg_png_allowed'));
    }

    $event_image = uniqid("event_") . "." . $extension;

    if (!move_uploaded_file(
        $_FILES['event_image']['tmp_name'],
        $uploadDir . $event_image
    )) {

        die(__('upload_failed'));
    }
}

$stmt = $pdo->prepare("
INSERT INTO events(
clan_id,
title,
description,
event_date,
start_time,
end_time,
location,
event_image,
event_status,
created_by_user_id
)
VALUES(
?,?,?,?,?,?,?,?,?,?
)
");

$stmt->execute([
    $clanId,
    $title,
    $description,
    $event_date,
    $start_time,
    $end_time,
    $location,
    $event_image,
    $event_status,
    $_SESSION['user_id']
]);

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
        __('new_event_notification'),
        "event",
        __('new_event_message_prefix') . $title . __('new_event_message_suffix')
    ]);
}

header("Location:index.php?success=1");
exit;
