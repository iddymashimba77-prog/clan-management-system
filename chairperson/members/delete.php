<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database = new Database();
$pdo = $database->connect();

$clanId = getCurrentClanId();

$user_id = (int)($_GET['id'] ?? 0);

if($user_id <= 0){
    header("Location:index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| HAKIKI ROLE YA USER
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT
u.user_id,
u.role_id,
r.role_name
FROM users u
INNER JOIN roles r
ON u.role_id=r.role_id
WHERE u.user_id=?
AND u.clan_id = ?
LIMIT 1
");

$stmt->execute([$user_id, $clanId]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$user){
    die(__('member_not_found'));
}

/*
|--------------------------------------------------------------------------
| Chairperson haruhusiwi kufuta Administrator
|--------------------------------------------------------------------------
*/

if($user['role_name'] == 'Administrator'){
    die(__('access_denied'));
}

/*
|--------------------------------------------------------------------------
| Soft Delete
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
UPDATE users
SET

is_deleted=1

WHERE user_id=?
AND clan_id=?
");

$stmt->execute([$user_id, $clanId]);

header("Location:index.php?deleted=1");

exit;