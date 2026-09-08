<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database = new Database();
$pdo = $database->connect();

$stmt=$pdo->query("
SELECT

ec.contribution_id,
ec.title,
ec.required_amount,
ec.deadline,
ec.status,

e.title AS event_name

FROM event_contributions ec

INNER JOIN events e
ON ec.event_id=e.event_id

ORDER BY ec.deadline DESC
");

$contributions=$stmt->fetchAll(PDO::FETCH_ASSOC);

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

<?= __('manage_event_contributions') ?>

</h3>

<div class="card-tools">

<a href="create.php" class="btn btn-primary">

<i class="fas fa-plus"></i>

<?= __('new_contribution') ?>

</a>

</div>

</div>

<div class="card-body">

<table class="table table-bordered table-striped">

<thead>

<tr>

<th><?= __('event') ?></th>
<th><?= __('contribution') ?></th>
<th><?= __('amount') ?></th>
<th><?= __('deadline') ?></th>
<th><?= __('status') ?></th>
<th width="220"><?= __('action') ?></th>

</tr>

</thead>

<tbody>

<?php foreach($contributions as $row): ?>

<tr>

<td><?= htmlspecialchars($row['event_name']) ?></td>

<td><?= htmlspecialchars($row['title']) ?></td>

<td>

TZS <?= number_format($row['required_amount'],2) ?>

</td>

<td><?= $row['deadline'] ?></td>

<td>

<span class="badge badge-success">

<?= $row['status'] ?>

</span>

</td>

<td>

<a
href="view.php?id=<?= $row['contribution_id'] ?>"
class="btn btn-info btn-sm">

<?= __('view') ?>

</a>

<a
href="edit.php?id=<?= $row['contribution_id'] ?>"
class="btn btn-warning btn-sm">

<?= __('edit') ?>

</a>

<a
href="delete.php?id=<?= $row['contribution_id'] ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('<?= __('delete_contribution_confirm') ?>')">

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