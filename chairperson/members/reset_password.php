<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$database = new Database();
$pdo = $database->connect();

$clanId = getCurrentClanId();

$user_id = (int)$_POST['user_id'];
$password = trim($_POST['password']);

if(strlen($password) < 6){
    header("Location: edit.php?id=".$user_id."&error=1");
    exit;
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("
SELECT r.role_name
FROM users u
INNER JOIN roles r
ON u.role_id=r.role_id
WHERE u.user_id=?
AND u.clan_id = ?
LIMIT 1
");

$stmt->execute([$user_id, $clanId]);

$role = $stmt->fetchColumn();

if($role == 'Administrator'){
    die(__('access_denied'));
}

$stmt = $pdo->prepare("
UPDATE users
SET password_hash=?
WHERE user_id=?
AND clan_id=?
");

$stmt->execute([
    $password_hash,
    $user_id,
    $clanId
]);

header("Location: edit.php?id=".$user_id."&success=1");
exit;