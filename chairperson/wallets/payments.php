<?php


require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database = new Database();
$pdo = $database->connect();

$payment_id = (int)($_GET['id'] ?? 0);

if ($payment_id <= 0) {
    die(__('invalid_payment_id'));
}

$stmt = $pdo->prepare("
    SELECT
        fwp.*,

        fw.wallet_name,
        fw.monthly_amount,

        CONCAT(
            u.first_name,
            ' ',
            u.last_name
        ) AS member_name,

        u.phone

    FROM family_wallet_payments fwp

    INNER JOIN family_wallets fw
        ON fwp.wallet_id = fw.wallet_id

    INNER JOIN users u
        ON fwp.member_user_id = u.user_id

    WHERE fwp.payment_id = ?

    LIMIT 1
");

$stmt->execute([$payment_id]);

$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    die(__('payment_not_found'));
}

define('USER_ROLE', 'Chairperson');

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/chairperson.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid" style="max-width: 1200px; margin: 0 auto;">

<div class="card">

<div class="card-header">

<h3 class="card-title">
<?= __('wallet_payment_review') ?>
</h3>

</div>

<div class="card-body">

<div class="row">

<div class="col-md-6">

<table class="table table-bordered">

<tr>
<th><?= __('member') ?></th>
<td>
<?= htmlspecialchars($payment['member_name']) ?>
</td>
</tr>

<tr>
<th><?= __('wallet') ?></th>
<td>
<?= htmlspecialchars($payment['wallet_name']) ?>
</td>
</tr>

<tr>
<th><?= __('amount') ?></th>
<td>
TZS <?= number_format($payment['amount'], 2) ?>
</td>
</tr>

<tr>
<th><?= __('payment_month') ?></th>
<td>
<?= date(
    'F Y',
    strtotime($payment['payment_month'])
) ?>
</td>
</tr>

<tr>
<th><?= __('reference_number') ?></th>
<td>
<?= htmlspecialchars(
    $payment['reference_number']
) ?>
</td>
</tr>

<tr>
<th><?= __('secretary_status') ?></th>
<td>
<span class="badge badge-info">
<?= htmlspecialchars(
    $payment['secretary_status']
) ?>
</span>
</td>
</tr>

<tr>
<th><?= __('treasurer_status') ?></th>
<td>
<span class="badge badge-info">
<?= htmlspecialchars(
    $payment['treasurer_status']
) ?>
</span>
</td>
</tr>

<tr>
<th><?= __('chairperson_status') ?></th>
<td>
<span class="badge badge-warning">
<?= htmlspecialchars(
    $payment['chairperson_status']
) ?>
</span>
</td>
</tr>

</table>

</div>


<div class="col-md-6">

<h5><?= __('payment_proof') ?></h5>

<?php if (!empty($payment['payment_proof'])): ?>

<?php
$proofPath =
    BASE_URL .
    'uploads/payment_proofs/' .
    rawurlencode(
        $payment['payment_proof']
    );
?>

<?php
$extension = strtolower(
    pathinfo(
        $payment['payment_proof'],
        PATHINFO_EXTENSION
    )
);
?>

<?php if (
    in_array(
        $extension,
        ['jpg','jpeg','png']
    )
): ?>

<img
src="<?= $proofPath ?>"
class="img-fluid img-thumbnail"
alt="<?= __('payment_proof') ?>"
>

<?php elseif ($extension === 'pdf'): ?>

<a
href="<?= $proofPath ?>"
target="_blank"
class="btn btn-primary"
>

<i class="fas fa-file-pdf"></i>

<?= __('view_payment_proof') ?>

</a>

<?php endif; ?>

<?php else: ?>

<p class="text-muted">
<?= __('no_payment_proof_uploaded') ?>
</p>

<?php endif; ?>

</div>

</div>

<hr>

<?php if (
    $payment['chairperson_status'] === 'Pending'
): ?>

<div class="d-flex">

<form
action="approve.php"
method="POST"
class="mr-2"
>

<input
type="hidden"
name="payment_id"
value="<?= $payment['payment_id'] ?>"
>

<button
type="submit"
class="btn btn-success"
>

<i class="fas fa-check"></i>

<?= __('approve_payment') ?>

</button>

</form>


<form
action="reject.php"
method="POST"
>

<input
type="hidden"
name="payment_id"
value="<?= $payment['payment_id'] ?>"
>

<button
type="submit"
class="btn btn-danger"
>

<i class="fas fa-times"></i>

<?= __('reject_payment') ?>

</button>

</form>

</div>

<?php else: ?>

<div class="alert alert-info">

<?= __('payment_already_processed') ?>

</div>

<?php endif; ?>

</div>

</div>

</div>

</section>

<?php

require_once '../../includes/dashboard/footer.php';

?>