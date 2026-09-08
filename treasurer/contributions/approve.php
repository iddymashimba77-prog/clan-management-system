<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireTreasurer();

$database = new Database();
$pdo = $database->connect();

$payment_id = (int)($_GET['id'] ?? 0);

$clanId = (int)($_SESSION['clan_id'] ?? 1);

$stmt = $pdo->prepare("
SELECT *
FROM contribution_payments
WHERE payment_id = ?
LIMIT 1
");

$stmt->execute([$payment_id]);

$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    $_SESSION['error'] = __('payment_not_found');
    header("Location:../my_contributions/pending_approvals.php");
    exit;
}

if ($payment['secretary_status'] !== 'Forwarded') {
    $_SESSION['error'] = __('payment_must_be_forwarded_by_secretary');
    header("Location:../my_contributions/pending_approvals.php");
    exit;
}

if ($payment['treasurer_status'] !== 'Pending') {
    $_SESSION['error'] = __('payment_already_processed_by_treasurer');
    header("Location:../my_contributions/pending_approvals.php");
    exit;
}

$stmt = $pdo->prepare("
UPDATE contribution_payments
SET

treasurer_status='Approved',
approved_by_treasurer=?,
treasurer_date=NOW()

WHERE payment_id=?
");

$stmt->execute([

$_SESSION['user_id'],

$payment_id

]);

$stmt=$pdo->prepare("
SELECT user_id
FROM users
WHERE role_id=1
AND account_status='active'
LIMIT 1
");

$stmt->execute();

$chairperson=$stmt->fetch(PDO::FETCH_ASSOC);

if ($chairperson) {

    $notify=$pdo->prepare("
INSERT INTO notifications
(
clan_id,
user_id,
title,
notification_type,
message
)
VALUES
(?,?,?,?,?)
");

    $notify->execute([

        $clanId,
        $chairperson['user_id'],

        __('final_approval_required'),

        "payment",

        __('contribution_waiting_final_approval')

    ]);
}

header("Location:../my_contributions/pending_approvals.php?approved=1");

exit;
