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
    DELETE FROM meetings
    WHERE meeting_id = ?
      AND clan_id = ?
      AND meeting_status <> 'live'
");

$stmt->execute([$id, $clanId]);

header('Location: index.php?deleted=1');
exit;