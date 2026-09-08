<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database = new Database();
$pdo = $database->connect();

$roles = $pdo->query("
    SELECT role_id, role_name
    FROM roles
    ORDER BY role_id
")->fetchAll(PDO::FETCH_ASSOC);

define('USER_ROLE','Chairperson');

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/chairperson.php';
require_once '../../includes/dashboard/navbar.php';

?>
<?php if (isset($_GET['success']) && $_GET['success'] === 'member_added'): ?>

<div class="container-fluid" style="max-width: 1200px; margin: 20px auto 0;">

    <div class="alert alert-success alert-dismissible fade show" role="alert">

        <i class="fas fa-check-circle mr-2"></i>

        <strong><?= __('success_punct') ?></strong>
        <?= __('family_member_added_successfully') ?>

        <button
            type="button"
            class="close"
            data-dismiss="alert"
            aria-label="Close"
        >
            <span aria-hidden="true">&times;</span>
        </button>

    </div>

</div>

<?php endif; ?>


<div class="content-wrapper">

<section class="content">

<div class="container-fluid" style="max-width: 1200px; margin: 0 auto;">

<div class="card">

<div class="card-header">

<h3 class="card-title">

<?= __('add_family_member') ?>

</h3>

<div class="card-tools">

<a href="index.php" class="btn btn-secondary">

<i class="fas fa-arrow-left"></i>
<?= __('back') ?>

</a>

</div>

</div>

<div class="card-body">

<form action="store.php" method="POST" enctype="multipart/form-data">

<div class="row">

<div class="col-md-6">

<div class="form-group">

<label><?= __('first_name') ?></label>

<input type="text" name="first_name" class="form-control" required>

</div>

</div>

<div class="col-md-6">

<div class="form-group">

<label><?= __('middle_name') ?></label>

<input type="text" name="middle_name" class="form-control">

</div>

</div>

</div>

<div class="row">

<div class="col-md-6">

<div class="form-group">

<label><?= __('last_name') ?></label>

<input type="text" name="last_name" class="form-control" required>

</div>

</div>

<div class="col-md-6">

<div class="form-group">

<label><?= __('gender') ?></label>

<select name="gender" class="form-control" required>

<option value=""><?= __('select') ?></option>
<option value="Male"><?= __('male') ?></option>
<option value="Female"><?= __('female') ?></option>

</select>

</div>

</div>

</div>

<div class="row">

<div class="col-md-6">

<div class="form-group">

<label><?= __('date_of_birth') ?></label>

<input type="date" name="date_of_birth" class="form-control">

</div>

</div>

<div class="col-md-6">

<div class="form-group">

<label><?= __('phone') ?></label>

<input type="text" name="phone" class="form-control">

</div>

</div>

</div>

<div class="row">

<div class="col-md-6">

<div class="form-group">

<label><?= __('email') ?></label>

<input type="email" name="email" class="form-control">

</div>

</div>

<div class="col-md-6">

<div class="form-group">

<label><?= __('birth_place') ?></label>

<input type="text" name="birth_place" class="form-control">

</div>

</div>

</div>

<div class="row">

<div class="col-md-6">

<div class="form-group">

<label><?= __('marital_status') ?></label>

<select name="marital_status" class="form-control">

<option value="Single"><?= __('single') ?></option>
<option value="Married"><?= __('married') ?></option>
<option value="Divorced"><?= __('divorced') ?></option>
<option value="Widowed"><?= __('widowed') ?></option>

</select>

</div>

</div>

<div class="col-md-6">

<div class="form-group">

<label><?= __('current_residence') ?></label>

<input type="text" name="current_residence" class="form-control">

</div>

</div>

</div>

<div class="row">

<div class="col-md-4">

<div class="form-group">

<label><?= __('father') ?></label>

<select name="father_user_id" class="form-control">

<option value=""><?= __('select_father') ?></option>

<?php
$stmt = $pdo->query("
    SELECT user_id, first_name, last_name, member_code
    FROM users
    WHERE is_deleted = 0
      AND gender = 'Male'
    ORDER BY first_name ASC
");

while ($person = $stmt->fetch(PDO::FETCH_ASSOC)):
?>

    <option value="<?= $person['user_id']; ?>">
        <?= htmlspecialchars(
            $person['member_code'] . ' - ' .
            $person['first_name'] . ' ' .
            $person['last_name']
        ); ?>
    </option>

<?php endwhile; ?>

</select>

</div>

</div>

<div class="col-md-4">

<div class="form-group">

<label><?= __('mother') ?></label>

<select name="mother_user_id" class="form-control">

<option value=""><?= __('select_mother') ?></option>

<?php
$stmt = $pdo->query("
    SELECT user_id, first_name, last_name, member_code
    FROM users
    WHERE is_deleted = 0
      AND gender = 'Female'
    ORDER BY first_name ASC
");

while ($person = $stmt->fetch(PDO::FETCH_ASSOC)):
?>

    <option value="<?= $person['user_id']; ?>">
        <?= htmlspecialchars(
            $person['member_code'] . ' - ' .
            $person['first_name'] . ' ' .
            $person['last_name']
        ); ?>
    </option>

<?php endwhile; ?>

</select>

</div>

</div>

<div class="col-md-4">

<div class="form-group">

<label><?= __('spouse') ?></label>

<select name="spouse_user_id" class="form-control">

<option value=""><?= __('select_spouse') ?></option>

<?php
$stmt = $pdo->query("
    SELECT user_id, first_name, last_name, member_code
    FROM users
    WHERE is_deleted = 0
    ORDER BY first_name ASC
");

while ($person = $stmt->fetch(PDO::FETCH_ASSOC)):
?>

    <option value="<?= $person['user_id']; ?>">
        <?= htmlspecialchars(
            $person['member_code'] . ' - ' .
            $person['first_name'] . ' ' .
            $person['last_name']
        ); ?>
    </option>

<?php endwhile; ?>

</select>

</div>

</div>

</div>

<div class="form-group">

<label><?= __('biography') ?></label>

<textarea name="biography" class="form-control" rows="4"></textarea>

</div>

<div class="row">

<div class="col-md-4">

<div class="form-group">

<label><?= __('role') ?></label>

<select name="role_id" class="form-control">

<?php foreach ($roles as $role): ?>

<option
value="<?= (int)$role['role_id']; ?>"
<?= strtolower($role['role_name']) === 'member'
? 'selected'
: ''; ?>
>

<?= htmlspecialchars($role['role_name']) ?>

</option>

<?php endforeach; ?>

</select>

</div>

</div>

<div class="col-md-4">

<div class="form-group">

<label><?= __('username') ?></label>

<input type="text" name="username" class="form-control" autocomplete="off">

</div>

</div>

<div class="col-md-4">

<div class="form-group">

<label><?= __('password') ?></label>

<input type="password" name="password" class="form-control" autocomplete="new-password">

</div>

</div>

</div>

<div class="form-group">

<div class="custom-control custom-checkbox">

<input
type="checkbox"
name="is_deceased"
value="1"
class="custom-control-input"
id="isDeceased"
>

<label class="custom-control-label" for="isDeceased">

<?= __('mark_member_deceased') ?>

</label>

</div>

</div>

<div class="form-group">

    <label><?= __('profile_picture_label') ?></label>

    <input
        type="file"
        name="profile_picture"
        class="form-control-file"
        accept="image/jpeg,image/png,image/webp"
    >

    <small class="text-muted">
        JPG, PNG or WEBP — Maximum 2MB
    </small>

</div>

<button type="submit" class="btn btn-success">

<i class="fas fa-save"></i>
<?= __('save_member') ?>

</button>

<a href="index.php" class="btn btn-secondary">

<?= __('cancel') ?>

</a>

</form>

</div>

</div>

</div>

</section>

</div>

<?php
require_once '../../includes/dashboard/footer.php';
?>