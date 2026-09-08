<?php

ini_set('display_startup_errors', 1);

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireMember();

$database = new Database();
$pdo = $database->connect();

$payment_id = (int)($_GET['id'] ?? 0);

if ($payment_id <= 0) {
    die(__('invalid_payment_id'));
}

try {

    $stmt = $pdo->prepare("
        SELECT

            cp.*,

            u.member_code,

            CONCAT(
                u.first_name,
                ' ',
                COALESCE(u.middle_name, ''),
                ' ',
                u.last_name
            ) AS member_name,

            ec.title AS contribution_title,

            e.title AS event_name,

            e.event_date,

            cs.clan_name

        FROM contribution_payments cp

        INNER JOIN users u
            ON cp.member_user_id = u.user_id

        INNER JOIN event_contributions ec
            ON cp.contribution_id = ec.contribution_id

        INNER JOIN events e
            ON ec.event_id = e.event_id

        LEFT JOIN clan_settings cs
            ON cs.setting_id = 1

        WHERE cp.payment_id = ?

        AND cp.member_user_id = ?

        AND cp.payment_status = 'approved'

        LIMIT 1
    ");

    $stmt->execute([
        $payment_id,
        $_SESSION['user_id']
    ]);

    $receipt = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$receipt) {
        die(__('receipt_not_found_or_not_approved'));
    }

} catch (PDOException $e) {

    die(
        "<h3>" . __('database_error') . "</h3>" .
        "<pre>" .
        htmlspecialchars($e->getMessage()) .
        "</pre>"
    );
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<title>
<?= __('contribution_receipt') ?>
</title>

<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>

<style>

body {

    font-family: Arial, sans-serif;

    background: #f2f2f2;

    margin: 0;

    padding: 30px;

}

.receipt {

    width: 800px;

    max-width: 100%;

    margin: auto;

    background: white;

    padding: 40px;

    box-sizing: border-box;

}

.header {

    text-align: center;

    margin-bottom: 25px;

}

.header h1 {

    margin: 0;

    font-size: 28px;

}

.header h3 {

    margin-top: 8px;

}

hr {

    border: 0;

    border-top: 1px solid #ddd;

    margin: 20px 0;

}

table {

    width: 100%;

    border-collapse: collapse;

}

th,
td {

    border: 1px solid #ddd;

    padding: 12px;

    text-align: left;

}

th {

    width: 35%;

    background: #f7f7f7;

}

.amount {

    font-size: 22px;

    font-weight: bold;

}

.approved {

    color: green;

    font-weight: bold;

}

.footer {

    text-align: center;

    margin-top: 40px;

    color: #666;

}

.print-button {

    text-align: center;

    margin: 20px;

}

button {

    padding: 10px 20px;

    border: none;

    background: #198754;

    color: white;

    cursor: pointer;

    border-radius: 5px;

}

@media print {

    body {

        background: white;

        padding: 0;

    }

    .receipt {

        width: 100%;

        padding: 20px;

    }

    .print-button {

        display: none;

    }

}

</style>

</head>


<body>


<div class="print-button">

<button onclick="window.print()">

<?= __('print_receipt') ?>

</button>

</div>


<div class="receipt">


<div class="header">

<h1>

<?= htmlspecialchars(
    $receipt['clan_name'] ?? __('family_relation')
) ?>

</h1>

<h3>

<?= __('official_contribution_receipt') ?>

</h3>

<hr>

</div>


<table>


<tr>

<th><?= __('receipt_no') ?></th>

<td>

<?= htmlspecialchars(
    $receipt['payment_id']
) ?>

</td>

</tr>


<tr>

<th><?= __('reference_no') ?></th>

<td>

<?= htmlspecialchars(
    $receipt['reference_number'] ?? 'N/A'
) ?>

</td>

</tr>


<tr>

<th><?= __('member') ?></th>

<td>

<?= htmlspecialchars(
    $receipt['member_name']
) ?>

</td>

</tr>


<tr>

<th><?= __('member_code') ?></th>

<td>

<?= htmlspecialchars(
    $receipt['member_code']
) ?>

</td>

</tr>


<tr>

<th><?= __('event') ?></th>

<td>

<?= htmlspecialchars(
    $receipt['event_name']
) ?>

</td>

</tr>


<tr>

<th><?= __('contribution') ?></th>

<td>

<?= htmlspecialchars(
    $receipt['contribution_title']
) ?>

</td>

</tr>


<tr>

<th><?= __('event_date') ?></th>

<td>

<?= !empty($receipt['event_date'])
    ? date(
        'd M Y',
        strtotime(
            $receipt['event_date']
        )
    )
    : 'N/A'
?>

</td>

</tr>


<tr>

<th><?= __('amount_paid') ?></th>

<td class="amount">

TZS

<?= number_format(
    (float)$receipt['amount'],
    2
) ?>

</td>

</tr>


<tr>

<th><?= __('payment_date') ?></th>

<td>

<?= htmlspecialchars(
    $receipt['payment_date'] ?? 'N/A'
) ?>

</td>

</tr>


<tr>

<th><?= __('status') ?></th>

<td class="approved">

<?= __('approved') ?>

</td>

</tr>


</table>


<div class="footer">

<p>

<?= __('thank_you_for_contribution') ?>

</p>

<p>

<?= htmlspecialchars(
    $receipt['clan_name'] ?? __('clan_management_system')
) ?>

</p>

</div>


</div>


</body>

</html>