<?php


require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireTreasurer();

try {
    $database=new Database();
    $pdo=$database->connect();
} catch (Exception $e) {
    die(__('db_connection_failed') . $e->getMessage());
}

$payment_id=(int)($_GET['id'] ?? 0);

if($payment_id <= 0) {
    die(__('invalid_payment_id'));
}

try {
    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | Get Payment
    |--------------------------------------------------------------------------
    */

    $stmt=$pdo->prepare("
SELECT
    fwp.*,
    fw.wallet_name,
    CONCAT(u.first_name,' ',u.last_name) AS member_name

FROM family_wallet_payments fwp

INNER JOIN family_wallets fw
ON fwp.wallet_id=fw.wallet_id

INNER JOIN users u
ON fwp.member_user_id=u.user_id

WHERE fwp.payment_id=?
LIMIT 1
");

    $stmt->execute([$payment_id]);

    $payment=$stmt->fetch(PDO::FETCH_ASSOC);

    if(!$payment) {
        throw new Exception(__('payment_not_found'));
    }

    /*
    |--------------------------------------------------------------------------
    | Check Secretary Status
    |--------------------------------------------------------------------------
    */

    if($payment['secretary_status'] !== 'Forwarded') {
        throw new Exception(__('payment_must_be_forwarded_by_secretary'));
    }

    /*
    |--------------------------------------------------------------------------
    | Check Treasurer Status
    |--------------------------------------------------------------------------
    */

    if($payment['treasurer_status'] !== 'Pending') {
        throw new Exception(__('payment_already_processed_by_treasurer'));
    }

    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */

    $stmt=$pdo->prepare("
UPDATE family_wallet_payments
SET
    treasurer_status = 'Approved',
    approved_by_treasurer = ?,
    treasurer_date = NOW()
WHERE payment_id = ?
");

    $result=$stmt->execute([

$_SESSION['user_id'],

$payment_id

]);

    if(!$result) {
        throw new Exception(__('failed_to_update_payment_status'));
    }

    $rows_affected = $stmt->rowCount();

    if($rows_affected == 0) {
        throw new Exception(__('no_rows_updated_payment_already_processed'));
    }

    // Debug
    error_log("Payment ID $payment_id approved by treasurer. Rows affected: " . $rows_affected);

    /*
    |--------------------------------------------------------------------------
    | Notify Chairperson
    |--------------------------------------------------------------------------
    */

    $chairs=$pdo->query("
SELECT
u.user_id
FROM users u

INNER JOIN roles r
ON u.role_id=r.role_id

WHERE r.role_name='Chairperson'
");

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

    while($c=$chairs->fetch(PDO::FETCH_ASSOC)){

$notify->execute([

$c['user_id'],

__('wallet_payment_waiting'),

"wallet",

$payment['member_name'].__('wallet_payment_for').$payment['wallet_name'].__('waiting_final_approval')

]);

    }

    $pdo->commit();

    header("Location:index.php?success=1");
    exit;

} catch (Exception $e) {
    if($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die(__('approval_error_prefix') . htmlspecialchars($e->getMessage()));
}