<?php
ini_set('display_startup_errors',1);

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database = new Database();
$pdo = $database->connect();

$type = $_GET['type'] ?? 'my';

if($type == 'others'){

$stmt=$pdo->prepare("
SELECT

cp.payment_id,
u.member_code,
CONCAT(u.first_name,' ',u.last_name) AS member_name,

e.title AS event_name,
ec.title AS contribution_title,

cp.amount,
cp.payment_date,
cp.reference_number,
cp.payment_status

FROM contribution_payments cp

INNER JOIN users u
ON cp.member_user_id=u.user_id

INNER JOIN event_contributions ec
ON cp.contribution_id=ec.contribution_id

INNER JOIN events e
ON ec.event_id=e.event_id

WHERE cp.member_user_id <> ?

ORDER BY cp.payment_date DESC
");

}else{

$stmt=$pdo->prepare("
SELECT

cp.payment_id,
u.member_code,
CONCAT(u.first_name,' ',u.last_name) AS member_name,

e.title AS event_name,
ec.title AS contribution_title,

cp.amount,
cp.payment_date,
cp.reference_number,
cp.payment_status

FROM contribution_payments cp

INNER JOIN users u
ON cp.member_user_id=u.user_id

INNER JOIN event_contributions ec
ON cp.contribution_id=ec.contribution_id

INNER JOIN events e
ON ec.event_id=e.event_id

WHERE cp.member_user_id = ?

ORDER BY cp.payment_date DESC
");

}

$stmt->execute([$_SESSION['user_id']]);

$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

<?= __('final_approval') ?>

</h3>

</div>

<div class="card-body">

<div class="mb-3">

<a href="?type=my"
class="btn <?= $type=='my'?'btn-primary':'btn-outline-primary' ?>">

<?= __('my_contributions_tab') ?>

</a>

<a href="?type=others"
class="btn <?= $type=='others'?'btn-success':'btn-outline-success' ?>">

<?= __('other_contributions_tab') ?>

</a>

</div>

<table class="table table-bordered table-striped">

<thead>

<tr>

<th><?= __('member_code') ?></th>
<th><?= __('member') ?></th>
<th><?= __('event') ?></th>
<th><?= __('contribution') ?></th>
<th><?= __('amount') ?></th>
<th><?= __('date') ?></th>
<th><?= __('action') ?></th>

</tr>

</thead>

<tbody>

<?php foreach($payments as $row): ?>

<tr>

<td><?= $row['member_code'] ?></td>

<td><?= htmlspecialchars($row['member_name']) ?></td>

<td><?= htmlspecialchars($row['event_name']) ?></td>

<td><?= htmlspecialchars($row['contribution_title']) ?></td>

<td>TZS <?= number_format($row['amount'],2) ?></td>

<td><?= $row['payment_date'] ?></td>

<td>

<a href="view.php?id=<?= $row['payment_id'] ?>"
class="btn btn-primary btn-sm">

<?= __('review') ?>

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