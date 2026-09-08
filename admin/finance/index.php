<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

/*
|--------------------------------------------------------------------------
| SUMMARY
|--------------------------------------------------------------------------
*/

$totalContributions = (float)($pdo->query("
    SELECT COALESCE(SUM(amount),0)
    FROM contribution_payments
    WHERE payment_status = 'approved'
")->fetchColumn());

$totalWalletPayments = (float)($pdo->query("
    SELECT COALESCE(SUM(amount),0)
    FROM family_wallet_payments
    WHERE payment_status = 'approved'
")->fetchColumn());

$pendingContributionPayments = (int)($pdo->query("
    SELECT COUNT(*)
    FROM contribution_payments
    WHERE payment_status = 'pending'
")->fetchColumn());

$pendingWalletPayments = (int)($pdo->query("
    SELECT COUNT(*)
    FROM family_wallet_payments
    WHERE payment_status = 'pending'
")->fetchColumn());

$totalWalletBalance = (float)($pdo->query("
    SELECT COALESCE(SUM(current_balance),0)
    FROM family_wallets
    WHERE status = 'active'
")->fetchColumn());

$totalContributionTargets = (float)($pdo->query("
    SELECT COALESCE(SUM(target_amount),0)
    FROM contributions
    WHERE status = 'active'
")->fetchColumn());

$totalContributionCurrent = (float)($pdo->query("
    SELECT COALESCE(SUM(current_amount),0)
    FROM contributions
    WHERE status = 'active'
")->fetchColumn());

/*
|--------------------------------------------------------------------------
| RECENT PAYMENTS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        cp.payment_id,
        cp.amount,
        cp.payment_status,
        cp.payment_date,
        c.title,
        u.first_name,
        u.last_name
    FROM contribution_payments cp
    INNER JOIN contributions c
        ON c.contribution_id = cp.contribution_id
    INNER JOIN users u
        ON u.user_id = cp.member_user_id
    ORDER BY cp.payment_id DESC
    LIMIT 10
");

$recentPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/administrator.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">

<div class="card clan-page-card">

<div class="card-body">

<div class="d-flex justify-content-between align-items-center">

<div>
<h3 class="mb-1">
<i class="fas fa-coins text-primary mr-2"></i>
<?= __('finance_management') ?>
</h3>

<p class="text-muted mb-0">
<?= __('central_finance_description') ?>
</p>
</div>

</div>

</div>
</div>


<!-- SUMMARY -->

<div class="row">

<div class="col-lg-3 col-md-6">

<div class="small-box bg-success">

<div class="inner">

<h3>
<?= number_format($totalContributions, 2); ?>
</h3>

<p><?= __('approved_contributions') ?></p>

</div>

<div class="icon">
<i class="fas fa-hand-holding-usd"></i>
</div>

</div>

</div>


<div class="col-lg-3 col-md-6">

<div class="small-box bg-primary">

<div class="inner">

<h3>
<?= number_format($totalWalletPayments, 2); ?>
</h3>

<p><?= __('wallet_payments') ?></p>

</div>

<div class="icon">
<i class="fas fa-wallet"></i>
</div>

</div>

</div>


<div class="col-lg-3 col-md-6">

<div class="small-box bg-warning">

<div class="inner">

<h3>
<?= $pendingContributionPayments + $pendingWalletPayments; ?>
</h3>

<p><?= __('pending_payments') ?></p>

</div>

<div class="icon">
<i class="fas fa-clock"></i>
</div>

</div>

</div>


<div class="col-lg-3 col-md-6">

<div class="small-box bg-info">

<div class="inner">

<h3>
<?= number_format($totalWalletBalance, 2); ?>
</h3>

<p><?= __('active_wallet_balance') ?></p>

</div>

<div class="icon">
<i class="fas fa-money-check-alt"></i>
</div>

</div>

</div>

</div>


<!-- CONTRIBUTION PROGRESS -->

<div class="card clan-page-card">

<div class="card-header">

<h3 class="card-title">
<i class="fas fa-chart-line mr-2"></i>
<?= __('contribution_progress') ?>
</h3>

</div>

<div class="card-body">

<?php

$progress = 0;

if ($totalContributionTargets > 0) {

    $progress =
        ($totalContributionCurrent /
        $totalContributionTargets) * 100;

}

$progress = min(100, max(0, $progress));

?>

<div class="progress mb-2" style="height:25px;">

<div
class="progress-bar bg-success"
style="width:<?= $progress ?>%;"
>

<?= number_format($progress, 1); ?>%

</div>

</div>

<p class="text-muted mb-0">

Collected:
<strong>
<?= number_format($totalContributionCurrent, 2); ?>
</strong>

&nbsp; / &nbsp;

Target:
<strong>
<?= number_format($totalContributionTargets, 2); ?>
</strong>

</p>

</div>

</div>


<!-- QUICK ACCESS -->

<div class="row">

<div class="col-md-3">

<a href="contributions.php"
class="text-decoration-none">

<div class="card clan-page-card">

<div class="card-body text-center">

<i class="fas fa-hand-holding-usd fa-2x text-success mb-2"></i>

<h5><?= __('contributions') ?></h5>

<p class="text-muted mb-0">
<?= __('manage_all_contributions') ?>
</p>

</div>

</div>

</a>

</div>


<div class="col-md-3">

<a href="payments.php"
class="text-decoration-none">

<div class="card clan-page-card">

<div class="card-body text-center">

<i class="fas fa-receipt fa-2x text-primary mb-2"></i>

<h5><?= __('payments') ?></h5>

<p class="text-muted mb-0">
<?= __('review_and_approve_payments') ?>
</p>

</div>

</div>

</a>

</div>


<div class="col-md-3">

<a href="wallets.php"
class="text-decoration-none">

<div class="card clan-page-card">

<div class="card-body text-center">

<i class="fas fa-wallet fa-2x text-info mb-2"></i>

<h5><?= __('wallets') ?></h5>

<p class="text-muted mb-0">
<?= __('manage_family_wallets') ?>
</p>

</div>

</div>

</a>

</div>


<div class="col-md-3">

<a href="reports.php"
class="text-decoration-none">

<div class="card clan-page-card">

<div class="card-body text-center">

<i class="fas fa-chart-bar fa-2x text-warning mb-2"></i>

<h5><?= __('reports') ?></h5>

<p class="text-muted mb-0">
<?= __('financial_reports') ?>
</p>

</div>

</div>

</a>

</div>

</div>


<!-- RECENT PAYMENTS -->

<div class="card clan-page-card">

<div class="card-header">

<h3 class="card-title">
<?= __('recent_contribution_payments') ?>
</h3>

<a href="payments.php"
class="btn btn-primary btn-sm float-right">

View All

</a>

</div>

<div class="card-body p-0">

<div class="table-responsive">

<table class="table table-bordered table-hover mb-0">

<thead>

<tr>
<th>ID</th>
<th><?= __('member') ?></th>
<th><?= __('contribution') ?></th>
<th><?= __('amount') ?></th>
<th><?= __('status') ?></th>
<th><?= __('date') ?></th>
<th><?= __('action') ?></th>
</tr>

</thead>

<tbody>

<?php foreach ($recentPayments as $payment): ?>

<tr>

<td>
<?= (int)$payment['payment_id']; ?>
</td>

<td>
<?= htmlspecialchars(
$payment['first_name'].' '.$payment['last_name']
); ?>
</td>

<td>
<?= htmlspecialchars($payment['title']); ?>
</td>

<td>
<?= number_format(
(float)$payment['amount'],
2
); ?>
</td>

<td>

<?php

$status = $payment['payment_status'];

$class = match ($status) {

    'approved' => 'success',
    'rejected' => 'danger',
    default => 'warning'

};

?>

<span class="badge badge-<?= $class; ?>">

<?= ucfirst($status); ?>

</span>

</td>

<td>

<?= htmlspecialchars(
$payment['payment_date'] ?? '-'
); ?>

</td>

<td>

<a
href="payment_view.php?id=<?= (int)$payment['payment_id']; ?>"
class="btn btn-info btn-sm"
>

<i class="fas fa-eye"></i>

</a>

</td>

</tr>

<?php endforeach; ?>


<?php if (empty($recentPayments)): ?>

<tr>

<td colspan="7"
class="text-center text-muted py-4">

<?= __('no_payment_records_found') ?>

</td>

</tr>

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