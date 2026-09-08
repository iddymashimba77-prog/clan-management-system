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

$stmt = $pdo->prepare("
UPDATE contribution_payments
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

header("Location:../my_contributions/pending_approvals.php?rejected=1");

exit;
