<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$pdo = $database->connect();

$contributionIncome = (float)$pdo->query("
    SELECT COALESCE(SUM(amount),0)
    FROM contribution_payments
    WHERE payment_status = 'approved'
")->fetchColumn();

$walletIncome = (float)$pdo->query("
    SELECT COALESCE(SUM(amount),0)
    FROM family_wallet_payments
    WHERE payment_status = 'approved'
")->fetchColumn();

$pendingContribution = (float)$pdo->query("
    SELECT COALESCE(SUM(amount),0)
    FROM contribution_payments
    WHERE payment_status = 'pending'
")->fetchColumn();

$pendingWallet = (float)$pdo->query("
    SELECT COALESCE(SUM(amount),0)
    FROM family_wallet_payments
    WHERE payment_status = 'pending'
")->fetchColumn();

$totalIncome = $contributionIncome + $walletIncome;
$totalPending = $pendingContribution + $pendingWallet;

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
<i class="fas fa-money-bill-wave mr-2 text-success"></i>
Financial Report
</h4>

<a href="export.php?report=finance"
class="btn btn-success btn-sm">
<i class="fas fa-download mr-1"></i>
Export
</a>

</div>

<div class="card-body">

<div class="row">

<div class="col-md-4">
<div class="small-box bg-success">
<div class="inner">
<h3><?= number_format($totalIncome,2) ?></h3>
<p>Total Approved Income</p>
</div>
</div>
</div>

<div class="col-md-4">
<div class="small-box bg-info">
<div class="inner">
<h3><?= number_format($contributionIncome,2) ?></h3>
<p>Contribution Income</p>
</div>
</div>
</div>

<div class="col-md-4">
<div class="small-box bg-primary">
<div class="inner">
<h3><?= number_format($walletIncome,2) ?></h3>
<p>Wallet Income</p>
</div>
</div>
</div>

</div>

<div class="row">

<div class="col-md-6">

<table class="table table-bordered">

<tr>
<th>Contribution Approved</th>
<td><?= number_format($contributionIncome,2) ?></td>
</tr>

<tr>
<th>Wallet Approved</th>
<td><?= number_format($walletIncome,2) ?></td>
</tr>

<tr class="font-weight-bold">
<th>Total Approved</th>
<td><?= number_format($totalIncome,2) ?></td>
</tr>

</table>

</div>

<div class="col-md-6">

<table class="table table-bordered">

<tr>
<th>Contribution Pending</th>
<td><?= number_format($pendingContribution,2) ?></td>
</tr>

<tr>
<th>Wallet Pending</th>
<td><?= number_format($pendingWallet,2) ?></td>
</tr>

<tr class="font-weight-bold">
<th>Total Pending</th>
<td><?= number_format($totalPending,2) ?></td>
</tr>

</table>

</div>

</div>

</div>
</div>

</div>
</section>
</div>

<?php require_once '../../includes/dashboard/footer.php'; ?>