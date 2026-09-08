<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: index.php?error=Invalid contribution ID');
    exit;
}

$database = new Database();
$pdo = $database->connect();

try {

    $stmt = $pdo->prepare("
        SELECT contribution_id, title, status
        FROM contributions
        WHERE contribution_id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $contribution = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$contribution) {
        throw new Exception('Contribution not found.');
    }

    if ($contribution['status'] === 'active') {
        throw new Exception('Contribution is already active.');
    }

    $stmt = $pdo->prepare("
        UPDATE contributions
        SET status = 'active'
        WHERE contribution_id = ?
    ");

    $stmt->execute([$id]);

    $adminId = $_SESSION['user_id'] ?? null;

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
        $adminId,
        'Reopen Contribution',
        'contributions',
        $id,
        'Administrator reopened contribution: ' . $contribution['title'],
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);

    header(
        'Location: view.php?id=' .
        $id .
        '&success=' .
        urlencode('Contribution reopened successfully.')
    );

    exit;

} catch (Throwable $e) {

    header(
        'Location: view.php?id=' .
        $id .
        '&error=' .
        urlencode($e->getMessage())
    );

    exit;
}