<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database=new Database();
$pdo=$database->connect();

$wallet_id=(int)$_GET['id'];

$members=$pdo->query("
SELECT
user_id,
member_code,
CONCAT(first_name,' ',last_name) AS member_name
FROM users
WHERE account_status='active'
ORDER BY first_name
")->fetchAll(PDO::FETCH_ASSOC);

define('USER_ROLE','Chairperson');

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/chairperson.php';
require_once '../../includes/dashboard/navbar.php';
?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid" style="max-width: 1200px; margin: 0 auto;">

<form action="save_members.php" method="POST">

<input type="hidden" name="wallet_id" value="<?= $wallet_id ?>">

<div class="card">

<div class="card-header">

<h3><?= __('add_members') ?></h3>

</div>

<div class="card-body">

<div class="form-check">

<input
type="checkbox"
name="for_all"
value="1"
class="form-check-input">

<label class="form-check-label">

<?= __('for_all_members') ?>

</label>

</div>

<hr>

<?php foreach($members as $m): ?>

<div class="form-check">

<input
type="checkbox"
name="members[]"
value="<?= $m['user_id'] ?>"
class="form-check-input">

<label class="form-check-label">

<?= $m['member_code'] ?>

-

<?= htmlspecialchars($m['member_name']) ?>

</label>

</div>

<?php endforeach; ?>

</div>

<div class="card-footer">

<button class="btn btn-success">

<?= __('save') ?>

</button>

</div>

</div>

</form>

</div>

</section>

<?php
require_once '../../includes/dashboard/footer.php';