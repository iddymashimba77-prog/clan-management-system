<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database = new Database();
$pdo = $database->connect();

$wallet_id = (int)($_GET['id'] ?? 0);

if ($wallet_id <= 0) {
    die("Invalid wallet.");
}

/*
|--------------------------------------------------------------------------
| Get Wallet
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM family_wallets
    WHERE wallet_id = ?
    AND status = 'active'
    LIMIT 1
");

$stmt->execute([$wallet_id]);

$wallet = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$wallet) {
    die("Wallet not found.");
}

$clanId = (int)($wallet['clan_id'] ?? 1);

/*
|--------------------------------------------------------------------------
| Current Month
|--------------------------------------------------------------------------
*/

$month = date('Y-m-01');

/*
|--------------------------------------------------------------------------
| Get Wallet Members
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT user_id
    FROM family_wallet_members
    WHERE wallet_id = ?
");

$stmt->execute([$wallet_id]);

$members = $stmt->fetchAll(PDO::FETCH_COLUMN);

/*
|--------------------------------------------------------------------------
| Create Monthly Dues
|--------------------------------------------------------------------------
*/

$insert = $pdo->prepare("
    INSERT INTO wallet_monthly_dues
    (
        clan_id,
        wallet_id,
        member_user_id,
        due_month,
        required_amount,
        paid_amount,
        balance,
        status
    )
    VALUES
    (
        ?, ?, ?, ?, ?, 0, ?, 'Pending'
    )
");

/*
|--------------------------------------------------------------------------
| Avoid Duplicate Month
|--------------------------------------------------------------------------
*/

$check = $pdo->prepare("
    SELECT due_id
    FROM wallet_monthly_dues
    WHERE wallet_id = ?
    AND member_user_id = ?
    AND due_month = ?
    LIMIT 1
");

foreach ($members as $member_user_id) {

    $check->execute([
        $wallet_id,
        $member_user_id,
        $month
    ]);

    if ($check->fetch()) {
        continue;
    }

    /*
    |--------------------------------------------------------------------------
    | Previous Carry Forward
    |--------------------------------------------------------------------------
    */

    $previous = $pdo->prepare("
        SELECT
            COALESCE(carry_forward,0) AS carry_forward
        FROM family_wallet_members
        WHERE wallet_id = ?
        AND user_id = ?
        LIMIT 1
    ");

    $previous->execute([
        $wallet_id,
        $member_user_id
    ]);

    $carry = (float)$previous->fetchColumn();

    /*
    |--------------------------------------------------------------------------
    | Monthly Requirement
    |--------------------------------------------------------------------------
    */

    $monthly_amount = (float)$wallet['monthly_amount'];

    $required_amount = max(
        0,
        $monthly_amount - $carry
    );

    $insert->execute([
        $clanId,
        $wallet_id,
        $member_user_id,
        $month,
        $required_amount,
        $required_amount
    ]);

    /*
    |--------------------------------------------------------------------------
    | Reset Carry Forward After Applying It
    |--------------------------------------------------------------------------
    */

    $reset = $pdo->prepare("
        UPDATE family_wallet_members
        SET carry_forward = 0
        WHERE wallet_id = ?
        AND user_id = ?
    ");

    $reset->execute([
        $wallet_id,
        $member_user_id
    ]);
}

/*
|--------------------------------------------------------------------------
| Update Expected Amount
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM family_wallet_members
    WHERE wallet_id = ?
");

$stmt->execute([$wallet_id]);

$total_members = (int)$stmt->fetchColumn();

$expected_amount =
    $total_members *
    (float)$wallet['monthly_amount'] *
    12;

$stmt = $pdo->prepare("
    UPDATE family_wallets
    SET expected_amount = ?
    WHERE wallet_id = ?
");

$stmt->execute([
    $expected_amount,
    $wallet_id
]);

header(
    "Location: index.php?generated=1"
);

exit;