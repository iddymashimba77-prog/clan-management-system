<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database = new Database();
$pdo = $database->connect();

$contribution_id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
SELECT

ec.*,

e.title AS event_name,

CONCAT(u.first_name,' ',u.last_name) AS created_by_name

FROM event_contributions ec

INNER JOIN events e
ON ec.event_id=e.event_id

INNER JOIN users u
ON ec.created_by=u.user_id

WHERE ec.contribution_id=?

LIMIT 1
");

$stmt->execute([$contribution_id]);

$contribution = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$contribution){
    die(__('contribution_not_found'));
}

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

<?= __('contribution_details') ?>

</h3>

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>

<th><?= __('event') ?></th>

<td><?= htmlspecialchars($contribution['event_name']) ?></td>

</tr>

<tr>

<th><?= __('contribution') ?></th>

<td><?= htmlspecialchars($contribution['title']) ?></td>

</tr>

<tr>

<th><?= __('description') ?></th>

<td><?= nl2br(htmlspecialchars($contribution['description'])) ?></td>

</tr>

<tr>

<th><?= __('required_amount') ?></th>

<td>

<strong>

TZS <?= number_format($contribution['required_amount'],2) ?>

</strong>

</td>

</tr>

<tr>

<th><?= __('deadline') ?></th>

<td><?= $contribution['deadline'] ?></td>

</tr>

<tr>

<th><?= __('status') ?></th>

<td>

<span class="badge badge-success">

<?= $contribution['status'] ?>

</span>

</td>

</tr>

<tr>

<th><?= __('allow_partial_payment') ?></th>

<td>

<?= $contribution['allow_partial_payment'] ? __('yes') : __('no') ?>

</td>

</tr>

<tr>

<th><?= __('created_by') ?></th>

<td><?= htmlspecialchars($contribution['created_by_name']) ?></td>

</tr>

<tr>

<th><?= __('created_at_label') ?></th>

<td><?= $contribution['created_at'] ?></td>

</tr>

</table>

<a
href="edit.php?id=<?= $contribution['contribution_id'] ?>"
class="btn btn-warning">

<?= __('edit') ?>

</a>

<a
href="index.php"
class="btn btn-secondary">

<?= __('back') ?>

</a>

</div>

</div>

</div>

</section>

<?php
require_once '../../includes/dashboard/footer.php';