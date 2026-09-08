<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->query("
    SELECT 
        fw.*,
        CONCAT(
            COALESCE(u.first_name, ''),
            ' ',
            COALESCE(u.middle_name, ''),
            ' ',
            COALESCE(u.last_name, '')
        ) AS creator_name
    FROM family_wallets fw
    LEFT JOIN users u
        ON u.user_id = fw.created_by_user_id
    ORDER BY fw.wallet_id DESC
");

$wallets = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebar.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">

<div class="card clan-page-card">

<div class="card-header d-flex justify-content-between align-items-center">

<h3 class="card-title">
    <i class="fas fa-wallet mr-2"></i>
    <?= __('wallets') ?>
</h3>

<a href="create.php" class="btn btn-primary btn-sm">
    <i class="fas fa-plus mr-1"></i>
    <?= __('create_wallet') ?>
</a>

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-striped">

<thead>

<tr>
    <th>#</th>
    <th>Wallet Name</th>
    <th><?= __('purpose') ?></th>
    <th><?= __('target') ?></th>
    <th><?= __('monthly') ?></th>
    <th>Current Balance</th>
    <th><?= __('status') ?></th>
    <th width="180"><?= __('actions') ?></th>
</tr>

</thead>

<tbody>

<?php if (!empty($wallets)): ?>

<?php foreach ($wallets as $wallet): ?>

<tr>

<td><?= (int)$wallet['wallet_id']; ?></td>

<td>
    <strong>
        <?= htmlspecialchars($wallet['wallet_name']); ?>
    </strong>
</td>

<td>
    <?= htmlspecialchars($wallet['purpose'] ?? ''); ?>
</td>

<td>
    <?= number_format((float)$wallet['target_amount'], 2); ?>
</td>

<td>
    <?= number_format((float)$wallet['monthly_amount'], 2); ?>
</td>

<td>
    <?= number_format((float)$wallet['current_balance'], 2); ?>
</td>

<td>

<?php if ($wallet['status'] === 'active'): ?>

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

<a
    href="view.php?id=<?= (int)$wallet['wallet_id']; ?>"
    class="btn btn-info btn-sm"
    title="View"
>
    <i class="fas fa-eye"></i>
</a>

<a
    href="edit.php?id=<?= (int)$wallet['wallet_id']; ?>"
    class="btn btn-primary btn-sm"
    title="Edit"
>
    <i class="fas fa-edit"></i>
</a>

</td>

</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>

<td colspan="8" class="text-center text-muted py-4">

<i class="fas fa-wallet fa-2x mb-2"></i>

<br>

<?= __('no_wallets_created_yet') ?>

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

<?php require_once '../../includes/dashboard/footer.php'; ?>