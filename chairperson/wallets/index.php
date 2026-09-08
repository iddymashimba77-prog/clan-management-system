<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->query("
SELECT

fw.*,

CONCAT(u.first_name,' ',u.last_name) AS created_by,

(
SELECT COUNT(*)
FROM family_wallet_members fwm
WHERE fwm.wallet_id=fw.wallet_id
) AS total_members,

(
SELECT COALESCE(SUM(amount),0)
FROM family_wallet_payments fwp
WHERE
fwp.wallet_id=fw.wallet_id
AND fwp.payment_status='approved'
) AS collected_amount

FROM family_wallets fw

INNER JOIN users u
ON fw.created_by_user_id=u.user_id

ORDER BY fw.created_at DESC
");

$wallets = $stmt->fetchAll(PDO::FETCH_ASSOC);

define('USER_ROLE','Chairperson');

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/chairperson.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid" style="max-width: 1200px; margin: 0 auto;">

<div class="card">

<div class="card-header">

<h3 class="card-title">

<?= __('family_wallets') ?>

</h3>

<div class="card-tools">

<a
href="all_payments.php"
class="btn btn-info btn-sm">

<i class="fas fa-list"></i>

<?= __('all_payments') ?>

</a>

<a
href="create.php"
class="btn btn-primary btn-sm">

<i class="fas fa-plus"></i>

<?= __('create_wallet') ?>

</a>

</div>

</div>

<div class="card-body">

<table class="table table-bordered table-striped">

<thead>

<tr>

<th>#</th>

<th><?= __('wallet') ?></th>

<th><?= __('monthly') ?></th>

<th><?= __('target') ?></th>

<th><?= __('members') ?></th>

<th><?= __('collected') ?></th>

<th><?= __('expected') ?></th>

<th><?= __('status') ?></th>

<th><?= __('action') ?></th>

</tr>

</thead>

<tbody>

<?php foreach($wallets as $i=>$wallet): ?>

<tr>

<td><?= $i+1 ?></td>

<td>

<strong>

<?= htmlspecialchars($wallet['wallet_name']) ?>

</strong>

<br>

<small>

<?= htmlspecialchars($wallet['purpose']) ?>

</small>

</td>

<td>

TZS

<?= number_format($wallet['monthly_amount'],2) ?>

</td>

<td>

TZS

<?= number_format($wallet['target_amount'],2) ?>

</td>

<td>

<?= $wallet['total_members'] ?>

</td>

<td>

TZS

<?= number_format($wallet['collected_amount'],2) ?>

</td>

<td>

TZS

<?= number_format($wallet['expected_amount'],2) ?>

</td>

<td>

<?php if($wallet['status']=='active'): ?>

<span class="badge badge-success">

<?= __('active') ?>

</span>

<?php else: ?>

<span class="badge badge-danger">

<?= __('closed') ?>

</span>

<?php endif; ?>

</td>

<td>

<a
href="edit.php?id=<?= $wallet['wallet_id'] ?>"
class="btn btn-warning btn-sm">

<?= __('edit') ?>

</a>

<a
href="delete.php?id=<?= $wallet['wallet_id'] ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('<?= __('delete_wallet_confirm') ?>')">

<?= __('delete') ?>

</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

</div>

</section>

<?php
require_once '../../includes/dashboard/footer.php';
?>