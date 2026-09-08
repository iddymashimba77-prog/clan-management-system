<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: index.php');
    exit;
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
    header('Location: index.php?error=not_found');
    exit;
}


$target = (float)$contribution['target_amount'];
$current = (float)$contribution['current_amount'];

$progress = $target > 0
    ? ($current / $target) * 100
    : 0;

$progress = min(100, max(0, $progress));


$paymentStmt = $pdo->prepare("
    SELECT
        COUNT(*) AS total_payments,
        COALESCE(
            SUM(
                CASE
                    WHEN payment_status = 'approved'
                    THEN amount
                    ELSE 0
                END
            ),
            0
        ) AS approved_amount,
        COALESCE(
            SUM(
                CASE
                    WHEN payment_status = 'pending'
                    THEN amount
                    ELSE 0
                END
            ),
            0
        ) AS pending_amount
    FROM contribution_payments
    WHERE contribution_id = ?
");

$paymentStmt->execute([$id]);

$paymentStats =
    $paymentStmt->fetch(PDO::FETCH_ASSOC);


require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebar.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">


<div class="card clan-page-card">

<div class="card-header d-flex justify-content-between">

<h3 class="card-title">

<i class="fas fa-hand-holding-heart mr-2 text-primary"></i>

<?= htmlspecialchars($contribution['title']); ?>

</h3>

<div>

<a href="edit.php?id=<?= $id; ?>"
   class="btn btn-warning btn-sm">

<i class="fas fa-edit"></i>
Edit

</a>

<a href="payments.php?id=<?= $id; ?>"
   class="btn btn-success btn-sm">

<i class="fas fa-money-bill-wave"></i>
Payments

</a>

</div>

</div>


<div class="card-body">

<div class="row">


<div class="col-md-4">

<?php if (!empty($contribution['contribution_image'])): ?>

<img
    src="../../uploads/contributions/<?= htmlspecialchars($contribution['contribution_image']); ?>"
    class="img-fluid rounded"
    alt="Contribution Image">

<?php else: ?>

<div class="text-center p-5 bg-light rounded">

<i class="fas fa-hand-holding-heart fa-4x text-muted"></i>

<p class="mt-3 text-muted">
No contribution image
</p>

</div>

<?php endif; ?>

</div>


<div class="col-md-8">

<h4>
<?= htmlspecialchars($contribution['title']); ?>
</h4>

<p class="text-muted">

<?= nl2br(
    htmlspecialchars(
        $contribution['description'] ?? ''
    )
); ?>

</p>


<hr>


<div class="row">

<div class="col-md-4">

<strong>Target</strong>

<h5>
<?= number_format($target, 2); ?>
</h5>

</div>


<div class="col-md-4">

<strong>Collected</strong>

<h5>
<?= number_format($current, 2); ?>
</h5>

</div>


<div class="col-md-4">

<strong>Status</strong>

<h5>

<?php if ($contribution['status'] === 'active'): ?>

<span class="badge badge-success">
Active
</span>

<?php else: ?>

<span class="badge badge-secondary">
Closed
</span>

<?php endif; ?>

</h5>

</div>

</div>


<div class="mt-3">

<div class="progress"
     style="height:25px;">

<div class="progress-bar"
     style="width:<?= $progress ?>%;">

<?= number_format($progress, 1); ?>%

</div>

</div>

</div>

</div>

</div>


<hr>


<div class="row text-center">


<div class="col-md-3">

<div class="border rounded p-3">

<h4>
<?= (int)$contribution['total_paid_members']; ?>
</h4>

<small class="text-muted">
<?= __('paid_members') ?>
</small>

</div>

</div>


<div class="col-md-3">

<div class="border rounded p-3">

<h4>
<?= (int)$contribution['total_pending_members']; ?>
</h4>

<small class="text-muted">
<?= __('pending_members') ?>
</small>

</div>

</div>


<div class="col-md-3">

<div class="border rounded p-3">

<h4>
<?= number_format(
    (float)$paymentStats['approved_amount'],
    2
); ?>
</h4>

<small class="text-muted">
Approved Payments
</small>

</div>

</div>


<div class="col-md-3">

<div class="border rounded p-3">

<h4>
<?= number_format(
    (float)$paymentStats['pending_amount'],
    2
); ?>
</h4>

<small class="text-muted">
Pending Amount
</small>

</div>

</div>

</div>


<hr>


<table class="table table-bordered">

<tr>

<th width="30%">
Allow Partial Payment
</th>

<td>

<?= $contribution['allow_partial_payment']
    ? '<span class="badge badge-success">Yes</span>'
    : '<span class="badge badge-secondary">No</span>'; ?>

</td>

</tr>


<tr>

<th>
Minimum Amount
</th>

<td>
<?= number_format(
    (float)$contribution['minimum_amount'],
    2
); ?>

</td>

</tr>


<tr>

<th>
<?= __('start_date') ?>
</th>

<td>
<?= htmlspecialchars(
    $contribution['start_date'] ?? '-'
); ?>

</td>

</tr>


<tr>

<th>
<?= __('end_date') ?>
</th>

<td>
<?= htmlspecialchars(
    $contribution['end_date'] ?? '-'
); ?>

</td>

</tr>


<tr>

<th>
Created By
</th>

<td>
<?= htmlspecialchars(
    trim($contribution['creator_name'])
    ?: 'System'
); ?>

</td>

</tr>


<tr>

<th>
Created At
</th>

<td>
<?= htmlspecialchars(
    $contribution['created_at']
); ?>

</td>

</tr>

</table>


<div class="mt-3">

<a href="index.php"
   class="btn btn-secondary">

<i class="fas fa-arrow-left mr-1"></i>
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