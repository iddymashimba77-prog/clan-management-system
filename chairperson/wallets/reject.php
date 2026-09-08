<?php


require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$payment_id = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_id = (int)($_POST['payment_id'] ?? 0);
} else {
    $payment_id = (int)($_GET['id'] ?? 0);
}

if ($payment_id <= 0) {
    die(__('invalid_payment'));
}

$database = new Database();
$pdo = $database->connect();

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
            CONCAT(
                u.first_name,
                ' ',
                u.last_name
            ) AS member_name

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
    | Check Treasurer Approval
    |--------------------------------------------------------------------------
    */

    if ($payment['treasurer_status'] !== 'Approved') {
        throw new Exception(__('wallet_payment_must_be_approved_by_treasurer'));
    }

    /*
    |--------------------------------------------------------------------------
    | Check Chairperson Status
    |--------------------------------------------------------------------------
    */

    if ($payment['chairperson_status'] !== 'Pending') {
        throw new Exception(__('payment_already_processed'));
    }

    /*
    |--------------------------------------------------------------------------
    | Reject Payment
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE family_wallet_payments

        SET
            chairperson_status = 'Rejected',
            payment_status = 'rejected',
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

    $notify->execute([

        $payment['member_user_id'],

        __('wallet_payment_rejected'),

        'wallet',

        __('wallet_payment_rejected_message_prefix')
        . number_format(
            (float)$payment['amount'],
            2
        )
        . __('wallet_payment_rejected_message_mid')
        . $payment['wallet_name']
        . __('wallet_payment_rejected_message_suffix')

    ]);

    /*
    |--------------------------------------------------------------------------
    | Commit
    |--------------------------------------------------------------------------
    */

    $pdo->commit();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header(
            "Location: payments.php?id="
            . $payment_id
            . "&rejected=1"
        );
    } else {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (strpos($referer, 'all_payments.php') !== false) {
            header("Location: all_payments.php?rejected=1");
        } else {
            header("Location: pending_approvals.php?rejected=1");
        }
    }

    exit;

} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die(
        __('rejection_error_prefix')
        . htmlspecialchars(
            $e->getMessage()
        )
    );
}