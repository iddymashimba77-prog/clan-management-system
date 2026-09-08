<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();


/*
|--------------------------------------------------------------------------
| CONTRIBUTIONS
|--------------------------------------------------------------------------
*/

$contributions = (float)$pdo->query("
    SELECT COALESCE(SUM(amount),0)
    FROM contribution_payments
    WHERE payment_status = 'approved'
")->fetchColumn();


/*
|--------------------------------------------------------------------------
| WALLETS
|--------------------------------------------------------------------------
*/

$walletPayments = (float)$pdo->query("
    SELECT COALESCE(SUM(amount),0)
    FROM family_wallet_payments
    WHERE payment_status = 'approved'
")->fetchColumn();


/*
|--------------------------------------------------------------------------
| PENDING
|--------------------------------------------------------------------------
*/

$pending = (float)$pdo->query("
    SELECT COALESCE(SUM(amount),0)
    FROM contribution_payments
    WHERE payment_status = 'pending'
")->fetchColumn();


$totalReceived =
    $contributions +
    $walletPayments;


require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/administrator.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">

<div class="card clan-page-card">

<div class="card-header">

<h3 class="card-title">

<i class="fas fa-chart-bar mr-2"></i>

<?= __('financial_reports_title') ?>

</h3>

</div>

<div class="card-body">

<div class="row">

<div class="col-md-4">

<div class="info-box">

<span class="info-box-icon bg-success">

<i class="fas fa-hand-holding-usd"></i>

</span>

<div class="info-box-content">

<span class="info-box-text">
Contributions
</span>

<span class="info-box-number">

<?= number_format(
$contributions,
2
); ?>

</span>

</div>

</div>

</div>


<div class="col-md-4">

<div class="info-box">

<span class="info-box-icon bg-primary">

<i class="fas fa-wallet"></i>

</span>

<div class="info-box-content">

<span class="info-box-text">
<?= __('wallet_payments') ?>
</span>

<span class="info-box-number">

<?= number_format(
$walletPayments,
2
); ?>

</span>

</div>

</div>

</div>


<div class="col-md-4">

<div class="info-box">

<span class="info-box-icon bg-info">

<i class="fas fa-money-bill-wave"></i>

</span>

<div class="info-box-content">

<span class="info-box-text">
<?= __('total_received') ?>
</span>

<span class="info-box-number">

<?= number_format(
$totalReceived,
2
); ?>

</span>

</div>

</div>

</div>

</div>


<div class="row">

<div class="col-md-6">

<div class="card card-outline card-warning">

<div class="card-body">

<h5>
Pending <?= __('contribution_payments') ?>
</h5>

<h3>

<?= number_format(
$pending,
2
); ?>

</h3>

</div>

</div>

</div>


<div class="col-md-6">

<div class="card card-outline card-success">

<div class="card-body">

<h5>
<?= __('report_actions') ?>
</h5>

<a
href="export.php"
class="btn btn-success"
>

<i class="fas fa-file-excel mr-1"></i>

<?= __('export_report') ?>

</a>

<button
type="button"
onclick="window.print()"
class="btn btn-secondary"
>

<i class="fas fa-print mr-1"></i>

<?= __('print') ?>

</button>

</div>

</div>

</div>

</div>

</div>

</div>

</div>

</section>

</div>

<?php

require_once '../../includes/dashboard/footer.php';

?>