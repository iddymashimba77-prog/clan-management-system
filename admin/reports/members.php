<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$pdo = $database->connect();

$total = (int)$pdo->query("
    SELECT COUNT(*)
    FROM users
    WHERE is_deleted = 0
")->fetchColumn();

$active = (int)$pdo->query("
    SELECT COUNT(*)
    FROM users
    WHERE is_deleted = 0
      AND account_status = 'active'
")->fetchColumn();

$inactive = (int)$pdo->query("
    SELECT COUNT(*)
    FROM users
    WHERE is_deleted = 0
      AND account_status = 'inactive'
")->fetchColumn();

$deceased = (int)$pdo->query("
    SELECT COUNT(*)
    FROM users
    WHERE is_deleted = 0
      AND is_deceased = 1
")->fetchColumn();

$male = (int)$pdo->query("
    SELECT COUNT(*)
    FROM users
    WHERE is_deleted = 0
      AND gender = 'Male'
")->fetchColumn();

$female = (int)$pdo->query("
    SELECT COUNT(*)
    FROM users
    WHERE is_deleted = 0
      AND gender = 'Female'
")->fetchColumn();

$members = $pdo->query("
    SELECT
        member_code,
        first_name,
        middle_name,
        last_name,
        gender,
        date_of_birth,
        marital_status,
        account_status,
        is_deceased,
        created_at
    FROM users
    WHERE is_deleted = 0
    ORDER BY first_name, last_name
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
        <i class="fas fa-users mr-2 text-primary"></i>
        Members Report
    </h4>

    <a href="export.php?report=members"
       class="btn btn-success btn-sm">
        <i class="fas fa-file-excel mr-1"></i>
        Export
    </a>
</div>

<div class="card-body">

<div class="row">

<div class="col-md-2 mb-3">
<div class="info-box">
<span class="info-box-icon bg-primary">
<i class="fas fa-users"></i>
</span>
<div class="info-box-content">
<span class="info-box-text"><?= __('total') ?></span>
<span class="info-box-number"><?= $total ?></span>
</div>
</div>
</div>

<div class="col-md-2 mb-3">
<div class="info-box">
<span class="info-box-icon bg-success">
<i class="fas fa-user-check"></i>
</span>
<div class="info-box-content">
<span class="info-box-text"><?= __('active') ?></span>
<span class="info-box-number"><?= $active ?></span>
</div>
</div>
</div>

<div class="col-md-2 mb-3">
<div class="info-box">
<span class="info-box-icon bg-warning">
<i class="fas fa-user-times"></i>
</span>
<div class="info-box-content">
<span class="info-box-text"><?= __('inactive') ?></span>
<span class="info-box-number"><?= $inactive ?></span>
</div>
</div>
</div>

<div class="col-md-2 mb-3">
<div class="info-box">
<span class="info-box-icon bg-secondary">
<i class="fas fa-star"></i>
</span>
<div class="info-box-content">
<span class="info-box-text"><?= __('deceased') ?></span>
<span class="info-box-number"><?= $deceased ?></span>
</div>
</div>
</div>

<div class="col-md-2 mb-3">
<div class="info-box">
<span class="info-box-icon bg-info">
<i class="fas fa-male"></i>
</span>
<div class="info-box-content">
<span class="info-box-text"><?= __('male') ?></span>
<span class="info-box-number"><?= $male ?></span>
</div>
</div>
</div>

<div class="col-md-2 mb-3">
<div class="info-box">
<span class="info-box-icon bg-danger">
<i class="fas fa-female"></i>
</span>
<div class="info-box-content">
<span class="info-box-text"><?= __('female') ?></span>
<span class="info-box-number"><?= $female ?></span>
</div>
</div>
</div>

</div>

<div class="table-responsive">

<table class="table table-bordered table-hover">

<thead>
<tr>
<th>#</th>
<th>Member Code</th>
<th><?= __('name') ?></th>
<th><?= __('gender') ?></th>
<th>DOB</th>
<th>Marital Status</th>
<th><?= __('account') ?></th>
<th><?= __('status') ?></th>
</tr>
</thead>

<tbody>

<?php foreach ($members as $i => $member): ?>

<tr>

<td><?= $i + 1 ?></td>

<td><?= htmlspecialchars($member['member_code']) ?></td>

<td>
<?= htmlspecialchars(
    trim(
        $member['first_name'].' '.
        ($member['middle_name'] ?? '').' '.
        $member['last_name']
    )
) ?>
</td>

<td><?= htmlspecialchars($member['gender']) ?></td>

<td><?= htmlspecialchars($member['date_of_birth'] ?? '-') ?></td>

<td><?= htmlspecialchars($member['marital_status'] ?? '-') ?></td>

<td>
<span class="badge badge-<?= $member['account_status'] === 'active' ? 'success' : 'secondary' ?>">
<?= htmlspecialchars($member['account_status']) ?>
</span>
</td>

<td>
<?php if ($member['is_deceased']): ?>
<span class="badge badge-dark"><?= __('deceased') ?></span>
<?php else: ?>
<span class="badge badge-success"><?= __('living') ?></span>
<?php endif; ?>
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