<?php

require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/language.php';
require_once '../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$clanId = getCurrentClanId();

$gallery_id = (int)$_GET['id'] ?? 0;
$user_id = (int)$_SESSION['user_id'];

// Check if photo exists and user has permission to delete
$stmt = $pdo->prepare("
    SELECT g.*, u.user_id as uploader_id
    FROM gallery g
    INNER JOIN users u ON g.user_id = u.user_id
    WHERE g.gallery_id = ?
      AND g.clan_id = ?
");
$stmt->execute([$gallery_id, $clanId]);
$photo = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$photo){
    die(__('photo_not_found'));
}

// Check permission: only uploader, chairperson, or admin can delete
$canDelete = false;
if($user_id == $photo['uploader_id']){
    $canDelete = true;
}

if(hasRole('Chairperson') || hasRole('Administrator')){
    $canDelete = true;
}

if(!$canDelete){
    die(__('no_permission_delete_photo'));
}

// Delete the file from filesystem
$file_path = '../gallery/uploads/' . $photo['file_name'];
if(file_exists($file_path)){
    unlink($file_path);
}

// Delete from database
$stmt = $pdo->prepare("DELETE FROM gallery WHERE gallery_id = ? AND clan_id = ?");
$stmt->execute([$gallery_id, $clanId]);

header("Location: index.php");
exit;
