<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    die('Invalid event ID.');
}

$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->prepare("
    SELECT
        e.*,
        CONCAT(
            COALESCE(u.first_name, ''),
            ' ',
            COALESCE(u.last_name, '')
        ) AS creator_name
    FROM events e
    LEFT JOIN users u
        ON u.user_id = e.created_by_user_id
    WHERE e.event_id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    die('Event not found.');
}

$stmt = $pdo->prepare("
    SELECT *
    FROM event_contributions
    WHERE event_id = ?
    ORDER BY created_at DESC
");

$stmt->execute([$id]);

$contributions = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebar.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">

<div class="card clan-page-card">

<div class="card-header">

<h3 class="card-title">
<i class="fas fa-calendar-check mr-2"></i>
<?= __('event_details') ?>
</h3>

</div>

<div class="card-body">

<h3><?= htmlspecialchars($event['title']); ?></h3>

<p>
<?= nl2br(htmlspecialchars($event['description'] ?? '')); ?>
</p>

<table class="table table-bordered">

<tr>
<th><?= __('date') ?></th>
<td><?= htmlspecialchars($event['event_date']); ?></td>
</tr>

<tr>
<th><?= __('time') ?></th>
<td>
<?= htmlspecialchars($event['start_time'] ?? ''); ?>
-
<?= htmlspecialchars($event['end_time'] ?? ''); ?>
</td>
</tr>

<tr>
<th><?= __('location') ?></th>
<td><?= htmlspecialchars($event['location'] ?? ''); ?></td>
</tr>

<tr>
<th><?= __('status') ?></th>
<td><?= htmlspecialchars($event['event_status']); ?></td>
</tr>

<tr>
<th>Created By</th>
<td><?= htmlspecialchars($event['creator_name'] ?? ''); ?></td>
</tr>

</table>

<hr>

<h5>Event Contributions</h5>

<div class="table-responsive">

<table class="table table-bordered table-striped">

<thead>

<tr>
<th><?= __('title') ?></th>
<th><?= __('required_amount') ?></th>
<th><?= __('deadline') ?></th>
<th><?= __('status') ?></th>
</tr>

</thead>

<tbody>

<?php foreach ($contributions as $item): ?>

<tr>

<td><?= htmlspecialchars($item['title']); ?></td>

<td><?= number_format($item['required_amount'], 2); ?></td>

<td><?= htmlspecialchars($item['deadline'] ?? ''); ?></td>

<td><?= htmlspecialchars($item['status']); ?></td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<a href="events.php"
   class="btn btn-secondary">
<?= __('back') ?>
</a>

</div>

</div>

</div>

</section>

</div>

<?php require_once '../../includes/dashboard/footer.php'; ?>