Contribution not found.<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database = new Database();
$pdo = $database->connect();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
SELECT *
FROM event_contributions
WHERE contribution_id=?
LIMIT 1
");
$stmt->execute([$id]);

$contribution = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$contribution){
    die(__('contribution_not_found'));
}

$events = $pdo->query("
SELECT event_id,title
FROM events
ORDER BY event_date DESC
")->fetchAll(PDO::FETCH_ASSOC);

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

<?= __('edit_contribution') ?>

</h3>

</div>

<form action="update.php" method="POST">

<input
type="hidden"
name="contribution_id"
value="<?= $contribution['contribution_id'] ?>">

<div class="card-body">

<div class="form-group">

<label><?= __('event') ?></label>

<select
name="event_id"
class="form-control">

<?php foreach($events as $event): ?>

<option
value="<?= $event['event_id'] ?>"
<?= $event['event_id']==$contribution['event_id']?'selected':'' ?>>

<?= htmlspecialchars($event['title']) ?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="form-group">

<label><?= __('contribution_title') ?></label>

<input
type="text"
name="title"
class="form-control"
value="<?= htmlspecialchars($contribution['title']) ?>"
required>

</div>

<div class="form-group">

<label><?= __('description') ?></label>

<textarea
name="description"
rows="4"
class="form-control"><?= htmlspecialchars($contribution['description']) ?></textarea>

</div>

<div class="row">

<div class="col-md-6">

<label><?= __('required_amount') ?></label>

<input
type="number"
name="required_amount"
class="form-control"
value="<?= $contribution['required_amount'] ?>"
required>

</div>

<div class="col-md-6">

<label><?= __('deadline') ?></label>

<input
type="date"
name="deadline"
class="form-control"
value="<?= $contribution['deadline'] ?>">

</div>

</div>

<br>

<div class="form-group">

<label><?= __('status') ?></label>

<select
name="status"
class="form-control">

<option value="Open"
<?= $contribution['status']=='Open'?'selected':'' ?>>

<?= __('opened') ?>

</option>

<option value="Closed"
<?= $contribution['status']=='Closed'?'selected':'' ?>>

<?= __('closed') ?>

</option>

</select>

</div>

<div class="form-group">

<label>

<input
type="checkbox"
name="allow_partial_payment"
value="1"
<?= $contribution['allow_partial_payment']?'checked':'' ?>>

<?= __('allow_partial_payment') ?>

</label>

</div>

</div>

<div class="card-footer">

<button class="btn btn-success">

<?= __('update_contribution') ?>

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

<div class="modal fade" id="paymentMethodModal">

<div class="modal-dialog">

<div class="modal-content">

<div class="modal-header">

<h5 class="modal-title">

<?= __('add_payment_method') ?>

</h5>

<button
type="button"
class="close"
data-dismiss="modal">

&times;

</button>

</div>

<form
action="save_payment_method.php"
method="POST">

<div class="modal-body">

<div class="form-group">

<label><?= __('method_name') ?></label>

<input
type="text"
name="method_name"
class="form-control"
required>

</div>

<div class="form-group">

<label><?= __('provider_short') ?></label>

<input
type="text"
name="provider_name"
class="form-control">

</div>

<div class="form-group">

<label><?= __('phone_number') ?></label>

<input
type="text"
name="phone_number"
class="form-control">

</div>

<div class="form-group">

<label><?= __('account_name') ?></label>

<input
type="text"
name="account_name"
class="form-control">

</div>

<div class="form-group">

<label><?= __('account_number') ?></label>

<input
type="text"
name="account_number"
class="form-control">

</div>

</div>

<div class="modal-footer">

<button
class="btn btn-success">

<?= __('save') ?>

</button>

</div>

</form>

</div>

</div>

</div>

<?php
require_once '../../includes/dashboard/footer.php';