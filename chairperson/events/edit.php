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

<?= __('edit_event') ?>

</h3>

</div>

<form action="update.php" method="POST" enctype="multipart/form-data">

<input
type="hidden"
name="event_id"
value="<?= $event['event_id'] ?>">

<div class="card-body">

<div class="form-group">

<label><?= __('event_title') ?></label>

<input
type="text"
name="title"
class="form-control"
value="<?= htmlspecialchars($event['title']) ?>"
required>

</div>

<div class="form-group">

<label><?= __('description') ?></label>

<textarea
name="description"
rows="5"
class="form-control"><?= htmlspecialchars($event['description']) ?></textarea>

</div>

<div class="row">

<div class="col-md-4">

<label><?= __('date') ?></label>

<input
type="date"
name="event_date"
class="form-control"
value="<?= $event['event_date'] ?>">

</div>

<div class="col-md-4">

<label><?= __('start_time') ?></label>

<input
type="time"
name="start_time"
class="form-control"
value="<?= $event['start_time'] ?>">

</div>

<div class="col-md-4">

<label><?= __('end_time') ?></label>

<input
type="time"
name="end_time"
class="form-control"
value="<?= $event['end_time'] ?>">

</div>

</div>

<br>

<div class="form-group">

<label><?= __('location') ?></label>

<input
type="text"
name="location"
class="form-control"
value="<?= htmlspecialchars($event['location']) ?>">

</div>

<?php if(!empty($event['event_image'])): ?>

<img
src="<?= BASE_URL ?>uploads/events/<?= $event['event_image'] ?>"
class="img-thumbnail mb-3"
style="max-width:250px;">

<?php endif; ?>

<div class="form-group">

<label><?= __('replace_poster') ?></label>

<input
type="file"
name="event_image"
class="form-control"
accept=".jpg,.jpeg,.png">

</div>

<div class="form-group">

<label><?= __('status') ?></label>

<select
name="event_status"
class="form-control">

<?php

$status = [
'upcoming',
'ongoing',
'completed',
'cancelled'
];

foreach($status as $s):

?>

<option
value="<?= $s ?>"
<?= $event['event_status']==$s?'selected':'' ?>>

<?= __($s) ?>

</option>

<?php endforeach; ?>

</select>

</div>

</div>

<div class="card-footer">

<button
class="btn btn-success">

<?= __('update_event') ?>

</button>

<a
href="index.php"
class="btn btn-secondary">

<?= __('cancel') ?>

</a>

</div>

</form>

</div>

</div>

</section>

<?php
require_once '../../includes/dashboard/footer.php';