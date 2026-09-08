<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

$database = new Database();
$pdo = $database->connect();

header('Content-Type: text/csv; charset=utf-8');
header(
    'Content-Disposition: attachment; filename="finance_report_' .
    date('Y-m-d_H-i-s') .
    '.csv"'
);

$output = fopen('php://output', 'w');

fputcsv($output, [
    'Transaction ID',
    'Type',
    'Source',
    'Member',
    'Amount',
    'Status',
    'Date'
]);

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

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

    fputcsv($output, [
        $row['transaction_id'],
        $row['type'],
        $row['source'],
        $row['member'],
        $row['amount'],
        $row['status'],
        $row['transaction_date']
    ]);
}

fclose($output);
exit;