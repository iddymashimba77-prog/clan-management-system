<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database = new Database();
$pdo = $database->connect();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
DELETE FROM event_contributions
WHERE contribution_id=?
");

$stmt->execute([$id]);

header("Location:index.php?deleted=1");
exit;