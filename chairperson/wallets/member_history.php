<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database=new Database();
$pdo=$database->connect();

$wallet_id=(int)$_GET['wallet'];
$member_id=(int)$_GET['member'];

$stmt=$pdo->prepare("
SELECT

u.member_code,
CONCAT(u.first_name,' ',u.last_name) AS member_name,
fw.wallet_name

FROM users u

INNER JOIN family_wallets fw

WHERE u.user_id=?
AND fw.wallet_id=?

LIMIT 1
");

$stmt->execute([$member_id,$wallet_id]);

$info=$stmt->fetch(PDO::FETCH_ASSOC);

$stmt=$pdo->prepare("
SELECT *

FROM family_wallet_payments

WHERE wallet_id=?
AND member_user_id=?

ORDER BY payment_month DESC
");

$stmt->execute([$wallet_id,$member_id]);

$payments=$stmt->fetchAll(PDO::FETCH_ASSOC);

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

<?= htmlspecialchars($info['member_name']) ?>

</h3>

</div>

<div class="card-body">

<p>

<?= __('wallet') ?> :

<b><?= htmlspecialchars($info['wallet_name']) ?></b>

</p>

<table class="table table-bordered">

<thead>

<tr>

<th><?= __('month') ?></th>

<th><?= __('amount') ?></th>

<th><?= __('status') ?></th>

<th><?= __('date') ?></th>

</tr>

</thead>

<tbody>

<?php foreach($payments as $row): ?>

<tr>

<td><?= date('F Y',strtotime($row['payment_month'])) ?></td>

<td><?= number_format($row['amount'],2) ?></td>

<td><?= $row['payment_status'] ?></td>

<td><?= $row['payment_date'] ?></td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

<a
href="members.php?id=<?= $wallet_id ?>"
class="btn btn-secondary">

<?= __('back') ?>

</a>

</div>

</div>

</div>

</section>

<?php
require_once '../../includes/dashboard/footer.php';