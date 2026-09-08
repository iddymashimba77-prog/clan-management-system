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
event_id,
title,
event_date,
location,
event_status
FROM events
ORDER BY event_date DESC
");

$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

<?= __('manage_events') ?>

</h3>

<div class="card-tools">

<a href="create.php" class="btn btn-primary">

<i class="fas fa-plus"></i>

<?= __('new_event') ?>

</a>

</div>

</div>

<div class="card-body">

<table class="table table-bordered table-striped">

<thead>

<tr>

<th><?= __('title') ?></th>
<th><?= __('date') ?></th>
<th><?= __('location') ?></th>
<th><?= __('status') ?></th>
<th width="220"><?= __('action') ?></th>

</tr>

</thead>

<tbody>

<?php foreach($events as $event): ?>

<tr>

<td><?= htmlspecialchars($event['title']) ?></td>

<td><?= $event['event_date'] ?></td>

<td><?= htmlspecialchars($event['location']) ?></td>

<td>

<span class="badge badge-info">

<?= ucfirst($event['event_status']) ?>

</span>

</td>

<td>

<a href="view.php?id=<?= $event['event_id'] ?>"
class="btn btn-info btn-sm">

<?= __('view') ?>

</a>

<a href="edit.php?id=<?= $event['event_id'] ?>"
class="btn btn-warning btn-sm">

<?= __('edit') ?>

</a>

<a
href="delete.php?id=<?= $event['event_id'] ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('<?= __('delete_this_event') ?>')">

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