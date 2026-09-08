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

$stmt = $pdo->prepare("
SELECT
u.user_id,
u.member_code,
CONCAT(u.first_name,' ',u.middle_name,' ',u.last_name) AS full_name,
u.username,
r.role_name
FROM users u
LEFT JOIN roles r
ON u.role_id=r.role_id
WHERE u.is_deleted=0
AND u.clan_id = ?
ORDER BY full_name
");
$stmt->execute([$clanId]);

$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

<?= __('leadership_management') ?>

</h3>

</div>

<div class="card-body">

<table class="table table-bordered table-striped">

<thead>

<tr>

<th><?= __('member_code') ?></th>
<th><?= __('full_name') ?></th>
<th><?= __('username') ?></th>
<th><?= __('current_role') ?></th>
<th><?= __('action') ?></th>

</tr>

</thead>

<tbody>

<?php foreach($members as $member): ?>

<tr>

<td><?= $member['member_code'] ?></td>

<td><?= htmlspecialchars($member['full_name']) ?></td>

<td><?= htmlspecialchars($member['username']) ?></td>

<td><?= htmlspecialchars($member['role_name']) ?></td>

<td>

<a href="assign.php?id=<?= $member['user_id'] ?>"
class="btn btn-primary btn-sm">

<?= __('assign_role') ?>

</a>

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