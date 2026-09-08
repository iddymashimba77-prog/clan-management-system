<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireClanUser();

$database = new Database();
$pdo = $database->connect();
$clanId = getCurrentClanId();

$user_id=$_SESSION['user_id'];

$stmt=$pdo->prepare("
SELECT

cp.payment_id,
e.title AS event_name,
ec.title,
cp.amount,
cp.payment_status,
cp.payment_date

FROM contribution_payments cp

INNER JOIN event_contributions ec
ON cp.contribution_id=ec.contribution_id

INNER JOIN events e
ON ec.event_id=e.event_id

WHERE cp.member_user_id=?
AND cp.clan_id=?

ORDER BY cp.payment_date DESC
");

$stmt->execute([$user_id, $clanId]);

$payments=$stmt->fetchAll(PDO::FETCH_ASSOC);

define('USER_ROLE','Treasurer');

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebar.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content" style="max-width: 1200px; margin: 0 auto;">

<div class="card">

<div class="card-header">

<h3 class="card-title">

<?= __('contribution_history') ?>

</h3>

</div>

<div class="card-body">

<?php if(isset($_GET['success'])): ?>

<div class="alert alert-success">

<?= __('payment_submitted_successfully') ?>

</div>

<?php endif; ?>

<table class="table table-bordered table-striped">

<thead>

<tr>

<th><?= __('event') ?></th>

<th><?= __('contribution') ?></th>

<th><?= __('amount') ?></th>

<th><?= __('status') ?></th>

<th><?= __('date') ?></th>

<th><?= __('receipt') ?></th>

</tr>

</thead>

<tbody>

<?php foreach($payments as $row): ?>

<tr>

<td><?= htmlspecialchars($row['event_name']) ?></td>

<td><?= htmlspecialchars($row['title']) ?></td>

<td>TZS <?= number_format($row['amount'],2) ?></td>

<td>

<?php

$color='warning';

if($row['payment_status']=='approved'){
    $color='success';
}

if($row['payment_status']=='rejected'){
    $color='danger';
}

?>

<span class="badge badge-<?= $color ?>">

<?= __($row['payment_status']) ?>

</span>

</td>

<td><?= $row['payment_date'] ?></td>

<td>

<a
href="receipt.php?id=<?= $row['payment_id'] ?>"
target="_blank"
class="btn btn-info btn-sm">

<?= __('receipt') ?>

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

</div>

<?php
require_once '../../includes/dashboard/footer.php';