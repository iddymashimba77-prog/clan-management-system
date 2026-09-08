<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database=new Database();
$pdo=$database->connect();

$wallet_id=(int)($_GET['id'] ?? 0);

$stmt=$pdo->prepare("
SELECT

fwm.*,

u.member_code,

CONCAT(u.first_name,' ',u.last_name) AS member_name,

COALESCE(SUM(fwp.amount),0) AS total_paid

FROM family_wallet_members fwm

INNER JOIN users u
ON fwm.member_user_id=u.user_id

LEFT JOIN family_wallet_payments fwp
ON fwm.wallet_id=fwp.wallet_id
AND fwm.member_user_id=fwp.member_user_id
AND fwp.payment_status='approved'

WHERE fwm.wallet_id=?

GROUP BY fwm.id

ORDER BY member_name
");

$stmt->execute([$wallet_id]);

$members=$stmt->fetchAll(PDO::FETCH_ASSOC);

$wallet=$pdo->prepare("
SELECT *
FROM family_wallets
WHERE wallet_id=?
");

$wallet->execute([$wallet_id]);

$wallet=$wallet->fetch(PDO::FETCH_ASSOC);

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

<h3><?= htmlspecialchars($wallet['wallet_name']) ?></h3>

<a
href="add_members.php?id=<?= $wallet_id ?>"
class="btn btn-success float-right">

<?= __('add_members') ?>

</a>

</div>

<div class="card-body">

<table class="table table-bordered table-striped">

<thead>

<tr>

<th><?= __('member_code') ?></th>

<th><?= __('name') ?></th>

<th><?= __('monthly') ?></th>

<th><?= __('total_paid') ?></th>

<th><?= __('balance') ?></th>

<th><?= __('action') ?></th>

</tr>

</thead>

<tbody>

<?php foreach($members as $row):

$balance=$wallet['year_target']-$row['total_paid'];

?>

<tr>

<td><?= $row['member_code'] ?></td>

<td><?= htmlspecialchars($row['member_name']) ?></td>

<td><?= number_format($wallet['monthly_amount'],2) ?></td>

<td><?= number_format($row['total_paid'],2) ?></td>

<td><?= number_format($balance,2) ?></td>

<td>

<a
href="member_history.php?wallet=<?= $wallet_id ?>&member=<?= $row['member_user_id'] ?>"
class="btn btn-primary btn-sm">

<?= __('history') ?>

</a>

<a
href="remove_member.php?id=<?= $row['id'] ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('<?= __('remove_member_confirm') ?>')">

<?= __('remove') ?>

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