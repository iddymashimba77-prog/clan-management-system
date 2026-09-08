<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php?error=invalid_id');
    exit;
}

$wallet_id = (int) $_GET['id'];

$database = new Database();
$pdo = $database->connect();

try {

    $pdo->beginTransaction();

    // Check wallet exists
    $stmt = $pdo->prepare("
        SELECT wallet_id, wallet_name
        FROM family_wallets
        WHERE wallet_id = ?
        LIMIT 1
    ");
    $stmt->execute([$wallet_id]);

    $wallet = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$wallet) {
        throw new Exception('Wallet not found.');
    }

    /*
     * Delete related records first
     * to avoid foreign-key problems.
     */

    $stmt = $pdo->prepare("
        DELETE FROM wallet_monthly_dues
        WHERE wallet_id = ?
    ");
    $stmt->execute([$wallet_id]);

    $stmt = $pdo->prepare("
        DELETE FROM family_wallet_payments
        WHERE wallet_id = ?
    ");
    $stmt->execute([$wallet_id]);

    $stmt = $pdo->prepare("
        DELETE FROM family_wallet_balances
        WHERE wallet_id = ?
    ");
    $stmt->execute([$wallet_id]);

    $stmt = $pdo->prepare("
        DELETE FROM family_wallet_members
        WHERE wallet_id = ?
    ");
    $stmt->execute([$wallet_id]);

    // Delete wallet
    $stmt = $pdo->prepare("
        DELETE FROM family_wallets
        WHERE wallet_id = ?
    ");
    $stmt->execute([$wallet_id]);

    // Activity log
    $stmt = $pdo->prepare("
        INSERT INTO activity_logs
        (
            user_id,
            action,
            table_name,
            record_id,
            description,
            ip_address
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $_SESSION['user_id'] ?? null,
        'DELETE_WALLET',
        'family_wallets',
        $wallet_id,
        'Deleted wallet: ' . $wallet['wallet_name'],
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);

    $pdo->commit();

    header('Location: index.php?success=deleted');
    exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header(
        'Location: index.php?error=' .
        urlencode($e->getMessage())
    );

    exit;
}