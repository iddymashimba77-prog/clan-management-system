<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    die('Invalid contribution ID.');
}

$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->prepare("
    SELECT
        c.*,
        CONCAT(
            COALESCE(u.first_name, ''),
            ' ',
            COALESCE(u.middle_name, ''),
            ' ',
            COALESCE(u.last_name, '')
        ) AS creator_name
    FROM contributions c
    LEFT JOIN users u
        ON u.user_id = c.created_by_user_id
    WHERE c.contribution_id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$contribution = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contribution) {
    die('Contribution not found.');
}

$stmt = $pdo->prepare("
    SELECT
        cp.*,
        u.member_code,
        CONCAT(
            u.first_name, ' ',
            COALESCE(u.middle_name, ''), ' ',
            u.last_name
        ) AS member_name,
        pm.method_name
    FROM contribution_payments cp
    INNER JOIN users u
        ON u.user_id = cp.member_user_id
    LEFT JOIN contribution_payment_methods pm
        ON pm.method_id = cp.payment_method_id
    WHERE cp.contribution_id = ?
    ORDER BY cp.submitted_at DESC
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
<i class="fas fa-hand-holding-usd mr-2"></i>
<?= __('contribution_details') ?>
</h3>

<div class="card-tools">

<a href="index.php" class="btn btn-secondary btn-sm">
<i class="fas fa-arrow-left"></i>
Back
</a>

</div>

</div>

<div class="card-body">

<div class="row">

<div class="col-md-8">

<h4><?= htmlspecialchars($contribution['title']); ?></h4>

<p>
<?= nl2br(htmlspecialchars($contribution['description'] ?? '')); ?>
</p>

<table class="table table-bordered">

<tr>
<th><?= __('target_amount') ?></th>
<td><?= number_format($contribution['target_amount'], 2); ?></td>
</tr>

<tr>
<th><?= __('current_amount') ?></th>
<td><?= number_format($contribution['current_amount'], 2); ?></td>
</tr>

<tr>
<th><?= __('progress') ?></th>
<td><?= number_format($contribution['progress'], 2); ?>%</td>
</tr>

<tr>
<th><?= __('paid_members') ?></th>
<td><?= (int)$contribution['total_paid_members']; ?></td>
</tr>

<tr>
<th><?= __('pending_members') ?></th>
<td><?= (int)$contribution['total_pending_members']; ?></td>
</tr>

<tr>
<th><?= __('start_date') ?></th>
<td><?= htmlspecialchars($contribution['start_date'] ?? ''); ?></td>
</tr>

<tr>
<th><?= __('end_date') ?></th>
<td><?= htmlspecialchars($contribution['end_date'] ?? ''); ?></td>
</tr>

<tr>
<th><?= __('status') ?></th>
<td><?= htmlspecialchars($contribution['status']); ?></td>
</tr>

</table>

</div>

<div class="col-md-4">

<div class="card card-primary">

<div class="card-body">

<h5><?= __('summary') ?></h5>

<p>
<strong><?= __('creator') ?></strong><br>
<?= htmlspecialchars($contribution['creator_name'] ?? 'Unknown'); ?>
</p>

<p>
<strong><?= __('minimum_payment') ?></strong><br>
<?= number_format($contribution['minimum_amount'], 2); ?>
</p>

<p>
<strong><?= __('partial_payment') ?></strong><br>
<?= $contribution['allow_partial_payment'] ? __('allowed') : __('not_allowed'); ?>
</p>

</div>

</div>

</div>

</div>

<hr>

<h5><?= __('payments') ?></h5>

<div class="table-responsive">

<table class="table table-bordered table-striped">

<thead>

<tr>
<th>ID</th>
<th><?= __('member') ?></th>
<th><?= __('amount') ?></th>
<th><?= __('method') ?></th>
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

<td><?= htmlspecialchars($payment['method_name'] ?? 'Unknown'); ?></td>

<td><?= htmlspecialchars($payment['payment_status']); ?></td>

<td><?= htmlspecialchars($payment['submitted_at']); ?></td>

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