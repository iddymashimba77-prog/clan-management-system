<?php

require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/language.php';
require_once '../config/auth.php';

requireAnyRoleAccess(['Administrator', 'Chairperson']);

$database = new Database();
$pdo = $database->connect();

$clanId = getCurrentClanId();

$stmt = $pdo->prepare("
    SELECT
        u.*,
        r.role_name
    FROM users u
    LEFT JOIN roles r
        ON u.role_id = r.role_id
    WHERE u.user_id = ?
      AND u.clan_id = ?
    LIMIT 1
");

$stmt->execute([$_SESSION['user_id'], $clanId]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

$roleName = strtolower(
    trim($user['role_name'] ?? 'Member')
);

define('USER_ROLE', $user['role_name'] ?? 'Member');

require_once '../includes/dashboard/header.php';

if ($roleName === 'administrator') {

    require_once '../includes/dashboard/sidebars/admin.php';

} else {

    require_once '../includes/dashboard/sidebars/chairperson.php';
}

require_once '../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid" style="max-width: 820px; margin: 0 auto;">

<div class="card">

<div class="card-header">

<h3>

<i class="fas fa-video text-primary mr-2"></i>

<?= __('new_meeting') ?>

</h3>

</div>

<form action="store.php" method="POST">

<div class="card-body">

<?php if (isset($_GET['error'])): ?>

<div class="alert alert-danger">

<?= __('meeting_form_error') ?>

</div>

<?php endif; ?>

<div class="form-group">

<label><?= __('meeting_title') ?></label>

<input
type="text"
name="title"
class="form-control"
required
maxlength="255"
placeholder="<?= __('meeting_title_placeholder') ?>">

</div>

<div class="form-group">

<label><?= __('description') ?></label>

<textarea
name="description"
rows="4"
class="form-control"></textarea>

</div>

<div class="row">

<div class="col-md-4">

<div class="form-group">

<label><?= __('meeting_date') ?></label>

<input
type="date"
name="meeting_date"
class="form-control"
required>

</div>

</div>

<div class="col-md-4">

<div class="form-group">

<label><?= __('start_time') ?></label>

<input
type="time"
name="start_time"
class="form-control">

</div>

</div>

<div class="col-md-4">

<div class="form-group">

<label><?= __('end_time') ?></label>

<input
type="time"
name="end_time"
class="form-control">

</div>

</div>

</div>

<div class="alert alert-light border">

<i class="fas fa-info-circle text-primary mr-1"></i>

<?= __('meeting_link_info') ?>

</div>

</div>

<div class="card-footer">

<button
type="submit"
class="btn btn-success">

<i class="fas fa-save"></i>

<?= __('save_meeting') ?>

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

</div>

<?php
require_once '../includes/dashboard/footer.php';