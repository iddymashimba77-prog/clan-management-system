<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireSecretary();

$database=new Database();
$pdo=$database->connect();

$stmt=$pdo->query("
SELECT

fwp.payment_id,

fw.wallet_name,

u.member_code,

CONCAT(u.first_name,' ',u.last_name) AS member_name,

fwp.amount,

fwp.payment_month,

fwp.payment_date,

fwp.reference_number

FROM family_wallet_payments fwp

INNER JOIN users u
ON fwp.member_user_id=u.user_id

INNER JOIN family_wallets fw
ON fwp.wallet_id=fw.wallet_id

WHERE

fwp.payment_status='pending'

AND fwp.secretary_status='Pending'

ORDER BY fwp.payment_date DESC
");

$payments=$stmt->fetchAll(PDO::FETCH_ASSOC);

define('USER_ROLE','Secretary');

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

<?= __('wallet_payments') ?>

</h3>

</div>

<div class="card-body">

<table class="table table-bordered table-striped">

<thead>

<tr>

<th><?= __('member_code') ?></th>

<th><?= __('member') ?></th>

<th><?= __('wallet') ?></th>

<th><?= __('amount') ?></th>

<th><?= __('month') ?></th>

<th><?= __('date') ?></th>

<th><?= __('action') ?></th>

</tr>

</thead>

<tbody>

<?php foreach($payments as $row): ?>

<tr>

<td><?= $row['member_code'] ?></td>

<td><?= htmlspecialchars($row['member_name']) ?></td>

<td><?= htmlspecialchars($row['wallet_name']) ?></td>

<td>

TZS <?= number_format($row['amount'],2) ?>

</td>

<td>

<?= date('F Y',strtotime($row['payment_month'])) ?>

</td>

<td>

<?= $row['payment_date'] ?>

</td>

<td>

<a
href="view.php?id=<?= $row['payment_id'] ?>"
class="btn btn-primary btn-sm">

<?= __('view') ?>

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
?>