<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireTreasurer();

try {
    $database = new Database();
    $pdo = $database->connect();
} catch (Exception $e) {
    die(__('db_connection_failed') . $e->getMessage());
}

$clanId = getCurrentClanId();

$status_filter = $_GET['status'] ?? 'pending';

try {
    $where_clause = "";
    $params = [];

    if($status_filter == 'pending') {
        $where_clause = "WHERE cp.treasurer_status = 'Pending' AND cp.chairperson_status = 'Pending' AND cp.secretary_status = 'Forwarded' AND cp.clan_id = ?";
        $params = [$clanId];
    } elseif($status_filter == 'approved') {
        $where_clause = "WHERE cp.treasurer_status = 'Approved' AND cp.clan_id = ?";
        $params = [$clanId];
    } elseif($status_filter == 'rejected') {
        $where_clause = "WHERE cp.treasurer_status = 'Rejected' AND cp.clan_id = ?";
        $params = [$clanId];
    } elseif($status_filter == 'all') {
        $where_clause = "WHERE cp.clan_id = ?";
        $params = [$clanId];
    }

    $sql = "
SELECT
cp.payment_id,
cp.amount,
cp.reference_number,
cp.payment_date,
cp.payment_proof,
cp.treasurer_status,
cp.chairperson_status,
cp.payment_status,

u.member_code,
CONCAT(u.first_name,' ',u.last_name) AS member_name,

e.title AS event_name,
ec.title AS contribution_title,

pm.method_name,
pm.provider_name,
pm.phone_number,
pm.account_number

FROM contribution_payments cp

INNER JOIN users u
ON cp.member_user_id = u.user_id

INNER JOIN event_contributions ec
ON cp.contribution_id = ec.contribution_id

INNER JOIN events e
ON ec.event_id = e.event_id

LEFT JOIN payment_methods pm
ON cp.payment_method_id = pm.payment_method_id

$where_clause

ORDER BY cp.payment_date DESC
";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die(__('query_failed') . $e->getMessage());
}

define('USER_ROLE','Treasurer');

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebar.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid" style="max-width: 1200px; margin: 0 auto;">

<div class="card">

<div class="card-header">

<h3 class="card-title">
<i class="fas fa-clock"></i>
<?= __('payment_approvals') ?>
</h3>

<div class="card-tools">
<div class="btn-group">
<a href="?status=pending" class="btn btn-sm <?= $status_filter == 'pending' ? 'btn-primary' : 'btn-default' ?>">
<?= __('pending') ?>
</a>
<a href="?status=approved" class="btn btn-sm <?= $status_filter == 'approved' ? 'btn-primary' : 'btn-default' ?>">
<?= __('approved') ?>
</a>
<a href="?status=rejected" class="btn btn-sm <?= $status_filter == 'rejected' ? 'btn-primary' : 'btn-default' ?>">
<?= __('rejected') ?>
</a>
<a href="?status=all" class="btn btn-sm <?= $status_filter == 'all' ? 'btn-primary' : 'btn-default' ?>">
<?= __('all') ?>
</a>
</div>
</div>

</div>

<div class="card-body">

<?php if(empty($payments)): ?>

<div class="alert alert-info">
<?= __('no_pending_payments_awaiting_approval') ?>
</div>

<?php else: ?>

<table class="table table-bordered table-striped">

<thead>

<tr>

<th>#</th>

<th><?= __('member') ?></th>

<th><?= __('event') ?></th>

<th><?= __('contribution') ?></th>

<th><?= __('payment_method') ?></th>

<th><?= __('amount') ?></th>

<th><?= __('reference') ?></th>

<th><?= __('status') ?></th>

<th><?= __('date') ?></th>

<th><?= __('proof') ?></th>

<th><?= __('action') ?></th>

</tr>

</thead>

<tbody>

<?php foreach($payments as $row): ?>

<tr>

<td><?= $row['payment_id'] ?></td>

<td>
<b><?= htmlspecialchars($row['member_code']) ?></b>
<br>
<?= htmlspecialchars($row['member_name']) ?>
</td>

<td><?= htmlspecialchars($row['event_name']) ?></td>

<td><?= htmlspecialchars($row['contribution_title']) ?></td>

<td>
<?= htmlspecialchars($row['method_name']) ?>
<?php if(!empty($row['provider_name'])): ?>
- <?= htmlspecialchars($row['provider_name']) ?>
<?php endif; ?>
</td>

<td>TZS <?= number_format($row['amount'],2) ?></td>

<td><?= htmlspecialchars($row['reference_number']) ?></td>

<td>
<span class="badge badge-<?= $row['treasurer_status'] == 'Approved' ? 'success' : ($row['treasurer_status'] == 'Rejected' ? 'danger' : 'warning') ?>">
<?= __(strtolower($row['treasurer_status'])) ?>
</span>
</td>

<td><?= date('Y-m-d H:i', strtotime($row['payment_date'])) ?></td>

<td>
<?php if(!empty($row['payment_proof'])): ?>
<a href="../../uploads/payment_proofs/<?= htmlspecialchars($row['payment_proof']) ?>" target="_blank" class="btn btn-sm btn-info">
<?= __('view_proof') ?>
</a>
<?php else: ?>
-
<?php endif; ?>
</td>

<td>
<?php if($row['treasurer_status'] == 'Pending'): ?>
<div class="btn-group">
<a href="../contributions/approve.php?id=<?= $row['payment_id'] ?>" class="btn btn-sm btn-success" title="<?= __('approve') ?>">
<i class="fas fa-check"></i> <?= __('approve') ?>
</a>
<a href="../contributions/reject.php?id=<?= $row['payment_id'] ?>" class="btn btn-sm btn-danger" title="<?= __('reject') ?>" onclick="return confirm('<?= __('reject_payment_confirm') ?>')">
<i class="fas fa-times"></i> <?= __('reject') ?>
</a>
</div>
<?php else: ?>
-
<?php endif; ?>
</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

<?php endif; ?>

</div>

</div>

</div>

</section>

</div>

<?php
require_once '../../includes/dashboard/footer.php';
