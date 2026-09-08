<?php


require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireSecretary();

$database = new Database();
$pdo = $database->connect();

$payment_id = (int)($_GET['id'] ?? 0);

if ($payment_id <= 0) {
    die(__('invalid_payment_id'));
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
            CONCAT(u.first_name, ' ', u.last_name) AS member_name

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
    | Check Secretary Status
    |--------------------------------------------------------------------------
    */

    if ($payment['secretary_status'] !== 'Pending') {
        throw new Exception(
            __('payment_already_forwarded')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Forward Payment
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE family_wallet_payments

        SET
            secretary_status = 'Forwarded'

        WHERE payment_id = ?
    ");

    $stmt->execute([$payment_id]);

    /*
    |--------------------------------------------------------------------------
    | Find Treasurer
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->query("
        SELECT u.user_id

        FROM users u

        INNER JOIN roles r
            ON u.role_id = r.role_id

        WHERE r.role_name = 'Treasurer'

        AND u.account_status = 'active'
    ");

    $treasurers =
        $stmt->fetchAll(PDO::FETCH_COLUMN);

    /*
    |--------------------------------------------------------------------------
    | Notification
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

    foreach ($treasurers as $treasurer_id) {

        $notify->execute([

            $treasurer_id,

            __('wallet_payment_forwarded'),

            'wallet',

            sprintf(
                __('wallet_payment_forwarded_message'),
                $payment['member_name'],
                number_format(
                    (float)$payment['amount'],
                    2
                ),
                $payment['wallet_name']
            )

        ]);
    }

    $pdo->commit();

    header(
        "Location: index.php?forwarded=1"
    );

    exit;

} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die(
        __('forward_error_prefix')
        . htmlspecialchars(
            $e->getMessage()
        )
    );
}