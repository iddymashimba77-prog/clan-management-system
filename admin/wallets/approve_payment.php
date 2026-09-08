<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

$database = new Database();
$pdo = $database->connect();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die('Invalid payment ID.');
}

$user_id = $_SESSION['user_id'] ?? null;

try {

    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | Get payment
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT *
        FROM family_wallet_payments
        WHERE payment_id = ?
        FOR UPDATE
    ");

    $stmt->execute([$id]);

    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        throw new Exception('Payment not found.');
    }

    if ($payment['payment_status'] === 'approved') {
        throw new Exception('Payment is already approved.');
    }

    /*
    |--------------------------------------------------------------------------
    | Approve payment
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE family_wallet_payments

        SET
            payment_status = 'approved',
            treasurer_status = 'Approved',
            chairperson_status = 'Approved',

            approved_by_treasurer = ?,
            approved_by_chairperson = ?,

            treasurer_date = NOW(),
            chairperson_date = NOW(),

            payment_date = NOW()

        WHERE payment_id = ?
    ");

    $stmt->execute([
        $user_id,
        $user_id,
        $id
    ]);

    /*
    |--------------------------------------------------------------------------
    | Update wallet balance
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE family_wallets

        SET current_balance =
            current_balance + ?

        WHERE wallet_id = ?
    ");

    $stmt->execute([
        $payment['amount'],
        $payment['wallet_id']
    ]);

    /*
    |--------------------------------------------------------------------------
    | Update member wallet balance
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT balance_id
        FROM family_wallet_balances
        WHERE wallet_id = ?
          AND user_id = ?
        LIMIT 1
    ");

    $stmt->execute([
        $payment['wallet_id'],
        $payment['member_user_id']
    ]);

    $balance = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($balance) {

        $stmt = $pdo->prepare("
            UPDATE family_wallet_balances

            SET current_balance =
                current_balance + ?

            WHERE balance_id = ?
        ");

        $stmt->execute([
            $payment['amount'],
            $balance['balance_id']
        ]);

    } else {

        $stmt = $pdo->prepare("
            INSERT INTO family_wallet_balances
            (
                wallet_id,
                user_id,
                current_balance
            )

            VALUES (?, ?, ?)
        ");

        $stmt->execute([
            $payment['wallet_id'],
            $payment['member_user_id'],
            $payment['amount']
        ]);

    }

    /*
    |--------------------------------------------------------------------------
    | Update monthly due
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT due_id
        FROM wallet_monthly_dues
        WHERE wallet_id = ?
          AND member_user_id = ?
          AND due_month = ?
        LIMIT 1
    ");

    $stmt->execute([
        $payment['wallet_id'],
        $payment['member_user_id'],
        $payment['payment_month']
    ]);

    $due = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($due) {

        $stmt = $pdo->prepare("
            UPDATE wallet_monthly_dues

            SET
                paid_amount = paid_amount + ?,
                balance = GREATEST(
                    required_amount -
                    (paid_amount + ?),
                    0
                ),

                status =
                    CASE

                        WHEN
                            (paid_amount + ?) >= required_amount
                        THEN 'Completed'

                        WHEN
                            (paid_amount + ?) > 0
                        THEN 'Partial'

                        ELSE 'Pending'

                    END

            WHERE due_id = ?
        ");

        $stmt->execute([
            $payment['amount'],
            $payment['amount'],
            $payment['amount'],
            $payment['amount'],
            $due['due_id']
        ]);

    }

    /*
    |--------------------------------------------------------------------------
    | Activity log
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO activity_logs
        (
            user_id,
            action,
            table_name,
            record_id,
            description,
            ip_address
        )

        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $user_id,
        'Approve Wallet Payment',
        'family_wallet_payments',
        $id,
        'Wallet payment approved by administrator.',
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);

    $pdo->commit();

    header(
        'Location: payment_view.php?id=' .
        $id .
        '&success=approved'
    );

    exit;

} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die(
        'Unable to approve payment: ' .
        htmlspecialchars($e->getMessage())
    );
}