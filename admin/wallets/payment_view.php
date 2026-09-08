<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die('Invalid payment ID.');
}

$stmt = $pdo->prepare("
    SELECT

        p.*,

        w.wallet_name,

        u.member_code,
        u.first_name,
        u.middle_name,
        u.last_name,

        t.first_name AS treasurer_first_name,
        t.last_name AS treasurer_last_name,

        c.first_name AS chairperson_first_name,
        c.last_name AS chairperson_last_name

    FROM family_wallet_payments p

    LEFT JOIN family_wallets w
        ON w.wallet_id = p.wallet_id

    LEFT JOIN users u
        ON u.user_id = p.member_user_id

    LEFT JOIN users t
        ON t.user_id = p.approved_by_treasurer

    LEFT JOIN users c
        ON c.user_id = p.approved_by_chairperson

    WHERE p.payment_id = ?

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

<i class="fas fa-receipt mr-2 text-primary"></i>

Wallet Payment Details

</h3>

</div>

<div class="card-body">

<div class="row">

<div class="col-md-6">

<table class="table table-bordered">

<tr>

<th><?= __('wallet') ?></th>

<td>
<?= htmlspecialchars($payment['wallet_name']) ?>
</td>

</tr>

<tr>

<th><?= __('member') ?></th>

<td>

<?= htmlspecialchars(
    $payment['first_name'].' '.
    $payment['middle_name'].' '.
    $payment['last_name']
) ?>

<br>

<small class="text-muted">

<?= htmlspecialchars($payment['member_code']) ?>

</small>

</td>

</tr>

<tr>

<th><?= __('amount') ?></th>

<td>

<strong>

<?= number_format(
    (float)$payment['amount'],
    2
) ?>

</strong>

</td>

</tr>

<tr>

<th>Payment Month</th>

<td>
<?= htmlspecialchars($payment['payment_month']) ?>
</td>

</tr>

<tr>

<th>Reference Number</th>

<td>
<?= htmlspecialchars(
    $payment['reference_number'] ?? '-'
) ?>
</td>

</tr>

<tr>

<th>Payment Date</th>

<td>
<?= htmlspecialchars($payment['payment_date']) ?>
</td>

</tr>

</table>

</div>


<div class="col-md-6">

<table class="table table-bordered">

<tr>

<th>Payment Status</th>

<td>

<span class="badge
<?=
$payment['payment_status'] === 'approved'
    ? 'badge-success'
    : (
        $payment['payment_status'] === 'rejected'
        ? 'badge-danger'
        : 'badge-warning'
    )
?>">

<?= htmlspecialchars($payment['payment_status']) ?>

</span>

</td>

</tr>

<tr>

<th><?= __('secretary') ?></th>

<td>
<?= htmlspecialchars($payment['secretary_status']) ?>
</td>

</tr>

<tr>

<th><?= __('treasurer') ?></th>

<td>

<?= htmlspecialchars($payment['treasurer_status']) ?>

<?php if ($payment['treasurer_first_name']): ?>

<br>

<small>

By:
<?= htmlspecialchars(
    $payment['treasurer_first_name'].' '.
    $payment['treasurer_last_name']
) ?>

</small>

<?php endif; ?>

</td>

</tr>

<tr>

<th><?= __('chairperson') ?></th>

<td>

<?= htmlspecialchars($payment['chairperson_status']) ?>

<?php if ($payment['chairperson_first_name']): ?>

<br>

<small>

By:
<?= htmlspecialchars(
    $payment['chairperson_first_name'].' '.
    $payment['chairperson_last_name']
) ?>

</small>

<?php endif; ?>

</td>

</tr>

<tr>

<th><?= __('remarks') ?></th>

<td>

<?= nl2br(
    htmlspecialchars(
        $payment['remarks'] ?? ''
    )
) ?>

</td>

</tr>

</table>

</div>

</div>


<div class="mt-3">

<?php if ($payment['payment_status'] === 'pending'): ?>

<a
href="approve_payment.php?id=<?= $id ?>"
class="btn btn-success"
onclick="return confirm('Approve this wallet payment?')">

<i class="fas fa-check"></i>
Approve

</a>

<a
href="reject_payment.php?id=<?= $id ?>"
class="btn btn-danger"
onclick="return confirm('Reject this wallet payment?')">

<i class="fas fa-times"></i>
Reject

</a>

<?php endif; ?>


<a
href="payments.php?wallet_id=<?= $payment['wallet_id'] ?>"
class="btn btn-secondary">

<i class="fas fa-arrow-left"></i>
Back

</a>

</div>

</div>

</div>

</div>

</section>

</div>

<?php

require_once '../../includes/dashboard/footer.php';

?>