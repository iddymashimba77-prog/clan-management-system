<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    die(__('payment_id') . ' is missing.');
}

$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->prepare("
    SELECT

        cp.*,

        c.title AS contribution_title,
        c.description AS contribution_description,

        u.member_code,
        u.first_name,
        u.middle_name,
        u.last_name,
        u.phone,
        u.email,

        pm.method_name

    FROM contribution_payments cp

    INNER JOIN contributions c
        ON c.contribution_id = cp.contribution_id

    INNER JOIN users u
        ON u.user_id = cp.member_user_id

    LEFT JOIN payment_methods pm
        ON pm.payment_method_id = cp.payment_method_id

    WHERE cp.payment_id = ?

    LIMIT 1
");

$stmt->execute([$id]);

$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    die('Payment not found.');
}

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/admin.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">

<div class="mb-3">

<a
href="payments.php"
class="btn btn-secondary">

<i class="fas fa-arrow-left mr-1"></i>
Back to Payments

</a>

<?php if ($payment['payment_status'] === 'pending'): ?>

<a
href="approve_payment.php?id=<?= $id; ?>"
class="btn btn-success"
onclick="return confirm('Approve this payment?')">

<i class="fas fa-check mr-1"></i>
Approve

</a>

<a
href="reject_payment.php?id=<?= $id; ?>"
class="btn btn-danger">

<i class="fas fa-times mr-1"></i>
Reject

</a>

<?php endif; ?>

</div>


<div class="row">

<div class="col-md-8">

<div class="card clan-page-card">

<div class="card-header">

<h3 class="card-title">

<i class="fas fa-receipt mr-2 text-primary"></i>

<?= __('payment_details') ?>

</h3>

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>
<th width="35%"><?= __('payment_id') ?></th>
<td><?= $id; ?></td>
</tr>

<tr>
<th><?= __('contribution') ?></th>
<td>
<?= htmlspecialchars($payment['contribution_title']); ?>
</td>
</tr>

<tr>
<th><?= __('member') ?></th>
<td>

<?= htmlspecialchars(
trim(
$payment['first_name'] . ' ' .
$payment['middle_name'] . ' ' .
$payment['last_name']
)
); ?>

<br>

<small>
<?= htmlspecialchars($payment['member_code']); ?>
</small>

</td>
</tr>

<tr>
<th><?= __('phone') ?></th>
<td>
<?= htmlspecialchars($payment['phone'] ?? 'N/A'); ?>
</td>
</tr>

<tr>
<th><?= __('email') ?></th>
<td>
<?= htmlspecialchars($payment['email'] ?? 'N/A'); ?>
</td>
</tr>

<tr>
<th><?= __('amount') ?></th>
<td>

<strong>
<?= number_format((float)$payment['amount'], 2); ?>
</strong>

</td>
</tr>

<tr>
<th><?= __('payment_method_label2') ?></th>
<td>
<?= htmlspecialchars($payment['method_name'] ?? 'N/A'); ?>
</td>
</tr>

<tr>
<th>Reference Number</th>
<td>
<?= htmlspecialchars($payment['reference_number'] ?? 'N/A'); ?>
</td>
</tr>

<tr>
<th><?= __('payment_status') ?></th>

<td>

<?php if ($payment['payment_status'] === 'approved'): ?>

<span class="badge badge-success">
Approved
</span>

<?php elseif ($payment['payment_status'] === 'rejected'): ?>

<span class="badge badge-danger">
Rejected
</span>

<?php else: ?>

<span class="badge badge-warning">
Pending
</span>

<?php endif; ?>

</td>

</tr>

<tr>
<th><?= __('secretary_status') ?></th>
<td>
<?= htmlspecialchars($payment['secretary_status']); ?>
</td>
</tr>

<tr>
<th><?= __('treasurer_status') ?></th>
<td>
<?= htmlspecialchars($payment['treasurer_status']); ?>
</td>
</tr>

<tr>
<th><?= __('chairperson_status') ?></th>
<td>
<?= htmlspecialchars($payment['chairperson_status']); ?>
</td>
</tr>

<tr>
<th>Payment Date</th>
<td>
<?= htmlspecialchars($payment['payment_date'] ?? 'N/A'); ?>
</td>
</tr>

<tr>
<th><?= __('remarks') ?></th>
<td>
<?= nl2br(
htmlspecialchars($payment['remarks'] ?? '')
); ?>
</td>
</tr>

</table>

</div>

</div>

</div>


<div class="col-md-4">

<div class="card clan-page-card">

<div class="card-header">

<h3 class="card-title">
Payment Proof
</h3>

</div>

<div class="card-body text-center">

<?php if (!empty($payment['payment_proof'])): ?>

<a
href="../../uploads/payment_proofs/<?= htmlspecialchars($payment['payment_proof']); ?>"
target="_blank">

<img
src="../../uploads/payment_proofs/<?= htmlspecialchars($payment['payment_proof']); ?>"
class="img-fluid rounded"
style="max-height:500px;"
alt="Payment Proof">

</a>

<?php elseif (!empty($payment['receipt'])): ?>

<a
href="../../assets/uploads/contribution_receipts/<?= htmlspecialchars($payment['receipt']); ?>"
target="_blank">

<img
src="../../assets/uploads/contribution_receipts/<?= htmlspecialchars($payment['receipt']); ?>"
class="img-fluid rounded"
style="max-height:500px;"
alt="Receipt">

</a>

<?php else: ?>

<div class="text-muted py-5">

<i class="fas fa-file-image fa-3x mb-3"></i>

<p>
No payment proof uploaded.
</p>

</div>

<?php endif; ?>

</div>

</div>

</div>

</div>

</div>

</section>

</div>

<?php

require_once '../../includes/dashboard/footer.php';

?>