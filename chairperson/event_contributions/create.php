<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database = new Database();
$pdo = $database->connect();

$clanId = (int)($_SESSION['clan_id'] ?? 1);

$events = $pdo->query("
SELECT event_id,title
FROM events
WHERE event_status IN('upcoming','ongoing')
ORDER BY event_date ASC
")->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
SELECT *
FROM payment_methods
WHERE status='active'
AND clan_id=?
ORDER BY method_name ASC
");
$stmt->execute([$clanId]);
$methods = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

<?= __('create_contribution') ?>

</h3>

</div>

<?php if(isset($_GET['method_added'])): ?>

<div class="alert alert-success">

<?= __('payment_method_added_successfully') ?>

</div>

<?php endif; ?>

<form action="store.php" method="POST">

<div class="card-body">

<div class="form-group">

<label><?= __('select_event') ?></label>

<select
name="event_id"
class="form-control"
required>

<option value=""><?= __('select_event_placeholder') ?></option>

<?php foreach($events as $event): ?>

<option value="<?= $event['event_id'] ?>">

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
required>

</div>

<div class="form-group">

<label><?= __('description') ?></label>

<textarea
name="description"
rows="4"
class="form-control"></textarea>

</div>

<div class="row">

<div class="col-md-6">

<label><?= __('required_amount_tzs') ?></label>

<input
type="number"
name="required_amount"
class="form-control"
min="1"
required>

</div>

<div class="col-md-6">

<label><?= __('deadline') ?></label>

<input
type="date"
name="deadline"
class="form-control"
required>

</div>

</div>

<br>

<div class="form-group">

<label><?= __('status') ?></label>

<select
name="status"
class="form-control">

<option value="Open"><?= __('opened') ?></option>

<option value="Closed"><?= __('closed') ?></option>

</select>

</div>

</div>

<div class="d-flex justify-content-between align-items-center mb-3">

<h5>

<i class="fas fa-credit-card"></i>

<?= __('select_payment_methods') ?>

</h5>

<button
type="button"
class="btn btn-success btn-sm"
data-toggle="modal"
data-target="#paymentMethodModal">

<i class="fas fa-plus"></i>

<?= __('add_new_method') ?>

</button>

</div>

<div class="card bg-light mb-3">

<div class="card-body">

<?php if(!empty($methods)): ?>

<div class="row">

<?php foreach($methods as $method): ?>

<div class="col-md-6 mb-2">

<div class="custom-control custom-checkbox">

<input
type="checkbox"
class="custom-control-input"
name="payment_methods[]"
value="<?= $method['payment_method_id'] ?>"
id="method_<?= $method['payment_method_id'] ?>">

<label
class="custom-control-label"
for="method_<?= $method['payment_method_id'] ?>">

<strong><?= htmlspecialchars($method['method_name']) ?></strong>

<?php if(!empty($method['provider_name'])): ?>

- <?= htmlspecialchars($method['provider_name']) ?>

<?php endif; ?>

<?php if(!empty($method['phone_number'])): ?>

<br><small><?= htmlspecialchars($method['phone_number']) ?></small>

<?php endif; ?>

<?php if(!empty($method['account_name'])): ?>

<br><small><?= htmlspecialchars($method['account_name']) ?></small>

<?php endif; ?>

<?php if(!empty($method['account_number'])): ?>

<br><small><?= htmlspecialchars($method['account_number']) ?></small>

<?php endif; ?>

</label>

</div>

</div>

<?php endforeach; ?>

</div>

<?php else: ?>

<p class="text-muted"><?= __('no_payment_methods_available_add') ?></p>

<?php endif; ?>

</div>

</div>



<div class="card-footer">

<button
type="submit"
class="btn btn-success">

<i class="fas fa-save"></i>

<?= __('save_contribution') ?>

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