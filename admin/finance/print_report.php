<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->query("
    SELECT
        cp.payment_id AS transaction_id,
        'Contribution' AS type,
        c.title AS source,
        CONCAT(
            u.first_name, ' ',
            COALESCE(u.last_name, '')
        ) AS member,
        cp.amount,
        cp.payment_status AS status,
        cp.payment_date AS transaction_date
    FROM contribution_payments cp
    INNER JOIN contributions c
        ON c.contribution_id = cp.contribution_id
    INNER JOIN users u
        ON u.user_id = cp.member_user_id

    UNION ALL

    SELECT
        wp.payment_id,
        'Wallet',
        w.wallet_name,
        CONCAT(
            u.first_name, ' ',
            COALESCE(u.last_name, '')
        ),
        wp.amount,
        wp.payment_status,
        wp.payment_date
    FROM family_wallet_payments wp
    INNER JOIN family_wallets w
        ON w.wallet_id = wp.wallet_id
    INNER JOIN users u
        ON u.user_id = wp.member_user_id

    ORDER BY transaction_date DESC
");

$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;

foreach ($transactions as $transaction) {
    if ($transaction['status'] === 'approved') {
        $total += (float)$transaction['amount'];
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Finance Report</title>

<link rel="stylesheet"
      href="../../assets/css/clan-theme.css">

<style>

@media print {

    .no-print {
        display: none !important;
    }

    body {
        background: #fff !important;
    }

}

.report-header {
    text-align: center;
    margin-bottom: 30px;
}

.report-summary {
    margin-bottom: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
    border: 1px solid #ccc;
    padding: 8px;
}

th {
    font-weight: 600;
}

</style>

</head>

<body>

<div class="container-fluid p-4">

<div class="report-header">

<h2>Clan Finance Report</h2>

<p>
Generated:
<?= date('Y-m-d H:i:s'); ?>
</p>

</div>

<div class="report-summary">

<strong>
<?= __('approved_total') ?>
</strong>

<?= number_format($total, 2); ?>

</div>

<table>

<thead>

<tr>

<th>ID</th>
<th><?= __('type') ?></th>
<th><?= __('source') ?></th>
<th><?= __('member') ?></th>
<th><?= __('amount') ?></th>
<th><?= __('status') ?></th>
<th><?= __('date') ?></th>

</tr>

</thead>

<tbody>

<?php foreach ($transactions as $transaction): ?>

<tr>

<td><?= (int)$transaction['transaction_id']; ?></td>

<td><?= htmlspecialchars($transaction['type']); ?></td>

<td><?= htmlspecialchars($transaction['source']); ?></td>

<td><?= htmlspecialchars($transaction['member']); ?></td>

<td><?= number_format($transaction['amount'], 2); ?></td>

<td><?= htmlspecialchars($transaction['status']); ?></td>

<td><?= htmlspecialchars($transaction['transaction_date'] ?? ''); ?></td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

<div class="no-print mt-4">

<button
    onclick="window.print();"
    class="btn btn-primary">

<i class="fas fa-print"></i>
<?= __('print_report') ?>

</button>

<a
    href="transactions.php"
    class="btn btn-secondary">

<?= __('back') ?>

</a>

</div>

</div>

</body>

</html>