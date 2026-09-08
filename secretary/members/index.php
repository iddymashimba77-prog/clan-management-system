<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireSecretary();

$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->query("
SELECT

u.user_id,
u.member_code,
u.first_name,
u.middle_name,
u.last_name,
u.username,
u.account_status,
r.role_name

FROM users u

LEFT JOIN roles r
ON u.role_id = r.role_id

WHERE u.is_deleted=0

ORDER BY u.first_name ASC
");

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

<?= __('user_management') ?>

</h3>


</div>

<div class="card-body">

<?php if(isset($_SESSION['success'])): ?>

<div class="alert alert-success">

<?= $_SESSION['success']; unset($_SESSION['success']); ?>

</div>

<?php endif; ?>

<?php if(isset($_SESSION['error'])): ?>

<div class="alert alert-danger">

<?= $_SESSION['error']; unset($_SESSION['error']); ?>

</div>

<?php endif; ?>

<?php if(isset($_GET['updated'])): ?>

<div class="alert alert-success">

<?= __('user_account_updated_successfully') ?>

</div>

<?php endif; ?>

<table class="table table-bordered table-striped">

<thead>

<tr>

<th>#</th>

<th><?= __('member_code') ?></th>

<th><?= __('full_name') ?></th>

<th><?= __('role') ?></th>

<th><?= __('username') ?></th>

<th><?= __('status') ?></th>

</tr>

</thead>

<tbody>

<?php foreach($users as $i=>$user): ?>

<tr>

<td><?= $i+1 ?></td>

<td><?= htmlspecialchars($user['member_code']) ?></td>

<td>

<?= htmlspecialchars(

trim(
$user['first_name'].' '.
$user['middle_name'].' '.
$user['last_name']
)

) ?>

</td>

<td>

<span class="badge badge-info">

<?= htmlspecialchars($user['role_name']) ?>

</span>

</td>

<td>

<?= htmlspecialchars($user['username']) ?>

</td>

<td>

<?php if($user['account_status']=='active'): ?>

<span class="badge badge-success">

<?= __('active') ?>

</span>

<?php else: ?>

<span class="badge badge-danger">

<?= __('inactive') ?>

</span>

<?php endif; ?>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

</div>

</section>

<?php

require_once '../../includes/dashboard/footer.php';

?>