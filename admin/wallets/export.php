<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

$database = new Database();
$pdo = $database->connect();

$wallet_id = isset($_GET['wallet_id'])
    ? (int) $_GET['wallet_id']
    : 0;

if ($wallet_id <= 0) {
    die('Invalid wallet ID.');
}

/*
|--------------------------------------------------------------------------
| WALLET
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT wallet_name
    FROM family_wallets
    WHERE wallet_id = ?
    LIMIT 1
");

$stmt->execute([$wallet_id]);

$wallet = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$wallet) {
    die('Wallet not found.');
}

/*
|--------------------------------------------------------------------------
| DUES
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT

        d.due_month,
        d.required_amount,
        d.paid_amount,
        d.balance,
        d.status,

        u.member_code,
        u.first_name,
        u.middle_name,
        u.last_name,
        u.phone

    FROM wallet_monthly_dues d

    INNER JOIN users u
        ON u.user_id = d.member_user_id

    WHERE d.wallet_id = ?

    ORDER BY
        d.due_month DESC,
        u.first_name ASC
");

$stmt->execute([$wallet_id]);

$dues = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| CSV HEADERS
|--------------------------------------------------------------------------
*/

$filename =
    'wallet_dues_' .
    $wallet_id .
    '_' .
    date('Y-m-d') .
    '.csv';

header('Content-Type: text/csv; charset=utf-8');

header(
    'Content-Disposition: attachment; filename="' .
    $filename .
    '"'
);

header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

/*
|--------------------------------------------------------------------------
| UTF-8 BOM
|--------------------------------------------------------------------------
*/

fprintf(
    $output,
    chr(0xEF) . chr(0xBB) . chr(0xBF)
);

/*
|--------------------------------------------------------------------------
| TITLE
|--------------------------------------------------------------------------
*/

fputcsv($output, [
    'Wallet',
    $wallet['wallet_name']
]);

fputcsv($output, []);

fputcsv($output, [
    'Member Code',
    'Member Name',
    'Phone',
    'Due Month',
    'Required Amount',
    'Paid Amount',
    'Balance',
    'Status'
]);

/*
|--------------------------------------------------------------------------
| DATA
|--------------------------------------------------------------------------
*/

foreach ($dues as $due) {

    $name = trim(
        $due['first_name'] . ' ' .
        $due['middle_name'] . ' ' .
        $due['last_name']
    );

    fputcsv($output, [

        $due['member_code'],

        $name,

        $due['phone'],

        date(
            'F Y',
            strtotime($due['due_month'])
        ),

        number_format(
            (float) $due['required_amount'],
            2,
            '.',
            ''
        ),

        number_format(
            (float) $due['paid_amount'],
            2,
            '.',
            ''
        ),

        number_format(
            (float) $due['balance'],
            2,
            '.',
            ''
        ),

        $due['status']

    ]);
}

fclose($output);

exit;