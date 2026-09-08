<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database = new Database();
$pdo = $database->connect();

$payment_id = (int)$_GET['id'];

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
    header("Location:index.php");
    exit;
}

if ($payment['treasurer_status'] !== 'Approved') {
    $_SESSION['error'] = __('payment_not_approved_by_treasurer');
    header("Location:index.php");
    exit;
}

if ($payment['chairperson_status'] !== 'Pending') {
    $_SESSION['error'] = __('payment_already_processed');
    header("Location:index.php");
    exit;
}

$stmt = $pdo->prepare("
UPDATE contribution_payments
SET

payment_status='rejected',

chairperson_status='Rejected',

approved_by_chairperson=?,

chairperson_date=NOW()

WHERE payment_id=?
");

$stmt->execute([

$_SESSION['user_id'],

$payment_id

]);

$notify = $pdo->prepare("
INSERT INTO notifications
(clan_id,user_id,title,notification_type,message,is_read)
VALUES(?,?,?,?,?,0)
");

$notify->execute([
    $clanId,
    $payment['member_user_id'],
    __('contribution_rejected'),
    'Contribution',
    __('contribution_payment_rejected')
]);

header("Location:../my_contributions/pending_approvals.php?rejected=1");

exit;
