<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database = new Database();
$pdo = $database->connect();

$event_id = (int)$_POST['event_id'];

$title        = trim($_POST['title']);
$description  = trim($_POST['description']);
$event_date   = $_POST['event_date'];
$start_time   = $_POST['start_time'] ?: null;
$end_time     = $_POST['end_time'] ?: null;
$location     = trim($_POST['location']);
$event_status = $_POST['event_status'];

$stmt = $pdo->prepare("
SELECT event_image
FROM events
WHERE event_id=?
");
$stmt->execute([$event_id]);
$old = $stmt->fetch(PDO::FETCH_ASSOC);

$event_image = $old['event_image'];

if(isset($_FILES['event_image']) && $_FILES['event_image']['error']==0){

    $uploadDir = "../../uploads/events/";

    $extension = strtolower(pathinfo($_FILES['event_image']['name'],PATHINFO_EXTENSION));

    if(in_array($extension,['jpg','jpeg','png'])){

        $newName = uniqid("event_").".".$extension;

        move_uploaded_file(
            $_FILES['event_image']['tmp_name'],
            $uploadDir.$newName
        );

        if(!empty($old['event_image']) && file_exists($uploadDir.$old['event_image'])){
            unlink($uploadDir.$old['event_image']);
        }

        $event_image = $newName;
    }

}

$stmt = $pdo->prepare("
UPDATE events
SET

title=?,
description=?,
event_date=?,
start_time=?,
end_time=?,
location=?,
event_image=?,
event_status=?

WHERE event_id=?
");

$stmt->execute([

$title,
$description,
$event_date,
$start_time,
$end_time,
$location,
$event_image,
$event_status,
$event_id

]);

header("Location:index.php?updated=1");
exit;