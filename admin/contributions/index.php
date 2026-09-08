<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->query("
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
    ORDER BY c.contribution_id DESC
");

$contributions = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebar.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">

<div class="card clan-page-card">

<div class="card-header d-flex justify-content-between align-items-center">

<div>
    <h3 class="card-title mb-1">
        <i class="fas fa-hand-holding-heart mr-2 text-primary"></i>
        Contributions Management
    </h3>

    <small class="text-muted">
        Manage clan contributions and payment progress
    </small>
</div>

<a href="create.php" class="btn btn-primary btn-sm">
    <i class="fas fa-plus mr-1"></i>
    Create Contribution
</a>

</div>

<div class="card-body">

<?php if (isset($_GET['success'])): ?>

    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-1"></i>

        <?php
        switch ($_GET['success']) {
            case 'created':
                echo 'Contribution created successfully.';
                break;

            case 'updated':
                echo 'Contribution updated successfully.';
                break;

            default:
                echo 'Operation completed successfully.';
        }
        ?>

        <button type="button"
                class="close"
                data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>

<?php endif; ?>


<?php if (isset($_GET['error'])): ?>

    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle mr-1"></i>
        Operation failed.

        <button type="button"
                class="close"
                data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>

<?php endif; ?>


<div class="table-responsive">

<table class="table table-bordered table-hover">

<thead>

<tr>

<th>#</th>
<th>Title</th>
<th>Target</th>
<th>Current</th>
<th>Progress</th>
<th>Members Paid</th>
<th>Start Date</th>
<th>End Date</th>
<th>Status</th>
<th width="170">Actions</th>

</tr>

</thead>

<tbody>

<?php if (!empty($contributions)): ?>

<?php foreach ($contributions as $contribution): ?>

<?php

$target = (float)$contribution['target_amount'];
$current = (float)$contribution['current_amount'];

$progress = $target > 0
    ? ($current / $target) * 100
    : 0;

$progress = min(100, max(0, $progress));

?>

<tr>

<td>
    <?= (int)$contribution['contribution_id']; ?>
</td>

<td>
    <strong>
        <?= htmlspecialchars($contribution['title']); ?>
    </strong>

    <?php if (!empty($contribution['description'])): ?>

        <br>

        <small class="text-muted">
            <?= htmlspecialchars(
                mb_strimwidth(
                    $contribution['description'],
                    0,
                    80,
                    '...'
                )
            ); ?>
        </small>

    <?php endif; ?>

</td>

<td>
    <?= number_format($target, 2); ?>
</td>

<td>
    <?= number_format($current, 2); ?>
</td>

<td style="min-width:150px;">

<div class="progress">

<div class="progress-bar"
     role="progressbar"
     style="width: <?= $progress ?>%;">

<?= number_format($progress, 1); ?>%

</div>

</div>

</td>

<td>
    <?= (int)$contribution['total_paid_members']; ?>
</td>

<td>
    <?= htmlspecialchars($contribution['start_date'] ?? '-'); ?>
</td>

<td>
    <?= htmlspecialchars($contribution['end_date'] ?? '-'); ?>
</td>

<td>

<?php if ($contribution['status'] === 'active'): ?>

<span class="badge badge-success">
    Active
</span>

<?php else: ?>

<span class="badge badge-secondary">
    Closed
</span>

<?php endif; ?>

</td>

<td>

<a href="view.php?id=<?= (int)$contribution['contribution_id']; ?>"
   class="btn btn-info btn-sm"
   title="View">

<i class="fas fa-eye"></i>

</a>

<a href="edit.php?id=<?= (int)$contribution['contribution_id']; ?>"
   class="btn btn-warning btn-sm"
   title="Edit">

<i class="fas fa-edit"></i>

</a>

<a href="payments.php?id=<?= (int)$contribution['contribution_id']; ?>"
   class="btn btn-success btn-sm"
   title="Payments">

<i class="fas fa-money-bill-wave"></i>

</a>

</td>

</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>

<td colspan="10"
    class="text-center py-5">

<i class="fas fa-hand-holding-heart fa-3x text-muted mb-3"></i>

<h5>No Contributions Found</h5>

<p class="text-muted mb-0">
Create the first clan contribution.
</p>

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