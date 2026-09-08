<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$pdo = $database->connect();

$totalWallets = (int)$pdo->query("
    SELECT COUNT(*) FROM family_wallets
")->fetchColumn();

$totalBalance = (float)$pdo->query("
    SELECT COALESCE(SUM(current_balance),0)
    FROM family_wallets
    WHERE status = 'active'
")->fetchColumn();

$totalPayments = (float)$pdo->query("
    SELECT COALESCE(SUM(amount),0)
    FROM family_wallet_payments
    WHERE payment_status = 'approved'
")->fetchColumn();

$totalPending = (float)$pdo->query("
    SELECT COALESCE(SUM(amount),0)
    FROM family_wallet_payments
    WHERE payment_status = 'pending'
")->fetchColumn();

$wallets = $pdo->query("
    SELECT *
    FROM family_wallets
    ORDER BY created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/admin.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">
<section class="content">
<div class="container-fluid clan-page-content">

<div class="card clan-page-card">

<div class="card-header d-flex justify-content-between">

<h4 class="mb-0">
<i class="fas fa-wallet mr-2 text-info"></i>
Wallets Report
</h4>

<a href="export.php?report=wallets"
class="btn btn-success btn-sm">
<i class="fas fa-download mr-1"></i>
Export
</a>

</div>

<div class="card-body">

<div class="row">

<div class="col-md-3">
<div class="small-box bg-primary">
<div class="inner">
<h3><?= $totalWallets ?></h3>
<p>Total Wallets</p>
</div>
</div>
</div>

<div class="col-md-3">
<div class="small-box bg-success">
<div class="inner">
<h3><?= number_format($totalBalance,2) ?></h3>
<p>Current Balance</p>
</div>
</div>
</div>

<div class="col-md-3">
<div class="small-box bg-info">
<div class="inner">
<h3><?= number_format($totalPayments,2) ?></h3>
<p>Approved Payments</p>
</div>
</div>
</div>

<div class="col-md-3">
<div class="small-box bg-warning">
<div class="inner">
<h3><?= number_format($totalPending,2) ?></h3>
<p><?= __('pending_payments') ?></p>
</div>
</div>
</div>

</div>

<div class="table-responsive">

<table class="table table-bordered table-hover">

<thead>
<tr>
<th>#</th>
<th><?= __('wallet') ?></th>
<th><?= __('purpose') ?></th>
<th><?= __('target') ?></th>
<th><?= __('monthly') ?></th>
<th><?= __('balance') ?></th>
<th><?= __('status') ?></th>
</tr>
</thead>

<tbody>

<?php foreach ($wallets as $i => $wallet): ?>

<tr>

<td><?= $i + 1 ?></td>

<td><?= htmlspecialchars($wallet['wallet_name']) ?></td>

<td><?= htmlspecialchars($wallet['purpose'] ?? '-') ?></td>

<td><?= number_format($wallet['target_amount'],2) ?></td>

<td><?= number_format($wallet['monthly_amount'],2) ?></td>

<td><?= number_format($wallet['current_balance'],2) ?></td>

<td>
<span class="badge badge-<?= $wallet['status'] === 'active' ? 'success' : 'secondary' ?>">
<?= htmlspecialchars($wallet['status']) ?>
</span>
</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>
</div>

</div>
</section>
</div>

<?php require_once '../../includes/dashboard/footer.php'; ?>