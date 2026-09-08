<?php


require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

try {
    $database = new Database();
    $pdo = $database->connect();
} catch (Exception $e) {
    die(__('database_connection_failed_prefix') . $e->getMessage());
}

$status_filter = $_GET['status'] ?? 'all';

try {
    $where_clause = "";
    $params = [];

    if($status_filter == 'pending') {
        $where_clause = "WHERE cp.treasurer_status = 'Approved' AND cp.chairperson_status = 'Pending'";
    } elseif($status_filter == 'approved') {
        $where_clause = "WHERE cp.chairperson_status = 'Approved'";
    } elseif($status_filter == 'rejected') {
        $where_clause = "WHERE cp.chairperson_status = 'Rejected'";
    } elseif($status_filter == 'all') {
        $where_clause = "";
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
cp.approved_by_treasurer,
cp.approved_by_chairperson,
cp.treasurer_date,
cp.chairperson_date,

u.member_code,
CONCAT(u.first_name,' ',u.last_name) AS member_name,

fw.wallet_name,

t_u.first_name AS treasurer_first_name,
t_u.last_name AS treasurer_last_name,

c_u.first_name AS chairperson_first_name,
c_u.last_name AS chairperson_last_name

FROM family_wallet_payments cp

INNER JOIN users u
ON cp.member_user_id = u.user_id

INNER JOIN family_wallets fw
ON cp.wallet_id = fw.wallet_id

LEFT JOIN users t_u
ON cp.approved_by_treasurer = t_u.user_id

LEFT JOIN users c_u
ON cp.approved_by_chairperson = c_u.user_id

$where_clause

ORDER BY cp.payment_date DESC
";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die(__('query_failed_prefix') . $e->getMessage());
}

define('USER_ROLE','Chairperson');

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/'.strtolower(USER_ROLE).'.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid" style="max-width: 1200px; margin: 0 auto;">

<div class="card">

<div class="card-header">

<h3 class="card-title">
<i class="fas fa-list"></i>
<?= __('all_wallet_payments') ?>
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
<?= __('no_payments_found') ?>
</div>

<?php else: ?>

<table class="table table-bordered table-striped">

<thead>

<tr>

<th>#</th>

<th><?= __('member') ?></th>

<th><?= __('wallet') ?></th>

<th><?= __('amount') ?></th>

<th><?= __('reference') ?></th>

<th><?= __('treasurer') ?></th>

<th><?= __('chairperson') ?></th>

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

<td><?= htmlspecialchars($row['wallet_name']) ?></td>

<td>TZS <?= number_format($row['amount'],2) ?></td>

<td><?= htmlspecialchars($row['reference_number']) ?></td>

<td>
<?php if(!empty($row['treasurer_first_name'])): ?>
<?= htmlspecialchars($row['treasurer_first_name'].' '.$row['treasurer_last_name']) ?>
<br>
<small><?= date('Y-m-d H:i', strtotime($row['treasurer_date'])) ?></small>
<?php else: ?>
-
<?php endif; ?>
</td>

<td>
<?php if(!empty($row['chairperson_first_name'])): ?>
<?= htmlspecialchars($row['chairperson_first_name'].' '.$row['chairperson_last_name']) ?>
<br>
<small><?= date('Y-m-d H:i', strtotime($row['chairperson_date'])) ?></small>
<?php else: ?>
-
<?php endif; ?>
</td>

<td>
<span class="badge badge-<?= $row['chairperson_status'] == 'Approved' ? 'success' : ($row['chairperson_status'] == 'Rejected' ? 'danger' : 'warning') ?>">
<?= $row['chairperson_status'] ?>
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
<?php if($row['chairperson_status'] == 'Pending' && $row['treasurer_status'] == 'Approved'): ?>
<div class="btn-group-vertical d-flex">
<a href="approve.php?id=<?= $row['payment_id'] ?>" class="btn btn-sm btn-success text-nowrap" title="<?= __('approve') ?>" onclick="return confirm('<?= __('approve_payment_confirm') ?>')">
<i class="fas fa-check"></i> <?= __('approve') ?>
</a>
<a href="reject.php?id=<?= $row['payment_id'] ?>" class="btn btn-sm btn-danger text-nowrap mt-1" title="<?= __('reject') ?>" onclick="return confirm('<?= __('reject_payment_confirm') ?>')">
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