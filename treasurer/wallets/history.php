<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireTreasurer();

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

fwp.secretary_status,

fwp.treasurer_status,

fwp.chairperson_status,

fwp.payment_status

FROM family_wallet_payments fwp

INNER JOIN users u
ON fwp.member_user_id=u.user_id

INNER JOIN family_wallets fw
ON fwp.wallet_id=fw.wallet_id

ORDER BY fwp.payment_date DESC
");

$payments=$stmt->fetchAll(PDO::FETCH_ASSOC);

define('USER_ROLE','Treasurer');

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

<?= __('wallet_payment_history') ?>

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

<th><?= __('secretary') ?></th>

<th><?= __('treasurer') ?></th>

<th><?= __('chairperson') ?></th>

<th><?= __('final_status') ?></th>

</tr>

</thead>

<tbody>

<?php foreach($payments as $row): ?>

<tr>

<td><?= $row['member_code'] ?></td>

<td><?= htmlspecialchars($row['member_name']) ?></td>

<td><?= htmlspecialchars($row['wallet_name']) ?></td>

<td>TZS <?= number_format($row['amount'],2) ?></td>

<td><?= date('F Y',strtotime($row['payment_month'])) ?></td>

<td><?= __(strtolower($row['secretary_status'])) ?></td>

<td><?= __(strtolower($row['treasurer_status'])) ?></td>

<td><?= __(strtolower($row['chairperson_status'])) ?></td>

<td>

<?php

if($row['payment_status']=='approved'){

echo '<span class="badge badge-success">' . __('approved') . '</span>';

}elseif($row['payment_status']=='rejected'){

echo '<span class="badge badge-danger">' . __('rejected') . '</span>';

}else{

echo '<span class="badge badge-warning">' . __('pending') . '</span>';

}

?>

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