<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireTreasurer();

$database = new Database();
$pdo = $database->connect();

$payment_id=(int)($_GET['id'] ?? 0);

try {
    $pdo->beginTransaction();

    // Get payment details for notification
    $stmt=$pdo->prepare("
    SELECT fwp.*, fw.wallet_name, fwp.member_user_id
    FROM family_wallet_payments fwp
    INNER JOIN family_wallets fw ON fwp.wallet_id = fw.wallet_id
    WHERE fwp.payment_id = ?
    ");
    $stmt->execute([$payment_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        die(__('payment_not_found'));
    }

    /*
    |--------------------------------------------------------------------------
    | Reject Payment
    |--------------------------------------------------------------------------
    */

    $stmt=$pdo->prepare("
UPDATE family_wallet_payments
SET

payment_status='rejected',

treasurer_status='Rejected',

approved_by_treasurer=?,

treasurer_date=NOW()

WHERE payment_id=?
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

    $notify=$pdo->prepare("
INSERT INTO notifications(

user_id,
title,
notification_type,
message

)

VALUES(

?,?,?,?

)

");

    $notify->execute([

$payment['member_user_id'],

__('wallet_payment_rejected'),

"wallet",

__('your_payment_for_wallet_prefix').$payment['wallet_name'].__('rejected_by_treasurer')

]);

    $pdo->commit();

    header("Location:index.php?rejected=1");
    exit;

} catch (Exception $e) {
    if($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die(__('rejection_error_prefix') . htmlspecialchars($e->getMessage()));
}