<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireTreasurer();

$database=new Database();
$pdo=$database->connect();

$payment_id=(int)($_GET['id'] ?? 0);

$stmt=$pdo->prepare("
SELECT

fwp.*,

fw.wallet_name,

u.member_code,

CONCAT(u.first_name,' ',u.last_name) AS member_name

FROM family_wallet_payments fwp

INNER JOIN users u
ON fwp.member_user_id=u.user_id

INNER JOIN family_wallets fw
ON fwp.wallet_id=fw.wallet_id

WHERE fwp.payment_id=?
LIMIT 1
");

$stmt->execute([$payment_id]);

$payment=$stmt->fetch(PDO::FETCH_ASSOC);

if(!$payment){
die(__('payment_not_found'));
}

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

<h3>

<?= __('wallet_payment_review') ?>

</h3>

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>
<th><?= __('wallet_label') ?></th>
<td><?= htmlspecialchars($payment['wallet_name']) ?></td>
</tr>

<tr>
<th><?= __('member') ?></th>
<td><?= htmlspecialchars($payment['member_name']) ?></td>
</tr>

<tr>
<th><?= __('member_code') ?></th>
<td><?= $payment['member_code'] ?></td>
</tr>

<tr>
<th><?= __('amount') ?></th>
<td>TZS <?= number_format($payment['amount'],2) ?></td>
</tr>

<tr>
<th><?= __('payment_month') ?></th>
<td><?= date('F Y',strtotime($payment['payment_month'])) ?></td>
</tr>

<tr>
<th><?= __('reference_number') ?></th>
<td><?= htmlspecialchars($payment['reference_number']) ?></td>
</tr>

</table>

<hr>

<?php

$ext=strtolower(pathinfo($payment['payment_proof'],PATHINFO_EXTENSION));

if(in_array($ext,['jpg','jpeg','png'])):

?>

<img
src="<?= BASE_URL ?>uploads/payment_proofs/<?= $payment['payment_proof'] ?>"
class="img-fluid img-thumbnail"
style="max-width:500px;">

<?php else: ?>

<a
href="<?= BASE_URL ?>uploads/payment_proofs/<?= $payment['payment_proof'] ?>"
target="_blank"
class="btn btn-danger">

<?= __('open_pdf') ?>

</a>

<?php endif; ?>

<hr>

<a
href="approve.php?id=<?= $payment['payment_id'] ?>"
class="btn btn-success">

<?= __('approve') ?>

</a>

<a
href="reject.php?id=<?= $payment['payment_id'] ?>"
class="btn btn-danger">

<?= __('reject') ?>

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
?>