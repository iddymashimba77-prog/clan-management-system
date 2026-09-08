<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database=new Database();
$pdo=$database->connect();

$wallet_id=(int)($_GET['id'] ?? 0);

$stmt=$pdo->prepare("
SELECT *
FROM family_wallets
WHERE wallet_id=?
");

$stmt->execute([$wallet_id]);

$wallet=$stmt->fetch(PDO::FETCH_ASSOC);

$stmt=$pdo->prepare("
SELECT COUNT(*)
FROM family_wallet_members
WHERE wallet_id=?
");

$stmt->execute([$wallet_id]);

$totalMembers=$stmt->fetchColumn();

$stmt=$pdo->prepare("
SELECT COALESCE(SUM(amount),0)
FROM family_wallet_payments
WHERE wallet_id=?
AND payment_status='approved'
");

$stmt->execute([$wallet_id]);

$totalCollected=$stmt->fetchColumn();

$expected=$wallet['monthly_amount']*$totalMembers*12;

$progress=0;

if($expected>0){

$progress=($totalCollected/$expected)*100;

}

define('USER_ROLE','Chairperson');

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/chairperson.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid" style="max-width: 1200px; margin: 0 auto;">

<div class="card">

<div class="card-header">

<h3>

<?= htmlspecialchars($wallet['wallet_name']) ?>

</h3>

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>

<th><?= __('goal') ?></th>

<td><?= htmlspecialchars($wallet['goal']) ?></td>

</tr>

<tr>

<th><?= __('monthly_amount') ?></th>

<td><?= number_format($wallet['monthly_amount'],2) ?></td>

</tr>

<tr>

<th><?= __('members') ?></th>

<td><?= $totalMembers ?></td>

</tr>

<tr>

<th><?= __('expected_per_year') ?></th>

<td><?= number_format($expected,2) ?></td>

</tr>

<tr>

<th><?= __('collected') ?></th>

<td><?= number_format($totalCollected,2) ?></td>

</tr>

<tr>

<th><?= __('remaining') ?></th>

<td><?= number_format($expected-$totalCollected,2) ?></td>

</tr>

</table>

<div class="progress mt-3">

<div
class="progress-bar bg-success"
style="width:<?= min($progress,100) ?>%">

<?= number_format($progress,1) ?>%

</div>

</div>

<hr>

<a
href="members.php?id=<?= $wallet_id ?>"
class="btn btn-primary">

<?= __('members') ?>

</a>

<a
href="payments.php?id=<?= $wallet_id ?>"
class="btn btn-success">

<?= __('payments') ?>

</a>

<a
href="generate_month.php?id=<?= $wallet['wallet_id'] ?>"
class="btn btn-info"
onclick="return confirm('<?= __('generate_month_dues_confirm') ?>')">

<?= __('generate_monthly_dues') ?>

</a>

<a
href="edit.php?id=<?= $wallet_id ?>"
class="btn btn-warning">

<?= __('edit') ?>

</a>

<a
href="index.php"
class="btn btn-secondary">

<?= __('back') ?>

</a>

</div>

</div>

</div>

</section>

<?php
require_once '../../includes/dashboard/footer.php';