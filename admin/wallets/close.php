<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

$database = new Database();
$pdo = $database->connect();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die('Invalid wallet ID.');
}

$user_id = $_SESSION['user_id'] ?? null;

$stmt = $pdo->prepare("
    SELECT wallet_name, status
    FROM family_wallets
    WHERE wallet_id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$wallet = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$wallet) {
    die('Wallet not found.');
}

if ($wallet['status'] === 'closed') {

    header(
        'Location: view.php?id=' .
        $id .
        '&success=already_closed'
    );

    exit;
}

$stmt = $pdo->prepare("
    UPDATE family_wallets
    SET
        status = 'closed',
        end_date = CURDATE()
    WHERE wallet_id = ?
");

$stmt->execute([$id]);

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
    $user_id,
    'Close Wallet',
    'family_wallets',
    $id,
    'Wallet "' . $wallet['wallet_name'] . '" was closed.',
    $_SERVER['REMOTE_ADDR'] ?? null
]);

header(
    'Location: view.php?id=' .
    $id .
    '&success=closed'
);

exit;