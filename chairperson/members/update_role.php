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

$user_id = (int)($_POST['user_id'] ?? 0);
$role_id = (int)($_POST['role_id'] ?? 0);

if ($user_id <= 0 || $role_id <= 0) {

    $_SESSION['error'] = __('invalid_request');

    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Hakikisha role ipo
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT role_id
FROM roles
WHERE role_id=?
LIMIT 1
");

$stmt->execute([$role_id]);

if (!$stmt->fetch()) {

    $_SESSION['error'] = __('selected_role_does_not_exist');

    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Update Role
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
UPDATE users
SET role_id=?
WHERE user_id=?
AND clan_id=?
");

$stmt->execute([$role_id, $user_id, $clanId]);

$_SESSION['success'] = __('user_role_updated_successfully');

header("Location: index.php");
exit;