<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database = new Database();
$pdo = $database->connect();

$event_id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
SELECT *
FROM events
WHERE event_id=?
LIMIT 1
");

$stmt->execute([$event_id]);

$event = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$event){
    die(__('event_not_found'));
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

<?= __('event_details') ?>

</h3>

</div>

<div class="card-body">

<?php if(!empty($event['event_image'])): ?>

<img
src="<?= BASE_URL ?>uploads/events/<?= $event['event_image'] ?>"
class="img-fluid img-thumbnail mb-3"
style="max-width:450px;">

<?php endif; ?>

<table class="table table-bordered">

<tr>
<th><?= __('title') ?></th>
<td><?= htmlspecialchars($event['title']) ?></td>
</tr>

<tr>
<th><?= __('description') ?></th>
<td><?= nl2br(htmlspecialchars($event['description'])) ?></td>
</tr>

<tr>
<th><?= __('date') ?></th>
<td><?= $event['event_date'] ?></td>
</tr>

<tr>
<th><?= __('start_time') ?></th>
<td><?= $event['start_time'] ?></td>
</tr>

<tr>
<th><?= __('end_time') ?></th>
<td><?= $event['end_time'] ?></td>
</tr>

<tr>
<th><?= __('location') ?></th>
<td><?= htmlspecialchars($event['location']) ?></td>
</tr>

<tr>
<th><?= __('status') ?></th>
<td><?= __($event['event_status']) ?></td>
</tr>

</table>

<a href="edit.php?id=<?= $event['event_id'] ?>"
class="btn btn-warning">

<?= __('edit') ?>

</a>

<a href="index.php"
class="btn btn-secondary">

<?= __('back') ?>

</a>

</div>

</div>

</div>

</section>

<?php
require_once '../../includes/dashboard/footer.php';