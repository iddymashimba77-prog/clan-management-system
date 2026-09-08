<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {

    die('Wallet ID is missing.');

}

$database = new Database();
$pdo = $database->connect();


$stmt = $pdo->prepare("
    SELECT
        fw.*,
        CONCAT(
            COALESCE(u.first_name, ''),
            ' ',
            COALESCE(u.middle_name, ''),
            ' ',
            COALESCE(u.last_name, '')
        ) AS creator_name
    FROM family_wallets fw
    LEFT JOIN users u
        ON u.user_id = fw.created_by_user_id
    WHERE fw.wallet_id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$wallet = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$wallet) {

    die('Wallet not found.');

}


/*
|--------------------------------------------------------------------------
| Members
|--------------------------------------------------------------------------
*/

$membersStmt = $pdo->prepare("
    SELECT
        u.user_id,
        u.member_code,
        u.first_name,
        u.middle_name,
        u.last_name,
        u.gender,
        fwb.current_balance
    FROM family_wallet_members fwm
    INNER JOIN users u
        ON u.user_id = fwm.user_id
    LEFT JOIN family_wallet_balances fwb
        ON fwb.wallet_id = fwm.wallet_id
       AND fwb.user_id = fwm.user_id
    WHERE fwm.wallet_id = ?
      AND u.is_deleted = 0
    ORDER BY u.first_name, u.last_name
");

$membersStmt->execute([$id]);

$members = $membersStmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Payments
|--------------------------------------------------------------------------
*/

$paymentsStmt = $pdo->prepare("
    SELECT
        fwp.*,
        u.member_code,
        CONCAT(
            u.first_name,
            ' ',
            u.last_name
        ) AS member_name
    FROM family_wallet_payments fwp
    INNER JOIN users u
        ON u.user_id = fwp.member_user_id
    WHERE fwp.wallet_id = ?
    ORDER BY fwp.payment_id DESC
");

$paymentsStmt->execute([$id]);

$payments = $paymentsStmt->fetchAll(PDO::FETCH_ASSOC);


require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebar.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">


<div class="card clan-page-card">

<div class="card-header d-flex justify-content-between">

<h3 class="card-title">

<i class="fas fa-wallet mr-2"></i>

<?= htmlspecialchars($wallet['wallet_name']); ?>

</h3>

<div>

<a
    href="edit.php?id=<?= $id; ?>"
    class="btn btn-primary btn-sm"
>

<i class="fas fa-edit"></i>

<?= __('edit') ?>

</a>

<a
    href="index.php"
    class="btn btn-secondary btn-sm"
>

<?= __('back') ?>

</a>

</div>

</div>


<div class="card-body">

<div class="row">

<div class="col-md-4">

<div class="small-box bg-primary">

<div class="inner">

<h3>
<?= number_format(
    (float)$wallet['current_balance'],
    2
); ?>
</h3>

<p><?= __('current_balance') ?></p>

</div>

<div class="icon">

<i class="fas fa-money-bill-wave"></i>

</div>

</div>

</div>


<div class="col-md-4">

<div class="small-box bg-success">

<div class="inner">

<h3>
<?= number_format(
    (float)$wallet['target_amount'],
    2
); ?>
</h3>

<p><?= __('target_amount') ?></p>

</div>

<div class="icon">

<i class="fas fa-bullseye"></i>

</div>

</div>

</div>


<div class="col-md-4">

<div class="small-box bg-info">

<div class="inner">

<h3>
<?= number_format(
    (float)$wallet['monthly_amount'],
    2
); ?>
</h3>

<p><?= __('monthly_amount') ?></p>

</div>

<div class="icon">

<i class="fas fa-calendar-alt"></i>

</div>

</div>

</div>

</div>


<table class="table table-bordered">

<tr>

<th width="25%"><?= __('description_label') ?></th>

<td>
<?= nl2br(
    htmlspecialchars(
        $wallet['description'] ?? ''
    )
); ?>
</td>

</tr>


<tr>

<th><?= __('purpose') ?></th>

<td>
<?= nl2br(
    htmlspecialchars(
        $wallet['purpose'] ?? ''
    )
); ?>
</td>

</tr>


<tr>

<th><?= __('period') ?></th>

<td>
<?= htmlspecialchars($wallet['start_date']); ?>

&nbsp; → &nbsp;

<?= htmlspecialchars($wallet['end_date']); ?>
</td>

</tr>


<tr>

<th><?= __('bank_name_label') ?></th>

<td>
<?= htmlspecialchars($wallet['bank_name']); ?>
</td>

</tr>


<tr>

<th><?= __('account_name_label') ?></th>

<td>
<?= htmlspecialchars($wallet['account_name']); ?>
</td>

</tr>


<tr>

<th><?= __('account_number_label') ?></th>

<td>
<?= htmlspecialchars($wallet['account_number']); ?>
</td>

</tr>


<tr>

<th><?= __('visibility') ?></th>

<td>
<?= htmlspecialchars($wallet['visibility']); ?>
</td>

</tr>


<tr>

<th><?= __('status') ?></th>

<td>

<?php if ($wallet['status'] === 'active'): ?>

<span class="badge badge-success">
Active
</span>

<?php else: ?>

<span class="badge badge-secondary">
Closed
</span>

<?php endif; ?>

</td>

</tr>

</table>

</div>

</div>


<!-- MEMBERS -->

<div class="card clan-page-card">

<div class="card-header">

<h3 class="card-title">

<i class="fas fa-users mr-2"></i>

<?= __('wallet_members') ?>

</h3>

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-striped">

<thead>

<tr>

<th>#</th>
<th>Member Code</th>
<th><?= __('name') ?></th>
<th><?= __('gender') ?></th>
<th><?= __('balance') ?></th>

</tr>

</thead>

<tbody>

<?php foreach ($members as $member): ?>

<tr>

<td><?= (int)$member['user_id']; ?></td>

<td>
<?= htmlspecialchars($member['member_code']); ?>
</td>

<td>

<?= htmlspecialchars(
    trim(
        $member['first_name']
        . ' '
        . $member['middle_name']
        . ' '
        . $member['last_name']
    )
); ?>

</td>

<td>
<?= htmlspecialchars($member['gender']); ?>
</td>

<td>
<?= number_format(
    (float)($member['current_balance'] ?? 0),
    2
); ?>
</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

</div>


<!-- PAYMENTS -->

<div class="card clan-page-card">

<div class="card-header">

<h3 class="card-title">

<i class="fas fa-receipt mr-2"></i>

<?= __('wallet_payments') ?>

</h3>

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-striped">

<thead>

<tr>

<th>#</th>
<th><?= __('member') ?></th>
<th><?= __('amount') ?></th>
<th><?= __('month') ?></th>
<th><?= __('reference') ?></th>
<th><?= __('status') ?></th>
<th><?= __('date') ?></th>

</tr>

</thead>

<tbody>

<?php foreach ($payments as $payment): ?>

<tr>

<td>
<?= (int)$payment['payment_id']; ?>
</td>

<td>

<?= htmlspecialchars(
    $payment['member_code']
    . ' - '
    . $payment['member_name']
); ?>

</td>

<td>

<?= number_format(
    (float)$payment['amount'],
    2
); ?>

</td>

<td>
<?= htmlspecialchars(
    $payment['payment_month']
); ?>
</td>

<td>
<?= htmlspecialchars(
    $payment['reference_number'] ?? ''
); ?>
</td>

<td>

<span class="badge
<?= $payment['payment_status'] === 'approved'
    ? 'badge-success'
    : ($payment['payment_status'] === 'rejected'
        ? 'badge-danger'
        : 'badge-warning')
?>">

<?= htmlspecialchars(
    $payment['payment_status']
); ?>

</span>

</td>

<td>
<?= htmlspecialchars(
    $payment['payment_date']
); ?>
</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

</section>

</div>

<?php require_once '../../includes/dashboard/footer.php'; ?>