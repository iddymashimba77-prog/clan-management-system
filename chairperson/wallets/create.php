<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->query("
SELECT
user_id,
member_code,
CONCAT(first_name,' ',last_name) AS full_name
FROM users
WHERE account_status='active'
ORDER BY first_name,last_name
");

$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

<h3><?= __('create_family_wallet') ?></h3>

</div>

<div class="card-body">

<form action="store.php" method="POST">

<div class="row">

<div class="col-md-6">

<div class="form-group">

<label><?= __('wallet_name') ?></label>

<input
type="text"
name="wallet_name"
class="form-control"
required>

</div>

</div>

<div class="col-md-6">

<div class="form-group">

<label><?= __('purpose') ?></label>

<input
type="text"
name="purpose"
class="form-control"
required>

</div>

</div>

</div>

<div class="form-group">

<label><?= __('description') ?></label>

<textarea
name="description"
class="form-control"
rows="3"></textarea>

</div>

<div class="row">

<div class="col-md-4">

<div class="form-group">

<label><?= __('monthly_contribution') ?></label>

<input
type="number"
step="0.01"
min="1"
name="monthly_amount"
id="monthly_amount"
class="form-control"
required>

</div>

</div>

<div class="col-md-4">

<div class="form-group">

<label><?= __('target_amount') ?></label>

<input
type="number"
step="0.01"
min="1"
name="target_amount"
class="form-control"
required>

</div>

</div>

<div class="col-md-4">

<div class="form-group">

<label><?= __('expected_amount') ?></label>

<input
type="text"
id="expected_amount"
class="form-control"
readonly>

</div>

</div>

</div>

<div class="row">

<div class="col-md-6">

<div class="form-group">

<label><?= __('start_date') ?></label>

<input
type="date"
name="start_date"
id="start_date"
class="form-control"
required>

</div>

</div>

<div class="col-md-6">

<div class="form-group">

<label><?= __('end_date') ?></label>

<input
type="date"
name="end_date"
id="end_date"
class="form-control"
required>

</div>

</div>

</div>

<hr>

<h5><?= __('bank_account_details') ?></h5>

<div class="row">

<div class="col-md-4">

<div class="form-group">

<label><?= __('bank_name') ?></label>

<input
type="text"
name="bank_name"
class="form-control"
required>

</div>

</div>

<div class="col-md-4">

<div class="form-group">

<label><?= __('account_name') ?></label>

<input
type="text"
name="account_name"
class="form-control"
required>

</div>

</div>

<div class="col-md-4">

<div class="form-group">

<label><?= __('account_number') ?></label>

<input
type="text"
name="account_number"
class="form-control"
required>

</div>

</div>

</div>

<hr>

<div class="form-group">

<label><?= __('wallet_visibility') ?></label>

<select
name="visibility"
id="visibility"
class="form-control">

<option value="all">

<?= __('for_all_members') ?>

</option>

<option value="selected">

<?= __('selected_members') ?>

</option>

</select>

</div>

<div
id="memberBox"
style="display:none;">

<div class="form-group">

<label><?= __('select_members') ?></label>

<select
name="members[]"
id="members"
class="form-control"
multiple
size="10">

<?php foreach($members as $m): ?>

<option value="<?= $m['user_id'] ?>">

<?= $m['member_code'] ?>

-

<?= htmlspecialchars($m['full_name']) ?>

</option>

<?php endforeach; ?>

</select>

<small>

<?= __('hold_ctrl_to_select') ?>

</small>

</div>

</div>

<div class="mt-3">

<button
class="btn btn-success">

<?= __('save_wallet') ?>

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

</div>

</section>

<script>

const visibility=document.getElementById('visibility');

const memberBox=document.getElementById('memberBox');

visibility.onchange=function(){

if(this.value==='selected'){

memberBox.style.display='block';

}else{

memberBox.style.display='none';

}

calculateExpected();

};

document.getElementById('monthly_amount').onkeyup=calculateExpected;
document.getElementById('start_date').onchange=calculateExpected;
document.getElementById('end_date').onchange=calculateExpected;
document.getElementById('members').onchange=calculateExpected;

function calculateExpected(){

let monthly=parseFloat(document.getElementById('monthly_amount').value)||0;

let start=document.getElementById('start_date').value;

let end=document.getElementById('end_date').value;

let membersCount=0;

if(visibility.value==='all'){

membersCount=<?= count($members) ?>;

}else{

membersCount=document.getElementById('members').selectedOptions.length;

}

if(start!=='' && end!==''){

let s=new Date(start);

let e=new Date(end);

let months=(e.getFullYear()-s.getFullYear())*12;

months+=e.getMonth()-s.getMonth()+1;

if(months<1) months=1;

let expected=monthly*membersCount*months;

document.getElementById('expected_amount').value=

expected.toLocaleString();

}

}

</script>

<?php
require_once '../../includes/dashboard/footer.php';
?>