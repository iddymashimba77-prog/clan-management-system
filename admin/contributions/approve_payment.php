<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';
require_once '../../config/language.php';
require_once '../../config/tenant.php';

requireLogin();
requireTenantContext();

$clanId = (int) getCurrentClanId();

$id = (int)($_GET['id'] ?? $_POST['payment_id'] ?? 0);

if ($id <= 0) {
    header('Location: payments.php?error=Invalid payment');
    exit;
}

$database = new Database();
$pdo = $database->connect();

try {

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        SELECT
            payment_id,
            contribution_id,
            amount,
            payment_status
        FROM contribution_payments
        WHERE payment_id = ?
          AND clan_id = ?
        FOR UPDATE
    ");

    $stmt->execute([$id, $clanId]);

    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        throw new Exception('Payment not found.');
    }

    if ($payment['payment_status'] === 'approved') {
        throw new Exception('Payment is already approved.');
    }

    if ($payment['payment_status'] === 'rejected') {
        throw new Exception('Rejected payment cannot be approved.');
    }


    /*
    |--------------------------------------------------------------------------
    | APPROVE PAYMENT
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE contribution_payments
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
          AND clan_id = ?
    ");

    $adminId = $_SESSION['user_id'] ?? null;

    $stmt->execute([
        $adminId,
        $adminId,
        $id,
        $clanId
    ]);


    /*
    |--------------------------------------------------------------------------
    | UPDATE CONTRIBUTION TOTALS
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE contributions
        SET
            current_amount = (
                SELECT COALESCE(SUM(amount), 0)
                FROM contribution_payments
                WHERE contribution_id = ?
                  AND clan_id = ?
                  AND payment_status = 'approved'
            ),

            total_paid_members = (
                SELECT COUNT(DISTINCT member_user_id)
                FROM contribution_payments
                WHERE contribution_id = ?
                  AND clan_id = ?
                  AND payment_status = 'approved'
            ),

            total_pending_members = (
                SELECT COUNT(DISTINCT member_user_id)
                FROM contribution_payments
                WHERE contribution_id = ?
                  AND clan_id = ?
                  AND payment_status = 'pending'
            ),

            progress = LEAST(
                100,
                (
                    (
                        SELECT COALESCE(SUM(amount), 0)
                        FROM contribution_payments
                        WHERE contribution_id = ?
                          AND clan_id = ?
                          AND payment_status = 'approved'
                    )
                    / NULLIF(target_amount, 0)
                ) * 100
            )

        WHERE contribution_id = ?
          AND clan_id = ?
    ");

    $stmt->execute([
        $payment['contribution_id'],
        $clanId,
        $payment['contribution_id'],
        $clanId,
        $payment['contribution_id'],
        $clanId,
        $payment['contribution_id'],
        $clanId,
        $payment['contribution_id'],
        $clanId
    ]);


    /*
    |--------------------------------------------------------------------------
    | NOTIFICATION
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT member_user_id, contribution_id
        FROM contribution_payments
        WHERE payment_id = ?
          AND clan_id = ?
        LIMIT 1
    ");

    $stmt->execute([$id, $clanId]);

    $paymentOwner = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($paymentOwner) {

        $stmt = $pdo->prepare("
            SELECT title
            FROM contributions
            WHERE contribution_id = ?
              AND clan_id = ?
            LIMIT 1
        ");

        $stmt->execute([
            $paymentOwner['contribution_id'],
            $clanId
        ]);

        $contribution = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("
            INSERT INTO notifications
            (
                user_id,
                clan_id,
                title,
                notification_type,
                message
            )
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $paymentOwner['member_user_id'],
            $clanId,
            'Contribution Payment Approved',
            'contribution_payment',
            'Your payment for "' .
            ($contribution['title'] ?? 'Contribution') .
            '" has been approved.'
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOG
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO activity_logs
        (
            user_id,
            clan_id,
            action,
            table_name,
            record_id,
            description,
            ip_address
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $adminId,
        $clanId,
        'Approve Contribution Payment',
        'contribution_payments',
        $id,
        'Administrator approved contribution payment.',
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);

    $pdo->commit();

    header(
        'Location: payment_view.php?id=' .
        $id .
        '&success=' .
        urlencode('Payment approved successfully.')
    );

    exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header(
        'Location: payment_view.php?id=' .
        $id .
        '&error=' .
        urlencode($e->getMessage())
    );

    exit;
}