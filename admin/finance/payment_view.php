<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    die('Invalid payment ID.');
}

$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->prepare("
    SELECT
        cp.*,
        c.title AS contribution_title,
        u.member_code,
        CONCAT(
            u.first_name, ' ',
            COALESCE(u.middle_name, ''), ' ',
            u.last_name
        ) AS member_name,
        pm.method_name
    FROM contribution_payments cp
    INNER JOIN contributions c
        ON c.contribution_id = cp.contribution_id
    INNER JOIN users u
        ON u.user_id = cp.member_user_id
    LEFT JOIN contribution_payment_methods pm
        ON pm.method_id = cp.payment_method_id
    WHERE cp.payment_id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    die('Payment not found.');
}

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebar.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">

<div class="card clan-page-card">

<div class="card-header">

<h3 class="card-title">
<i class="fas fa-receipt mr-2"></i>
<?= __('payment_details') ?>
</h3>

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>
<th><?= __('payment_id') ?></th>
<td><?= (int)$payment['payment_id']; ?></td>
</tr>

<tr>
<th><?= __('member') ?></th>
<td>
<?= htmlspecialchars(
    $payment['member_code'].' - '.$payment['member_name']
); ?>
</td>
</tr>

<tr>
<th><?= __('contribution') ?></th>
<td><?= htmlspecialchars($payment['contribution_title']); ?></td>
</tr>

<tr>
<th><?= __('amount') ?></th>
<td><?= number_format($payment['amount'], 2); ?></td>
</tr>

<tr>
<th><?= __('payment_method_label2') ?></th>
<td><?= htmlspecialchars($payment['method_name'] ?? 'Unknown'); ?></td>
</tr>

<tr>
<th><?= __('reference') ?></th>
<td><?= htmlspecialchars($payment['reference_number'] ?? ''); ?></td>
</tr>

<tr>
<th><?= __('payment_status') ?></th>
<td><?= htmlspecialchars($payment['payment_status']); ?></td>
</tr>

<tr>
<th><?= __('secretary_status') ?></th>
<td><?= htmlspecialchars($payment['secretary_status']); ?></td>
</tr>

<tr>
<th><?= __('treasurer_status') ?></th>
<td><?= htmlspecialchars($payment['treasurer_status']); ?></td>
</tr>

<tr>
<th><?= __('chairperson_status') ?></th>
<td><?= htmlspecialchars($payment['chairperson_status']); ?></td>
</tr>

<tr>
<th><?= __('remarks') ?></th>
<td><?= nl2br(htmlspecialchars($payment['remarks'] ?? '')); ?></td>
</tr>

<tr>
<th><?= __('submitted_at') ?></th>
<td><?= htmlspecialchars($payment['submitted_at']); ?></td>
</tr>

</table>

<a href="approve_payment.php?id=<?= $id ?>"
   class="btn btn-success">
<i class="fas fa-check"></i>
<?= __('approve') ?>
</a>

<a href="reject_payment.php?id=<?= $id ?>"
   class="btn btn-danger">
<i class="fas fa-times"></i>
<?= __('reject') ?>
</a>

<a href="payments.php"
   class="btn btn-secondary">
<?= __('back') ?>
</a>

</div>

</div>

</div>

</section>

</div>

<?php require_once '../../includes/dashboard/footer.php'; ?>