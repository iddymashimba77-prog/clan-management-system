<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireTreasurer();

try {
    $database = new Database();
    $pdo = $database->connect();
} catch (Exception $e) {
    die(__('db_connection_failed') . $e->getMessage());
}

$clanId = getCurrentClanId();

$payment_id = (int)($_GET['id'] ?? 0);

try {
    $stmt = $pdo->prepare("
UPDATE contribution_payments
SET
treasurer_status = 'Rejected',
rejected_by_treasurer = ?,
treasurer_date = NOW()
WHERE payment_id = ?
AND clan_id = ?
");

    $stmt->execute([$_SESSION['user_id'], $payment_id, $clanId]);

    header("Location:pending_approvals.php?rejected=1");
    exit;
} catch (PDOException $e) {
    die(__('update_failed') . $e->getMessage());
}
