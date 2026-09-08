<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->query("
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
    ORDER BY e.event_date DESC, e.start_time DESC
");

$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
<i class="fas fa-calendar-alt mr-2"></i>
<?= __('events') ?>
</h3>

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-striped">

<thead>

<tr>
<th>ID</th>
<th><?= __('title') ?></th>
<th><?= __('date') ?></th>
<th><?= __('time') ?></th>
<th><?= __('location') ?></th>
<th><?= __('status') ?></th>
<th>Created By</th>
<th><?= __('action') ?></th>
</tr>

</thead>

<tbody>

<?php foreach ($events as $event): ?>

<tr>

<td><?= (int)$event['event_id']; ?></td>

<td><?= htmlspecialchars($event['title']); ?></td>

<td><?= htmlspecialchars($event['event_date']); ?></td>

<td>
<?= htmlspecialchars(
    ($event['start_time'] ?? '') .
    ' - ' .
    ($event['end_time'] ?? '')
); ?>
</td>

<td><?= htmlspecialchars($event['location'] ?? ''); ?></td>

<td><?= htmlspecialchars($event['event_status']); ?></td>

<td><?= htmlspecialchars($event['creator_name'] ?? ''); ?></td>

<td>

<a href="event_view.php?id=<?= (int)$event['event_id']; ?>"
   class="btn btn-info btn-sm">

<i class="fas fa-eye"></i>

</a>

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