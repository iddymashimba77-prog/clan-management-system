<?php


require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("
SELECT *
FROM contributions
WHERE contribution_id=?
LIMIT 1
");

$stmt->execute([$id]);

$contribution = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$contribution){
    die(__('contribution_not_found'));
}

/*
|--------------------------------------------------------------------------
| PAYMENT METHODS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT pm.*

FROM contribution_method_links cml

INNER JOIN contribution_payment_methods pm

ON pm.method_id=cml.method_id

WHERE cml.contribution_id=?
ORDER BY pm.method_name
");

$stmt->execute([$id]);

$methods = $stmt->fetchAll(PDO::FETCH_ASSOC);

$progress = 0;

if($contribution['target_amount']>0){

    $progress = round(

        ($contribution['current_amount']/$contribution['target_amount'])*100,

        2

    );

}

$image = !empty($contribution['contribution_image'])

? BASE_URL."uploads/contributions/".$contribution['contribution_image']

: BASE_URL."assets/images/no-image.png";

include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid" style="max-width: 1200px; margin: 0 auto;">

<div class="row">

<div class="col-md-8">

<div class="card">

<div class="card-header bg-primary">

<h3 class="card-title">

<?= htmlspecialchars($contribution['title']) ?>

</h3>

</div>

<div class="card-body">

<img
src="<?= $image ?>"
class="img-fluid rounded mb-3"
style="width:100%;max-height:350px;object-fit:cover;">

<p>

<?= nl2br(htmlspecialchars($contribution['description'])) ?>

</p>

<hr>

<h5><?= __('contribution_progress') ?></h5>

<div class="progress mb-3" style="height:30px;">

<div
class="progress-bar bg-success"
style="width:<?= $progress ?>%;">

<?= $progress ?>%

</div>

</div>

<div class="row">

<div class="col-md-4">

<div class="small-box bg-primary">

<div class="inner">

<h4>

<?= number_format($contribution['target_amount'],2) ?>

</h4>

<p><?= __('target') ?></p>

</div>

</div>

</div>

<div class="col-md-4">

<div class="small-box bg-success">

<div class="inner">

<h4>

<?= number_format($contribution['current_amount'],2) ?>

</h4>

<p><?= __('collected') ?></p>

</div>

</div>

</div>

<div class="col-md-4">

<div class="small-box bg-warning">

<div class="inner">

<h4>

<?= number_format($contribution['target_amount']-$contribution['current_amount'],2) ?>

</h4>

<p><?= __('remaining') ?></p>

</div>

</div>

</div>

</div>

</div>

</div>

</div>

<div class="col-md-4">

<div class="card">

<div class="card-header bg-success">

<h5>

<?= __('payment_methods') ?>

</h5>

</div>

<div class="card-body">

<?php foreach($methods as $method): ?>

<div class="border rounded p-3 mb-3">

<h6>

<?= htmlspecialchars($method['method_name']) ?>

</h6>

<b><?= __('account_name') ?></b><br>

<?= htmlspecialchars($method['account_name']) ?>

<br><br>

<b><?= __('account_number') ?></b><br>

<?= htmlspecialchars($method['account_number']) ?>

</div>

<?php endforeach; ?>

<a
href="pay.php?id=<?= $contribution['contribution_id'] ?>"
class="btn btn-success btn-lg btn-block">

<i class="fas fa-money-check-alt"></i>

<?= __('pay_now') ?>

</a>

</div>

</div>

</div>

</div>

</div>

</section>

<?php include '../includes/footer.php'; ?>