<?php

require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/language.php';
require_once '../config/auth.php';

requireAnyRoleAccess(['Administrator', 'Chairperson']);

$database = new Database();
$pdo = $database->connect();

$clanId = getCurrentClanId();

$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT *
    FROM meetings
    WHERE meeting_id = ?
      AND clan_id = ?
    LIMIT 1
");

$stmt->execute([$id, $clanId]);

$meeting = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$meeting) {
    header('Location: index.php');
    exit;
}

if ($meeting['meeting_status'] === 'live') {
    header('Location: index.php');
    exit;
}

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

<i class="fas fa-edit text-primary mr-2"></i>

<?= __('edit_meeting') ?>

</h3>

</div>

<form action="update.php" method="POST">

<input
type="hidden"
name="meeting_id"
value="<?= (int) $meeting['meeting_id'] ?>">

<div class="card-body">

<div class="form-group">

<label><?= __('meeting_title') ?></label>

<input
type="text"
name="title"
class="form-control"
required
maxlength="255"
value="<?= htmlspecialchars($meeting['title'] ?? '') ?>">

</div>

<div class="form-group">

<label><?= __('description') ?></label>

<textarea
name="description"
rows="4"
class="form-control"><?= htmlspecialchars(
    $meeting['description'] ?? ''
) ?></textarea>

</div>

<div class="row">

<div class="col-md-4">

<div class="form-group">

<label><?= __('meeting_date') ?></label>

<input
type="date"
name="meeting_date"
class="form-control"
required
value="<?= htmlspecialchars($meeting['meeting_date'] ?? '') ?>">

</div>

</div>

<div class="col-md-4">

<div class="form-group">

<label><?= __('start_time') ?></label>

<input
type="time"
name="start_time"
class="form-control"
value="<?= htmlspecialchars($meeting['start_time'] ?? '') ?>">

</div>

</div>

<div class="col-md-4">

<div class="form-group">

<label><?= __('end_time') ?></label>

<input
type="time"
name="end_time"
class="form-control"
value="<?= htmlspecialchars($meeting['end_time'] ?? '') ?>">

</div>

</div>

</div>

<p class="mb-0">

<i class="fas fa-link text-primary mr-1"></i>

<strong><?= __('room_code') ?>:</strong>

<span class="text-monospace">

<?= htmlspecialchars($meeting['room_code'] ?? '') ?>

</span>

</p>

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