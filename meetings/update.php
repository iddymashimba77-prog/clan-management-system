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

$title        = trim($_POST['title'] ?? '');
$description  = trim($_POST['description'] ?? '');
$meeting_date = trim($_POST['meeting_date'] ?? '');
$start_time   = trim($_POST['start_time'] ?? '');
$end_time     = trim($_POST['end_time'] ?? '');

if ($title === '' || $meeting_date === '') {
    header('Location: edit.php?id=' . $id . '&error=required');
    exit;
}

$stmt = $pdo->prepare("
    UPDATE meetings
    SET
        title        = ?,
        description  = ?,
        meeting_date = ?,
        start_time   = ?,
        end_time     = ?
    WHERE meeting_id = ?
      AND clan_id = ?
      AND meeting_status <> 'live'
");

$stmt->execute([
    $title,
    $description,
    $meeting_date,
    $start_time ?: null,
    $end_time ?: null,
    $id,
    $clanId
]);

header('Location: index.php?success=1');
exit;