<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->query("
    SELECT
        d.*,
        w.wallet_name,
        u.member_code,
        u.first_name,
        u.last_name
    FROM wallet_monthly_dues d
    INNER JOIN family_wallets w
        ON w.wallet_id = d.wallet_id
    INNER JOIN users u
        ON u.user_id = d.member_user_id
    ORDER BY d.due_id DESC
");

$dues = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/administrator.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">

<div class="card clan-page-card">

<div class="card-header">

<h3 class="card-title">

<i class="fas fa-calendar-check mr-2"></i>

<?= __('wallet_monthly_dues') ?>

</h3>

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-hover">

<thead>

<tr>

<th>ID</th>
<th><?= __('member') ?></th>
<th><?= __('wallet') ?></th>
<th><?= __('month') ?></th>
<th><?= __('required') ?></th>
<th><?= __('paid') ?></th>
<th><?= __('balance') ?></th>
<th><?= __('status') ?></th>

</tr>

</thead>

<tbody>

<?php foreach ($dues as $due): ?>

<?php

$class = match ($due['status']) {

    'Completed' => 'success',
    'Partial' => 'warning',
    default => 'danger'

};

?>

<tr>

<td>
<?= (int)$due['due_id']; ?>
</td>

<td>

<?= htmlspecialchars(
$due['first_name'].' '.$due['last_name']
); ?>

<br>

<small class="text-muted">

<?= htmlspecialchars(
$due['member_code']
); ?>

</small>

</td>

<td>
<?= htmlspecialchars(
$due['wallet_name']
); ?>
</td>

<td>
<?= htmlspecialchars(
$due['due_month']
); ?>
</td>

<td>
<?= number_format(
(float)$due['required_amount'],
2
); ?>
</td>

<td>
<?= number_format(
(float)$due['paid_amount'],
2
); ?>
</td>

<td>
<?= number_format(
(float)$due['balance'],
2
); ?>
</td>

<td>

<span class="badge badge-<?= $class; ?>">

<?= htmlspecialchars(
$due['status']
); ?>

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

<?php

require_once '../../includes/dashboard/footer.php';

?>