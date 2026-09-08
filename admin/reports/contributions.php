<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$pdo = $database->connect();

$totalContributions = (int)$pdo->query("
    SELECT COUNT(*) FROM contributions
")->fetchColumn();

$target = (float)$pdo->query("
    SELECT COALESCE(SUM(target_amount),0)
    FROM contributions
")->fetchColumn();

$paid = (float)$pdo->query("
    SELECT COALESCE(SUM(amount),0)
    FROM contribution_payments
    WHERE payment_status = 'approved'
")->fetchColumn();

$pending = (float)$pdo->query("
    SELECT COALESCE(SUM(amount),0)
    FROM contribution_payments
    WHERE payment_status = 'pending'
")->fetchColumn();

$rejected = (float)$pdo->query("
    SELECT COALESCE(SUM(amount),0)
    FROM contribution_payments
    WHERE payment_status = 'rejected'
")->fetchColumn();

$contributions = $pdo->query("
    SELECT
        c.*,
        COALESCE(
            (
                SELECT SUM(cp.amount)
                FROM contribution_payments cp
                WHERE cp.contribution_id = c.contribution_id
                  AND cp.payment_status = 'approved'
            ),0
        ) AS approved_amount
    FROM contributions c
    ORDER BY c.created_at DESC
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
<i class="fas fa-hand-holding-usd mr-2 text-success"></i>
Contributions Report
</h4>

<a href="export.php?report=contributions"
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
<h3><?= $totalContributions ?></h3>
<p>Contribution Campaigns</p>
</div>
</div>
</div>

<div class="col-md-3">
<div class="small-box bg-info">
<div class="inner">
<h3><?= number_format($target,2) ?></h3>
<p>Total Target</p>
</div>
</div>
</div>

<div class="col-md-3">
<div class="small-box bg-success">
<div class="inner">
<h3><?= number_format($paid,2) ?></h3>
<p>Approved Payments</p>
</div>
</div>
</div>

<div class="col-md-3">
<div class="small-box bg-warning">
<div class="inner">
<h3><?= number_format($pending,2) ?></h3>
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
<th><?= __('title') ?></th>
<th><?= __('target') ?></th>
<th><?= __('collected') ?></th>
<th><?= __('progress') ?></th>
<th><?= __('status') ?></th>
</tr>
</thead>

<tbody>

<?php foreach ($contributions as $i => $row): ?>

<?php
$progress = $row['target_amount'] > 0
    ? ($row['approved_amount'] / $row['target_amount']) * 100
    : 0;

$progress = min(100, $progress);
?>

<tr>

<td><?= $i + 1 ?></td>

<td><?= htmlspecialchars($row['title']) ?></td>

<td><?= number_format($row['target_amount'],2) ?></td>

<td><?= number_format($row['approved_amount'],2) ?></td>

<td style="min-width:180px">

<div class="progress">
<div class="progress-bar bg-success"
style="width:<?= $progress ?>%">
<?= number_format($progress,1) ?>%
</div>
</div>

</td>

<td>
<span class="badge badge-<?= $row['status'] === 'active' ? 'success' : 'secondary' ?>">
<?= htmlspecialchars($row['status']) ?>
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