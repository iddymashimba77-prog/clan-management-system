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

/*
|--------------------------------------------------------------------------
| Members ambao hawana username
|--------------------------------------------------------------------------
*/


$stmt = $pdo->prepare("
SELECT
user_id,
member_code,
first_name,
middle_name,
last_name,
username
FROM users
WHERE
is_deleted=0
AND account_status='active'
AND clan_id = ?
ORDER BY first_name
");
$stmt->execute([$clanId]);

$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

$roles = $pdo->query("
SELECT role_id, role_name
FROM roles
WHERE role_name IN ('Member','Secretary','Treasurer','Chairperson')
ORDER BY role_name
")->fetchAll(PDO::FETCH_ASSOC);

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebar.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid" style="max-width: 1200px; margin: 0 auto;">

<div class="card">

<div class="card-header">

<h3 class="card-title">

<?= __('create_user_account') ?>

</h3>

</div>

<div class="card-body">

<form action="store.php" method="POST">

<div class="form-group">

<label><?= __('family_member_label') ?></label>

<select name="user_id" class="form-control" required>

<option value=""><?= __('select_member') ?></option>

<?php foreach($members as $member): ?>

<?php
$hasAccount = !empty($member['username']);
$memberLabel =
    $member['member_code']." - ".
    trim(
        $member['first_name']." ".
        $member['middle_name']." ".
        $member['last_name']
    ) .
    ($hasAccount ? " (" . __('member_has_account') . ")" : '');
?>

<option
    value="<?= $member['user_id'] ?>"
    <?= $hasAccount ? 'disabled' : '' ?>>

<?= htmlspecialchars($memberLabel) ?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="form-group">

<label><?= __('username') ?></label>

<input
type="text"
name="username"
class="form-control"
required>

</div>

<div class="form-group">

<label><?= __('password') ?></label>

<input
type="password"
name="password"
class="form-control"
required>

</div>

<div class="form-group">

<label><?= __('role') ?></label>

<select
name="role_id"
class="form-control"
required>

<?php foreach($roles as $role): ?>

<option value="<?= $role['role_id'] ?>">

<?= htmlspecialchars($role['role_name']) ?>

</option>

<?php endforeach; ?>

</select>

</div>

<button
type="submit"
class="btn btn-success">

<?= __('create_account') ?>

</button>

<a
href="index.php"
class="btn btn-secondary">

<?= __('cancel') ?>

</a>

</form>

</div>

</div>

</div>

</section>

<?php
require_once '../../includes/dashboard/footer.php';
?>