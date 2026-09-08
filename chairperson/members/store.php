<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: create.php");
    exit;
}

$database = new Database();
$pdo = $database->connect();

$clanId = getCurrentClanId();

$user_id  = (int)($_POST['user_id'] ?? 0);
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');
$role_id  = (int)($_POST['role_id'] ?? 0);

if (
    $user_id <= 0 ||
    empty($username) ||
    empty($password) ||
    $role_id <= 0
) {
    $_SESSION['error'] = __('please_fill_all_required_fields');
    header("Location: create.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Username isiwe duplicated
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT user_id
FROM users
WHERE username=?
AND clan_id = ?
LIMIT 1
");

$stmt->execute([$username, $clanId]);

if ($stmt->fetch()) {

    $_SESSION['error'] = __('username_already_exists');

    header("Location: create.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Hakikisha account haijawahi kutengenezwa
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT username
FROM users
WHERE user_id=?
AND clan_id = ?
LIMIT 1
");

$stmt->execute([$user_id, $clanId]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {

    $_SESSION['error'] = __('member_not_found');

    header("Location: create.php");
    exit;
}

if (!empty($user['username'])) {

    $_SESSION['error'] = __('this_member_already_has_account');

    header("Location: create.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Create Account
|--------------------------------------------------------------------------
*/

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("
UPDATE users
SET
username=?,
password_hash=?,
role_id=?,
account_status='active'
WHERE user_id=?
AND clan_id=?
");

$stmt->execute([
    $username,
    $password_hash,
    $role_id,
    $user_id,
    $clanId
]);

$_SESSION['success'] = __('user_account_created_successfully');

header("Location: index.php");
exit;