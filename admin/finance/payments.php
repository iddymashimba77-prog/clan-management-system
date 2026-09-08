<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->query("
    SELECT
        cp.*,
        c.title AS contribution_title,
        u.member_code,
        u.first_name,
        u.last_name
    FROM contribution_payments cp
    INNER JOIN contributions c
        ON c.contribution_id = cp.contribution_id
    INNER JOIN users u
        ON u.user_id = cp.member_user_id
    ORDER BY cp.payment_id DESC
");

$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
<i class="fas fa-receipt mr-2"></i>
<?= __('contribution_payments') ?>
</h3>

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-hover">

<thead>

<tr>

<th>ID</th>
<th><?= __('member') ?></th>
<th><?= __('contribution') ?></th>
<th><?= __('amount') ?></th>
<th><?= __('reference') ?></th>
<th><?= __('status') ?></th>
<th><?= __('submitted') ?></th>
<th><?= __('actions') ?></th>

</tr>

</thead>

<tbody>

<?php foreach ($payments as $row): ?>

<?php

$class = match ($row['payment_status']) {

    'approved' => 'success',
    'rejected' => 'danger',
    default => 'warning'

};

?>

<tr>

<td><?= (int)$row['payment_id']; ?></td>

<td>

<strong>
<?= htmlspecialchars(
$row['first_name'].' '.$row['last_name']
); ?>
</strong>

<br>

<small class="text-muted">
<?= htmlspecialchars($row['member_code']); ?>
</small>

</td>

<td>
<?= htmlspecialchars($row['contribution_title']); ?>
</td>

<td>
<?= number_format((float)$row['amount'], 2); ?>
</td>

<td>
<?= htmlspecialchars(
$row['reference_number'] ?? '-'
); ?>
</td>

<td>

<span class="badge badge-<?= $class; ?>">

<?= ucfirst(
$row['payment_status']
); ?>

</span>

</td>

<td>
<?= htmlspecialchars(
$row['submitted_at']
); ?>
</td>

<td>

<a
href="payment_view.php?id=<?= (int)$row['payment_id']; ?>"
class="btn btn-info btn-sm"
>

<i class="fas fa-eye"></i>

</a>

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