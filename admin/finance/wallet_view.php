<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    die('Invalid wallet ID.');
}

$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->prepare("
    SELECT
        w.*,
        CONCAT(
            COALESCE(u.first_name, ''),
            ' ',
            COALESCE(u.middle_name, ''),
            ' ',
            COALESCE(u.last_name, '')
        ) AS creator_name
    FROM family_wallets w
    LEFT JOIN users u
        ON u.user_id = w.created_by_user_id
    WHERE w.wallet_id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$wallet = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$wallet) {
    die('Wallet not found.');
}

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebar.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">

<div class="card clan-page-card">

<div class="card-header">

<h3 class="card-title">
<i class="fas fa-wallet mr-2"></i>
<?= __('wallet_details') ?>
</h3>

</div>

<div class="card-body">

<div class="row">

<div class="col-md-8">

<h4><?= htmlspecialchars($wallet['wallet_name']); ?></h4>

<p>
<?= nl2br(htmlspecialchars($wallet['description'] ?? '')); ?>
</p>

<table class="table table-bordered">

<tr>
<th><?= __('purpose') ?></th>
<td><?= nl2br(htmlspecialchars($wallet['purpose'] ?? '')); ?></td>
</tr>

<tr>
<th><?= __('target_amount') ?></th>
<td><?= number_format($wallet['target_amount'], 2); ?></td>
</tr>

<tr>
<th><?= __('monthly_amount') ?></th>
<td><?= number_format($wallet['monthly_amount'], 2); ?></td>
</tr>

<tr>
<th><?= __('expected_amount') ?></th>
<td><?= number_format($wallet['expected_amount'], 2); ?></td>
</tr>

<tr>
<th><?= __('current_balance') ?></th>
<td><?= number_format($wallet['current_balance'], 2); ?></td>
</tr>

<tr>
<th><?= __('start_date') ?></th>
<td><?= htmlspecialchars($wallet['start_date']); ?></td>
</tr>

<tr>
<th><?= __('end_date') ?></th>
<td><?= htmlspecialchars($wallet['end_date']); ?></td>
</tr>

<tr>
<th><?= __('bank_name_label') ?></th>
<td><?= htmlspecialchars($wallet['bank_name']); ?></td>
</tr>

<tr>
<th><?= __('account_name_label') ?></th>
<td><?= htmlspecialchars($wallet['account_name']); ?></td>
</tr>

<tr>
<th><?= __('account_number_label') ?></th>
<td><?= htmlspecialchars($wallet['account_number']); ?></td>
</tr>

<tr>
<th><?= __('status') ?></th>
<td><?= htmlspecialchars($wallet['status']); ?></td>
</tr>

</table>

</div>

</div>

<a href="wallet_payments.php?id=<?= $id ?>"
   class="btn btn-primary">
<i class="fas fa-money-bill"></i>
<?= __('wallet_payments') ?>
</a>

<a href="index.php"
   class="btn btn-secondary">
<?= __('back') ?>
</a>

</div>

</div>

</div>

</section>

</div>

<?php require_once '../../includes/dashboard/footer.php'; ?>