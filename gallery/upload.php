<?php

require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/language.php';
require_once '../config/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $database = new Database();
    $pdo = $database->connect();

    $clanId = getCurrentClanId();
    $user_id = (int) $_SESSION['user_id'];

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

    $stmt->execute([$user_id, $clanId]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    define(
        'USER_ROLE',
        $user['role_name'] ?? 'Member'
    );

    require_once '../includes/dashboard/header.php';

    $role = strtolower(
        trim($user['role_name'] ?? 'member')
    );

    if ($role === 'chairperson') {

        require_once
            '../includes/dashboard/sidebars/chairperson.php';

    } elseif ($role === 'treasurer') {

        require_once
            '../includes/dashboard/sidebars/treasurer.php';

    } elseif ($role === 'secretary') {

        require_once
            '../includes/dashboard/sidebars/secretary.php';

    } else {

        require_once
            '../includes/dashboard/sidebars/member.php';
    }

    require_once '../includes/dashboard/navbar.php';
?>

<div class="content-wrapper">

<section class="content gallery-page">

<div class="container-fluid">

<div class="card">

<div class="card-header">

<h3 class="card-title">

<?= __('upload_photo') ?>

</h3>

</div>

<div class="card-body">

<form
action=""
method="POST"
enctype="multipart/form-data">

<div class="form-group">

<label><?= __('title') ?></label>

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
class="form-control"></textarea>

</div>

<div class="form-group">

<label><?= __('select_image') ?></label>

<input
type="file"
name="photo"
class="form-control"
accept=".jpg,.jpeg,.png,.webp"
required>

</div>

<button
type="submit"
class="btn btn-success">

<i class="fas fa-upload"></i>

<?= __('upload') ?>

</button>

<a
href="index.php"
class="btn btn-secondary">

<?= __('cancel') ?>

</a>

</form>

</div>

</div>

</div>

</section>

</div>

<?php

require_once '../includes/dashboard/footer.php';

exit;

}

$database = new Database();
$pdo = $database->connect();

$clanId = getCurrentClanId();

$title = trim($_POST['title']);
$description = trim($_POST['description']);

if (!isset($_FILES['photo'])) {
    die(__('no_file_selected'));
}

$file = $_FILES['photo'];

if ($file['error'] != 0) {
    die(__('upload_failed'));
}

$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

$allowed = ['jpg','jpeg','png','webp'];

if (!in_array($extension, $allowed)) {
    die(__('invalid_image_type'));
}

$newName = uniqid('IMG_') . "." . $extension;

$destination = __DIR__ . "/uploads/" . $newName;

move_uploaded_file($file['tmp_name'], $destination);

$stmt = $pdo->prepare("
INSERT INTO gallery
(
user_id,
title,
description,
file_name,
clan_id
)
VALUES
(?,?,?,?,?)
");

$stmt->execute([
    $_SESSION['user_id'],
    $title,
    $description,
    $newName,
    $clanId
]);

header("Location:index.php");

exit;