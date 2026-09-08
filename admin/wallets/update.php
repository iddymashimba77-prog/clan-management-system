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

$walletId = (int)($_POST['wallet_id'] ?? 0);

if ($walletId <= 0) {

    die('Invalid wallet ID.');

}


$walletName    = trim($_POST['wallet_name'] ?? '');
$description   = trim($_POST['description'] ?? '');
$purpose       = trim($_POST['purpose'] ?? '');

$targetAmount  = (float)($_POST['target_amount'] ?? 0);
$monthlyAmount = (float)($_POST['monthly_amount'] ?? 0);

$startDate     = $_POST['start_date'] ?? null;
$endDate       = $_POST['end_date'] ?? null;

$bankName      = trim($_POST['bank_name'] ?? '');
$accountName   = trim($_POST['account_name'] ?? '');
$accountNumber = trim($_POST['account_number'] ?? '');

$visibility    = $_POST['visibility'] ?? 'all';

$members       = $_POST['members'] ?? [];


if ($walletName === '') {

    die('Wallet name is required.');

}


if (!in_array($visibility, ['all', 'selected'], true)) {

    $visibility = 'all';

}


try {

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | Update wallet
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE family_wallets
        SET
            wallet_name = ?,
            description = ?,
            purpose = ?,
            target_amount = ?,
            monthly_amount = ?,
            start_date = ?,
            end_date = ?,
            bank_name = ?,
            account_name = ?,
            account_number = ?,
            visibility = ?
        WHERE wallet_id = ?
        LIMIT 1
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
        $walletId

    ]);


    /*
    |--------------------------------------------------------------------------
    | Rebuild wallet members
    |--------------------------------------------------------------------------
    */

    $deleteMembers = $pdo->prepare("
        DELETE FROM family_wallet_members
        WHERE wallet_id = ?
    ");

    $deleteMembers->execute([
        $walletId
    ]);


    if ($visibility === 'selected') {

        $insertMember = $pdo->prepare("
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

            $insertMember->execute([
                $walletId,
                $memberId
            ]);

        }

    } else {

        $insertAll = $pdo->prepare("
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

        $insertAll->execute([
            $walletId
        ]);

    }


    /*
    |--------------------------------------------------------------------------
    | Commit
    |--------------------------------------------------------------------------
    */

    $pdo->commit();


    header(
        'Location: view.php?id='
        . $walletId
        . '&success='
        . urlencode('Wallet updated successfully.')
    );

    exit;


} catch (Throwable $e) {

    if ($pdo->inTransaction()) {

        $pdo->rollBack();

    }

    die(
        'Failed to update wallet: '
        . htmlspecialchars($e->getMessage())
    );

}