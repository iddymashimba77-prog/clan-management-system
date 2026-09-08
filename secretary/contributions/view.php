<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireSecretary();

$database = new Database();
$pdo = $database->connect();

$payment_id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
SELECT

cp.*,

u.member_code,
CONCAT(u.first_name,' ',u.last_name) AS member_name,

e.title AS event_name,
ec.title AS contribution_title,

pm.method_name

FROM contribution_payments cp

INNER JOIN users u
ON cp.member_user_id=u.user_id

INNER JOIN payment_methods pm
ON cp.payment_method_id=pm.payment_method_id

INNER JOIN event_contributions ec
ON cp.contribution_id=ec.contribution_id

INNER JOIN events e
ON ec.event_id=e.event_id

WHERE cp.payment_id=?
LIMIT 1
");

$stmt->execute([$payment_id]);

$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$payment){
    die(__('payment_not_found'));
}

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

<h3><?= __('contribution_details') ?></h3>

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>
<th><?= __('member') ?></th>
<td><?= htmlspecialchars($payment['member_name']) ?></td>
</tr>

<tr>
<th><?= __('member_code') ?></th>
<td><?= $payment['member_code'] ?></td>
</tr>

<tr>
<th><?= __('event') ?></th>
<td><?= htmlspecialchars($payment['event_name']) ?></td>
</tr>

<tr>
<th><?= __('contribution') ?></th>
<td><?= htmlspecialchars($payment['contribution_title']) ?></td>
</tr>

<tr>
<th><?= __('amount') ?></th>
<td>TZS <?= number_format($payment['amount'],2) ?></td>
</tr>

<tr>
<th><?= __('payment_method') ?></th>
<td><?= htmlspecialchars($payment['method_name']) ?></td>
</tr>

<tr>
<th><?= __('reference_number') ?></th>
<td><?= htmlspecialchars($payment['reference_number']) ?></td>
</tr>

</table>

<h5><?= __('payment_proof') ?></h5>

<?php

$ext = strtolower(pathinfo($payment['payment_proof'], PATHINFO_EXTENSION));

if(in_array($ext,['jpg','jpeg','png'])):

?>

<img
src="<?= BASE_URL ?>uploads/payment_proofs/<?= $payment['payment_proof'] ?>"
class="img-fluid img-thumbnail">

<?php elseif($ext=='pdf'): ?>

<a
target="_blank"
href="<?= BASE_URL ?>uploads/payment_proofs/<?= $payment['payment_proof'] ?>"
class="btn btn-danger">

<?= __('open_pdf') ?>

</a>

<?php endif; ?>

<hr>

<?php if($payment['payment_status'] == 'pending' && $payment['secretary_status'] == 'Pending'): ?>

<a
href="forward.php?id=<?= $payment['payment_id'] ?>"
class="btn btn-success">

<i class="fas fa-share"></i>
<?= __('forward_to_treasurer') ?>

</a>

<?php endif; ?>

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