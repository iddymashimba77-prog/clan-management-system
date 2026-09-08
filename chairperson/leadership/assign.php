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

$stmt = $pdo->prepare("
SELECT *
FROM users
WHERE user_id=?
AND clan_id = ?
LIMIT 1
");

$stmt->execute([$user_id, $clanId]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$user){
    die(__('member_not_found'));
}

$roles = $pdo->query("
SELECT role_id,role_name
FROM roles
WHERE role_name != 'Administrator'
ORDER BY role_name
")->fetchAll(PDO::FETCH_ASSOC);

define('USER_ROLE','Chairperson');

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

<?= __('assign_leadership_role') ?>

</h3>

</div>

<div class="card-body">

<form action="save.php" method="POST">

<input
type="hidden"
name="user_id"
value="<?= $user['user_id'] ?>">

<div class="form-group">

<label><?= __('member') ?></label>

<input
type="text"
class="form-control"
readonly
value="<?= htmlspecialchars(
trim($user['first_name'].' '.$user['middle_name'].' '.$user['last_name'])
) ?>">

</div>

<div class="form-group">

<label><?= __('role') ?></label>

<select
name="role_id"
class="form-control"
required>

<?php foreach($roles as $role): ?>

<option
value="<?= $role['role_id'] ?>"
<?= $role['role_id']==$user['role_id']?'selected':'' ?>>

<?= htmlspecialchars($role['role_name']) ?>

</option>

<?php endforeach; ?>

</select>

</div>

<hr>

<div class="form-group">

<label><?= __('username') ?></label>

<input
type="text"
name="username"
class="form-control"
value="<?= htmlspecialchars($user['username']) ?>">

</div>

<div class="form-group">

<label><?= __('new_password_leave_empty') ?></label>

<input
type="password"
name="password"
class="form-control">

</div>

<button
type="submit"
class="btn btn-success">

<i class="fas fa-save"></i>

<?= __('save_changes') ?>

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