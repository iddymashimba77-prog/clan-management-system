<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database = new Database();
$pdo = $database->connect();

$payment_id = (int)($_GET['id'] ?? 0);

if ($payment_id <= 0) {
    die(__('invalid_payment'));
}

try {

    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | Get Payment
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            fwp.*,
            fw.wallet_name,
            fw.monthly_amount,
            CONCAT(u.first_name,' ',u.last_name) AS member_name

        FROM family_wallet_payments fwp

        INNER JOIN family_wallets fw
            ON fwp.wallet_id = fw.wallet_id

        INNER JOIN users u
            ON fwp.member_user_id = u.user_id

        WHERE fwp.payment_id = ?

        LIMIT 1
    ");

    $stmt->execute([$payment_id]);

    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        throw new Exception(__('payment_not_found'));
    }

    /*
    |--------------------------------------------------------------------------
    | Must be Treasurer Approved
    |--------------------------------------------------------------------------
    */

    if ($payment['treasurer_status'] !== 'Approved') {
        throw new Exception(
            __('payment_not_approved_by_treasurer')
        );
    }

    if ($payment['chairperson_status'] !== 'Pending') {
        throw new Exception(
            __('payment_already_processed')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Find Monthly Due
    |--------------------------------------------------------------------------
    */

    $payment_month = date(
        'Y-m-01',
        strtotime($payment['payment_month'])
    );

    $stmt = $pdo->prepare("
        SELECT *
        FROM wallet_monthly_dues

        WHERE wallet_id = ?
        AND member_user_id = ?
        AND due_month = ?

        LIMIT 1
    ");

    $stmt->execute([

        $payment['wallet_id'],
        $payment['member_user_id'],
        $payment_month

    ]);

    $due = $stmt->fetch(PDO::FETCH_ASSOC);

    /*
    |--------------------------------------------------------------------------
    | Create Monthly Due If Missing
    |--------------------------------------------------------------------------
    */

    if (!$due) {

        $stmt = $pdo->prepare("
            INSERT INTO wallet_monthly_dues
            (
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
                ?, ?, ?, ?, 0, ?, 'Pending'
            )
        ");

        $monthly = (float)$payment['monthly_amount'];

        $stmt->execute([

            $payment['wallet_id'],

            $payment['member_user_id'],

            $payment_month,

            $monthly,

            $monthly

        ]);

        $due_id = $pdo->lastInsertId();

        $required_amount = $monthly;
        $paid_before = 0;

    } else {

        $due_id = $due['due_id'];

        $required_amount =
            (float)$due['required_amount'];

        $paid_before =
            (float)$due['paid_amount'];
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate Payment
    |--------------------------------------------------------------------------
    */

    $payment_amount =
        (float)$payment['amount'];

    $remaining_before =
        max(
            0,
            $required_amount - $paid_before
        );

    /*
    | Amount used for current month
    */

    $amount_applied =
        min(
            $payment_amount,
            $remaining_before
        );

    /*
    | Excess becomes carry forward
    */

    $overpayment =
        max(
            0,
            $payment_amount - $amount_applied
        );

    /*
    |--------------------------------------------------------------------------
    | New Monthly Values
    |--------------------------------------------------------------------------
    */

    $new_paid =
        $paid_before + $amount_applied;

    $new_balance =
        max(
            0,
            $required_amount - $new_paid
        );

    if ($new_balance <= 0) {

        $status = 'Completed';

    } elseif ($new_paid > 0) {

        $status = 'Partial';

    } else {

        $status = 'Pending';
    }

    /*
    |--------------------------------------------------------------------------
    | Update Monthly Due
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE wallet_monthly_dues

        SET
            paid_amount = ?,
            balance = ?,
            status = ?

        WHERE due_id = ?
    ");

    $stmt->execute([

        $new_paid,

        $new_balance,

        $status,

        $due_id

    ]);

    /*
    |--------------------------------------------------------------------------
    | Update Member Balance + Carry Forward
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            COALESCE(carry_forward,0)

        FROM family_wallet_members

        WHERE wallet_id = ?
        AND user_id = ?

        LIMIT 1
    ");

    $stmt->execute([

        $payment['wallet_id'],

        $payment['member_user_id']

    ]);

    $old_carry =
        (float)$stmt->fetchColumn();

    $new_carry =
        $old_carry + $overpayment;

    $stmt = $pdo->prepare("
        UPDATE family_wallet_members

        SET
            current_balance = ?,
            carry_forward = ?,
            last_paid_month = ?

        WHERE wallet_id = ?
        AND user_id = ?
    ");

    $stmt->execute([

        $new_balance,

        $new_carry,

        $payment_month,

        $payment['wallet_id'],

        $payment['member_user_id']

    ]);

    /*
    |--------------------------------------------------------------------------
    | Final Payment Approval
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE family_wallet_payments

        SET
            payment_status = 'approved',
            chairperson_status = 'Approved',
            approved_by_chairperson = ?,
            chairperson_date = NOW()

        WHERE payment_id = ?
    ");

    $stmt->execute([

        $_SESSION['user_id'],

        $payment_id

    ]);

    /*
    |--------------------------------------------------------------------------
    | Update Wallet Current Balance
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            COALESCE(SUM(amount),0)

        FROM family_wallet_payments

        WHERE wallet_id = ?

        AND payment_status = 'approved'
    ");

    $stmt->execute([
        $payment['wallet_id']
    ]);

    $total_collected =
        (float)$stmt->fetchColumn();

    $stmt = $pdo->prepare("
        UPDATE family_wallets

        SET current_balance = ?

        WHERE wallet_id = ?
    ");

    $stmt->execute([

        $total_collected,

        $payment['wallet_id']

    ]);

    /*
    |--------------------------------------------------------------------------
    | Notify Member
    |--------------------------------------------------------------------------
    */

    $notify = $pdo->prepare("
        INSERT INTO notifications
        (
            user_id,
            title,
            notification_type,
            message
        )

        VALUES
        (?, ?, ?, ?)
    ");

    $message =
        __('wallet_payment_approved_message_prefix')
        . number_format($payment_amount, 2)
        . __('wallet_payment_approved_message_mid')
        . $payment['wallet_name']
        . __('wallet_payment_approved_message_suffix');

    if ($new_balance > 0) {

        $message .=
            __('wallet_payment_approved_remaining_prefix')
            . number_format($new_balance, 2)
            . ".";

    } elseif ($new_carry > 0) {

        $message .=
            __('wallet_payment_approved_excess_prefix')
            . number_format($new_carry, 2)
            . __('wallet_payment_approved_excess_suffix');
    }

    $notify->execute([

        $payment['member_user_id'],

        __('wallet_payment_approved'),

        "wallet",

        $message

    ]);


    $pdo->commit();

$referer = $_SERVER['HTTP_REFERER'] ?? '';
if (strpos($referer, 'all_payments.php') !== false) {
    header("Location: all_payments.php?approved=1");
} elseif (strpos($referer, 'pending_approvals.php') !== false) {
    header("Location: pending_approvals.php?approved=1");
} else {
    header("Location: index.php?approved=1");
}
exit;

} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die(__('error_prefix') . $e->getMessage());
}

