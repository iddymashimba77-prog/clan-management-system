<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    die('Invalid wallet ID.');
}

$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->prepare("
    SELECT wallet_name
    FROM family_wallets
    WHERE wallet_id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$wallet = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$wallet) {
    die('Wallet not found.');
}

$stmt = $pdo->prepare("
    SELECT
        wp.*,
        u.member_code,
        CONCAT(
            u.first_name, ' ',
            COALESCE(u.middle_name, ''), ' ',
            u.last_name
        ) AS member_name
    FROM family_wallet_payments wp
    INNER JOIN users u
        ON u.user_id = wp.member_user_id
    WHERE wp.wallet_id = ?
    ORDER BY wp.payment_date DESC
");

$stmt->execute([$id]);

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
<?= __('wallet_payments') ?> —
<?= htmlspecialchars($wallet['wallet_name']); ?>
</h3>

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-striped">

<thead>

<tr>
<th>ID</th>
<th><?= __('member') ?></th>
<th><?= __('amount') ?></th>
<th><?= __('month') ?></th>
<th><?= __('reference') ?></th>
<th><?= __('status') ?></th>
<th><?= __('date') ?></th>
</tr>

</thead>

<tbody>

<?php foreach ($payments as $payment): ?>

<tr>

<td><?= (int)$payment['payment_id']; ?></td>

<td>
<?= htmlspecialchars(
    $payment['member_code'].' - '.$payment['member_name']
); ?>
</td>

<td><?= number_format($payment['amount'], 2); ?></td>

<td><?= htmlspecialchars($payment['payment_month']); ?></td>

<td><?= htmlspecialchars($payment['reference_number'] ?? ''); ?></td>

<td><?= htmlspecialchars($payment['payment_status']); ?></td>

<td><?= htmlspecialchars($payment['payment_date']); ?></td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<a href="wallet_view.php?id=<?= $id ?>"
   class="btn btn-secondary">
<?= __('back') ?>
</a>

</div>

</div>

</div>

</section>

</div>

<?php require_once '../../includes/dashboard/footer.php'; ?>