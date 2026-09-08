<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireMember();

$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->prepare("
SELECT

cp.payment_id,
cp.amount,
cp.payment_date,
cp.reference_number,

ec.title AS contribution_title,
e.title AS event_name

FROM contribution_payments cp

INNER JOIN event_contributions ec
ON cp.contribution_id=ec.contribution_id

INNER JOIN events e
ON ec.event_id=e.event_id

WHERE

cp.member_user_id=?

AND cp.payment_status='approved'

ORDER BY cp.payment_date DESC
");

$stmt->execute([$_SESSION['user_id']]);

$receipts = $stmt->fetchAll(PDO::FETCH_ASSOC);

define('USER_ROLE','Member');

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/member.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid" style="max-width: 1200px; margin: 0 auto;">

<div class="card">

<div class="card-header">

<h3 class="card-title">

<?= __('my_receipts') ?>

</h3>

</div>

<div class="card-body">

<table class="table table-bordered table-striped">

<thead>

<tr>

<th><?= __('reference') ?></th>
<th><?= __('event') ?></th>
<th><?= __('contribution') ?></th>
<th><?= __('amount') ?></th>
<th><?= __('date') ?></th>
<th><?= __('action') ?></th>

</tr>

</thead>

<tbody>

<?php foreach($receipts as $row): ?>

<tr>

<td><?= $row['reference_number'] ?></td>

<td><?= htmlspecialchars($row['event_name']) ?></td>

<td><?= htmlspecialchars($row['contribution_title']) ?></td>

<td>TZS <?= number_format($row['amount'],2) ?></td>

<td><?= $row['payment_date'] ?></td>

<td>

<a
href="view.php?id=<?= $row['payment_id'] ?>"
class="btn btn-info btn-sm">

<?= __('view_receipt') ?>

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