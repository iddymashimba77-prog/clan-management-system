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

$user_id=(int)($_GET['id'] ?? 0);

$stmt=$pdo->prepare("
SELECT
u.*,
u.role_id,
r.role_name
FROM users u
LEFT JOIN roles r
ON u.role_id=r.role_id
WHERE u.user_id=?
AND u.clan_id = ?
LIMIT 1
");

$stmt->execute([$user_id, $clanId]);

$user=$stmt->fetch(PDO::FETCH_ASSOC);

if(!$user){
    die(__('member_not_found'));
}

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

<h3><?= __('member_profile') ?></h3>

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>
<th><?= __('member_code') ?></th>
<td><?= $user['member_code'] ?></td>
</tr>

<tr>
<th><?= __('full_name') ?></th>
<td><?= htmlspecialchars($user['first_name'].' '.$user['middle_name'].' '.$user['last_name']) ?></td>
</tr>

<tr>
<th><?= __('gender') ?></th>
<td><?= $user['gender'] ?></td>
</tr>

<tr>
<th><?= __('phone') ?></th>
<td><?= $user['phone'] ?></td>
</tr>

<tr>
<th><?= __('email') ?></th>
<td><?= $user['email'] ?></td>
</tr>

<tr>
<th><?= __('username') ?></th>
<td><?= $user['username'] ?></td>
</tr>

<tr>
<th><?= __('role') ?></th>
<td><?= $user['role_name'] ?></td>
</tr>

</table>

<?php if($user['role_id'] == 5): ?>

<button
class="btn btn-success"
onclick="alert('<?= __('cannot_edit_administrator') ?>')"
disabled>

<?= __('edit') ?>

</button>

<?php else: ?>

<a href="edit.php?id=<?= $user['user_id'] ?>" class="btn btn-success">

<?= __('edit') ?>

</a>

<?php endif; ?>

<a href="index.php" class="btn btn-secondary">

<?= __('back') ?>

</a>

</div>

</div>

</div>

</section>

<?php
require_once '../../includes/dashboard/footer.php';