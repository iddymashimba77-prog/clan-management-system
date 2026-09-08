<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$wallet_id = isset($_GET['wallet_id']) ? (int)$_GET['wallet_id'] : 0;

if ($wallet_id <= 0) {
    die('Invalid wallet ID.');
}

/*
|--------------------------------------------------------------------------
| Wallet
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM family_wallets
    WHERE wallet_id = ?
    LIMIT 1
");

$stmt->execute([$wallet_id]);

$wallet = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$wallet) {
    die('Wallet not found.');
}

/*
|--------------------------------------------------------------------------
| Payments
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        p.*,

        u.member_code,
        u.first_name,
        u.middle_name,
        u.last_name,

        t.first_name AS treasurer_first_name,
        t.last_name AS treasurer_last_name,

        c.first_name AS chairperson_first_name,
        c.last_name AS chairperson_last_name

    FROM family_wallet_payments p

    LEFT JOIN users u
        ON u.user_id = p.member_user_id

    LEFT JOIN users t
        ON t.user_id = p.approved_by_treasurer

    LEFT JOIN users c
        ON c.user_id = p.approved_by_chairperson

    WHERE p.wallet_id = ?

    ORDER BY p.payment_id DESC
");

$stmt->execute([$wallet_id]);

$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <i class="fas fa-wallet mr-2 text-primary"></i>
    <?= htmlspecialchars($wallet['wallet_name']) ?> - <?= __('payments') ?>
</h3>

<div class="card-tools">

<a href="view.php?id=<?= $wallet_id ?>"
   class="btn btn-secondary btn-sm">

<i class="fas fa-arrow-left"></i>
<?= __('back') ?>

</a>

</div>

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-striped">

<thead>

<tr>

<th>#</th>
<th><?= __('member') ?></th>
<th><?= __('amount') ?></th>
<th><?= __('month') ?></th>
<th><?= __('reference') ?></th>
<th>Payment Status</th>
<th><?= __('secretary') ?></th>
<th><?= __('treasurer') ?></th>
<th><?= __('chairperson') ?></th>
<th><?= __('action') ?></th>

</tr>

</thead>

<tbody>

<?php if (!$payments): ?>

<tr>

<td colspan="10" class="text-center text-muted py-4">

<?= __('no_wallet_payments_found') ?>

</td>

</tr>

<?php else: ?>

<?php foreach ($payments as $payment): ?>

<tr>

<td><?= $payment['payment_id'] ?></td>

<td>

<strong>
<?= htmlspecialchars(
    $payment['first_name'].' '.
    $payment['middle_name'].' '.
    $payment['last_name']
) ?>
</strong>

<br>

<small class="text-muted">

<?= htmlspecialchars($payment['member_code']) ?>

</small>

</td>

<td>

<strong>

<?= number_format(
    (float)$payment['amount'],
    2
) ?>

</strong>

</td>

<td>

<?= htmlspecialchars($payment['payment_month']) ?>

</td>

<td>

<?= htmlspecialchars(
    $payment['reference_number'] ?? '-'
) ?>

</td>

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

<td>

<?= htmlspecialchars(
    $payment['secretary_status']
) ?>

</td>

<td>

<?= htmlspecialchars(
    $payment['treasurer_status']
) ?>

</td>

<td>

<?= htmlspecialchars(
    $payment['chairperson_status']
) ?>

</td>

<td>

<a href="payment_view.php?id=<?= $payment['payment_id'] ?>"
   class="btn btn-info btn-sm">

<i class="fas fa-eye"></i>

</a>

</td>

</tr>

<?php endforeach; ?>

<?php endif; ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

</section>

</div>

<?php

require_once '../../includes/dashboard/footer.php';

?>