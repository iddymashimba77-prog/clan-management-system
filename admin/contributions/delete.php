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

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        SELECT
            contribution_id,
            title,
            status
        FROM contributions
        WHERE contribution_id = ?
        LIMIT 1
        FOR UPDATE
    ");

    $stmt->execute([$id]);

    $contribution = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$contribution) {
        throw new Exception('Contribution not found.');
    }

    /*
    |--------------------------------------------------------------------------
    | CHECK PAYMENTS
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM contribution_payments
        WHERE contribution_id = ?
    ");

    $stmt->execute([$id]);

    $paymentCount = (int)$stmt->fetchColumn();

    if ($paymentCount > 0) {
        throw new Exception(
            'This contribution cannot be deleted because it has payment records.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE PAYMENT METHODS/LINKS
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        DELETE FROM contribution_method_links
        WHERE contribution_id = ?
    ");

    $stmt->execute([$id]);

    /*
    |--------------------------------------------------------------------------
    | DELETE CONTRIBUTION
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        DELETE FROM contributions
        WHERE contribution_id = ?
    ");

    $stmt->execute([$id]);

    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOG
    |--------------------------------------------------------------------------
    */

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
        'Delete Contribution',
        'contributions',
        $id,
        'Administrator deleted contribution: ' .
        $contribution['title'],
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);

    $pdo->commit();

    header(
        'Location: index.php?success=' .
        urlencode('Contribution deleted successfully.')
    );

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