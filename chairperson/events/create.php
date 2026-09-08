<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

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

<?= __('create_new_event') ?>

</h3>

</div>

<form action="store.php" method="POST" enctype="multipart/form-data">

<div class="card-body">

<div class="form-group">

<label><?= __('event_title') ?></label>

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
rows="5"
class="form-control"></textarea>

</div>

<div class="row">

<div class="col-md-4">

<div class="form-group">

<label><?= __('event_date') ?></label>

<input
type="date"
name="event_date"
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

<div class="form-group">

<label><?= __('location') ?></label>

<input
type="text"
name="location"
class="form-control">

</div>

<div class="form-group">

<label><?= __('event_poster') ?></label>

<input
type="file"
name="event_image"
accept=".jpg,.jpeg,.png"
class="form-control">

</div>

<div class="form-group">

<label><?= __('status') ?></label>

<select
name="event_status"
class="form-control">

<option value="upcoming"><?= __('upcoming') ?></option>

<option value="ongoing"><?= __('ongoing') ?></option>

<option value="completed"><?= __('completed') ?></option>

<option value="cancelled"><?= __('cancelled') ?></option>

</select>

</div>

</div>

<div class="card-footer">

<button
type="submit"
class="btn btn-success">

<i class="fas fa-save"></i>

<?= __('save_event') ?>

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