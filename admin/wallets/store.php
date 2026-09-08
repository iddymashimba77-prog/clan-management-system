<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: index.php');
    exit;

}

$database = new Database();
$pdo = $database->connect();

$adminId = $_SESSION['user_id'];

$walletName     = trim($_POST['wallet_name'] ?? '');
$description    = trim($_POST['description'] ?? '');
$purpose        = trim($_POST['purpose'] ?? '');

$targetAmount   = (float)($_POST['target_amount'] ?? 0);
$monthlyAmount  = (float)($_POST['monthly_amount'] ?? 0);

$startDate      = $_POST['start_date'] ?? null;
$endDate        = $_POST['end_date'] ?? null;

$bankName       = trim($_POST['bank_name'] ?? '');
$accountName    = trim($_POST['account_name'] ?? '');
$accountNumber  = trim($_POST['account_number'] ?? '');

$visibility     = $_POST['visibility'] ?? 'all';

$members        = $_POST['members'] ?? [];

if ($walletName === '') {

    die('Wallet name is required.');

}

if ($targetAmount < 0 || $monthlyAmount < 0) {

    die('Invalid amount.');

}

if (!in_array($visibility, ['all', 'selected'], true)) {

    $visibility = 'all';

}

try {

    $pdo->beginTransaction();


    $stmt = $pdo->prepare("
        INSERT INTO family_wallets
        (
            wallet_name,
            description,
            purpose,
            target_amount,
            monthly_amount,
            start_date,
            end_date,
            bank_name,
            account_name,
            account_number,
            visibility,
            expected_amount,
            current_balance,
            status,
            created_by_user_id
        )
        VALUES
        (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
            0.00,
            0.00,
            'active',
            ?
        )
    ");

    $stmt->execute([
        $walletName,
        $description ?: null,
        $purpose ?: null,
        $targetAmount,
        $monthlyAmount,
        $startDate,
        $endDate,
        $bankName,
        $accountName,
        $accountNumber,
        $visibility,
        $adminId
    ]);


    $walletId = (int)$pdo->lastInsertId();


    /*
    |--------------------------------------------------------------------------
    | Add wallet members
    |--------------------------------------------------------------------------
    */

    if ($visibility === 'selected') {

        $memberStmt = $pdo->prepare("
            INSERT INTO family_wallet_members
            (
                wallet_id,
                user_id
            )
            VALUES (?, ?)
        ");

        foreach ($members as $memberId) {

            $memberId = (int)$memberId;

            if ($memberId <= 0) {
                continue;
            }

            $memberStmt->execute([
                $walletId,
                $memberId
            ]);

        }

    } else {

        /*
        |--------------------------------------------------------------------------
        | All active members
        |--------------------------------------------------------------------------
        */

        $memberStmt = $pdo->prepare("
            INSERT INTO family_wallet_members
            (
                wallet_id,
                user_id
            )
            SELECT
                ?,
                user_id
            FROM users
            WHERE is_deleted = 0
              AND account_status = 'active'
        ");

        $memberStmt->execute([
            $walletId
        ]);

    }


    $pdo->commit();


    header(
        'Location: index.php?success=' .
        urlencode('Wallet created successfully.')
    );

    exit;


} catch (Throwable $e) {

    if ($pdo->inTransaction()) {

        $pdo->rollBack();

    }

    die(
        'Failed to create wallet: '
        . htmlspecialchars($e->getMessage())
    );

}