<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database=new Database();
$pdo=$database->connect();

$wallet_id=(int)($_GET['id'] ?? 0);

$stmt=$pdo->prepare("
SELECT *
FROM family_wallets
WHERE wallet_id=?
LIMIT 1
");

$stmt->execute([$wallet_id]);

$wallet=$stmt->fetch(PDO::FETCH_ASSOC);

if(!$wallet){
die(__('wallet_not_found'));
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

<h3><?= __('edit_wallet') ?></h3>

</div>

<div class="card-body">

<form action="update.php" method="POST">

<input type="hidden" name="wallet_id"
value="<?= $wallet['wallet_id'] ?>">

<div class="form-group">

<label><?= __('wallet_name') ?></label>

<input
type="text"
name="wallet_name"
class="form-control"
value="<?= htmlspecialchars($wallet['wallet_name']) ?>"
required>

</div>

<div class="form-group">

<label><?= __('goal') ?></label>

<textarea
name="goal"
class="form-control"
required><?= htmlspecialchars($wallet['goal']) ?></textarea>

</div>

<div class="form-group">

<label><?= __('monthly_amount') ?></label>

<input
type="number"
step="0.01"
name="monthly_amount"
class="form-control"
value="<?= $wallet['monthly_amount'] ?>"
required>

</div>

<div class="form-group">

<label><?= __('year_target') ?></label>

<input
type="number"
step="0.01"
name="year_target"
class="form-control"
value="<?= $wallet['year_target'] ?>"
required>

</div>

<div class="form-group">

<label><?= __('bank_name') ?></label>

<input
type="text"
name="bank_name"
class="form-control"
value="<?= htmlspecialchars($wallet['bank_name']) ?>">

</div>

<div class="form-group">

<label><?= __('account_name') ?></label>

<input
type="text"
name="account_name"
class="form-control"
value="<?= htmlspecialchars($wallet['account_name']) ?>">

</div>

<div class="form-group">

<label><?= __('account_number') ?></label>

<input
type="text"
name="account_number"
class="form-control"
value="<?= htmlspecialchars($wallet['account_number']) ?>">

</div>

<button class="btn btn-success">

<?= __('update_wallet') ?>

</button>

<a href="index.php"
class="btn btn-secondary">

<?= __('cancel') ?>

</a>

</form>

</div>

</div>

</div>

</section>

<?php
require_once '../../includes/dashboard/footer.php';
?>