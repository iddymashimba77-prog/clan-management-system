<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$pdo = $database->connect();

$total = (int)$pdo->query("
    SELECT COUNT(*) FROM events
")->fetchColumn();

$upcoming = (int)$pdo->query("
    SELECT COUNT(*)
    FROM events
    WHERE event_status = 'upcoming'
")->fetchColumn();

$ongoing = (int)$pdo->query("
    SELECT COUNT(*)
    FROM events
    WHERE event_status = 'ongoing'
")->fetchColumn();

$completed = (int)$pdo->query("
    SELECT COUNT(*)
    FROM events
    WHERE event_status = 'completed'
")->fetchColumn();

$events = $pdo->query("
    SELECT *
    FROM events
    ORDER BY event_date DESC, start_time DESC
")->fetchAll(PDO::FETCH_ASSOC);

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/admin.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">
<section class="content">
<div class="container-fluid clan-page-content">

<div class="card clan-page-card">

<div class="card-header d-flex justify-content-between">

<h4 class="mb-0">
<i class="fas fa-calendar-alt mr-2 text-warning"></i>
Events Report
</h4>

<a href="export.php?report=events"
class="btn btn-success btn-sm">
<i class="fas fa-download mr-1"></i>
Export
</a>

</div>

<div class="card-body">

<div class="row">

<div class="col-md-3">
<div class="info-box">
<span class="info-box-icon bg-primary">
<i class="fas fa-calendar"></i>
</span>
<div class="info-box-content">
<span class="info-box-text"><?= __('total') ?></span>
<span class="info-box-number"><?= $total ?></span>
</div>
</div>
</div>

<div class="col-md-3">
<div class="info-box">
<span class="info-box-icon bg-info">
<i class="fas fa-clock"></i>
</span>
<div class="info-box-content">
<span class="info-box-text"><?= __('upcoming') ?></span>
<span class="info-box-number"><?= $upcoming ?></span>
</div>
</div>
</div>

<div class="col-md-3">
<div class="info-box">
<span class="info-box-icon bg-warning">
<i class="fas fa-play"></i>
</span>
<div class="info-box-content">
<span class="info-box-text"><?= __('ongoing') ?></span>
<span class="info-box-number"><?= $ongoing ?></span>
</div>
</div>
</div>

<div class="col-md-3">
<div class="info-box">
<span class="info-box-icon bg-success">
<i class="fas fa-check"></i>
</span>
<div class="info-box-content">
<span class="info-box-text"><?= __('completed') ?></span>
<span class="info-box-number"><?= $completed ?></span>
</div>
</div>
</div>

</div>

<div class="table-responsive">

<table class="table table-bordered">

<thead>
<tr>
<th>#</th>
<th><?= __('event') ?></th>
<th><?= __('date') ?></th>
<th><?= __('time') ?></th>
<th><?= __('location') ?></th>
<th><?= __('status') ?></th>
</tr>
</thead>

<tbody>

<?php foreach ($events as $i => $event): ?>

<tr>

<td><?= $i + 1 ?></td>

<td><?= htmlspecialchars($event['title']) ?></td>

<td><?= htmlspecialchars($event['event_date']) ?></td>

<td>
<?= htmlspecialchars($event['start_time'] ?? '-') ?>
-
<?= htmlspecialchars($event['end_time'] ?? '-') ?>
</td>

<td><?= htmlspecialchars($event['location'] ?? '-') ?></td>

<td>
<span class="badge badge-primary">
<?= htmlspecialchars($event['event_status']) ?>
</span>
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

<?php require_once '../../includes/dashboard/footer.php'; ?>