<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

$pdo = $database->connect();

$report = $_GET['report'] ?? 'members';

$title = 'Clan Management Report';
$rows = [];
$headers = [];

switch ($report) {

    case 'members':

        $title = 'Members Report';

        $headers = [
            'Member Code',
            'Name',
            'Gender',
            'Date of Birth',
            'Marital Status',
            'Account'
        ];

        $stmt = $pdo->query("
            SELECT
                member_code,
                CONCAT(
                    first_name,' ',
                    COALESCE(middle_name,''),' ',
                    last_name
                ) AS name,
                gender,
                date_of_birth,
                marital_status,
                account_status
            FROM users
            WHERE is_deleted = 0
            ORDER BY first_name,last_name
        ");

        $rows = $stmt->fetchAll(PDO::FETCH_NUM);

        break;


    case 'contributions':

        $title = 'Contributions Report';

        $headers = [
            'Contribution',
            'Target',
            'Collected',
            'Status'
        ];

        $stmt = $pdo->query("
            SELECT
                c.title,
                c.target_amount,
                COALESCE(SUM(
                    CASE
                        WHEN cp.payment_status='approved'
                        THEN cp.amount
                        ELSE 0
                    END
                ),0),
                c.status
            FROM contributions c
            LEFT JOIN contribution_payments cp
                ON cp.contribution_id=c.contribution_id
            GROUP BY c.contribution_id
            ORDER BY c.created_at DESC
        ");

        $rows = $stmt->fetchAll(PDO::FETCH_NUM);

        break;


    case 'wallets':

        $title = 'Wallets Report';

        $headers = [
            'Wallet',
            'Target',
            'Monthly',
            'Balance',
            'Status'
        ];

        $stmt = $pdo->query("
            SELECT
                wallet_name,
                target_amount,
                monthly_amount,
                current_balance,
                status
            FROM family_wallets
            ORDER BY created_at DESC
        ");

        $rows = $stmt->fetchAll(PDO::FETCH_NUM);

        break;


    case 'events':

        $title = 'Events Report';

        $headers = [
            'Event',
            'Date',
            'Location',
            'Status'
        ];

        $stmt = $pdo->query("
            SELECT
                title,
                event_date,
                location,
                event_status
            FROM events
            ORDER BY event_date DESC
        ");

        $rows = $stmt->fetchAll(PDO::FETCH_NUM);

        break;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<title><?= htmlspecialchars($title) ?></title>

<link rel="stylesheet"
href="../../assets/css/bootstrap.min.css">

<style>

body {
    background:#fff;
    font-family:Arial,sans-serif;
    padding:30px;
}

.report-header {
    text-align:center;
    margin-bottom:30px;
}

.report-header h2 {
    margin-bottom:5px;
}

table {
    width:100%;
    border-collapse:collapse;
}

th,
td {
    border:1px solid #ccc;
    padding:8px;
    font-size:13px;
}

th {
    background:#f2f2f2;
}

@media print {

    .no-print {
        display:none;
    }

    body {
        padding:0;
    }

}

</style>

</head>

<body>

<div class="no-print mb-3">

<button
onclick="window.print()"
class="btn btn-primary">
<i class="fas fa-print"></i>
Print
</button>

</div>

<div class="report-header">

<h2><?= htmlspecialchars($title) ?></h2>

<div>
Clan Management System
</div>

<div>
Generated: <?= date('Y-m-d H:i:s') ?>
</div>

</div>

<table>

<thead>

<tr>

<?php foreach ($headers as $header): ?>

<th>
<?= htmlspecialchars($header) ?>
</th>

<?php endforeach; ?>

</tr>

</thead>

<tbody>

<?php foreach ($rows as $row): ?>

<tr>

<?php foreach ($row as $value): ?>

<td>
<?= htmlspecialchars((string)($value ?? '-')) ?>
</td>

<?php endforeach; ?>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</body>

</html>