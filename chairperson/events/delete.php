<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database = new Database();
$pdo = $database->connect();

$event_id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
SELECT event_image
FROM events
WHERE event_id=?
");
$stmt->execute([$event_id]);

$event = $stmt->fetch(PDO::FETCH_ASSOC);

if($event){

    if(!empty($event['event_image'])){

        $file = "../../uploads/events/".$event['event_image'];

        if(file_exists($file)){
            unlink($file);
        }

    }

    $stmt = $pdo->prepare("
    DELETE FROM events
    WHERE event_id=?
    ");

    $stmt->execute([$event_id]);

}

header("Location:index.php?deleted=1");
exit;