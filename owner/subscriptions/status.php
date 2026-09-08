<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/maindb.php';

requireOwnerLogin();
requireOwnerCsrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    exit('Method Not Allowed');
}

$clanId =
    (int)($_POST['clan_id'] ?? 0);

$newStatus =
    trim($_POST['subscription_status'] ?? '');

if ($clanId <= 0) {

    exit('Invalid clan.');
}

$allowedSubscription = [
    'trial',
    'active',
    'expired',
    'suspended',
    'cancelled'
];

if (!in_array($newStatus, $allowedSubscription, true)) {

    exit('Invalid subscription status.');
}

$clanDatabase = new OwnerClanDatabase();
$pdo = $clanDatabase->connect();

$ownerDatabase = new OwnerDatabase();
$ownerPdo = $ownerDatabase->connect();

try {

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        SELECT clan_id, clan_code, clan_name
        FROM clans
        WHERE clan_id = ?
        FOR UPDATE
    ");

    $stmt->execute([$clanId]);

    $clan = $stmt->fetch();

    if (!$clan) {

        throw new RuntimeException('Clan not found.');
    }

    $stmt = $pdo->prepare("
        UPDATE clans
        SET subscription_status = ?
        WHERE clan_id = ?
    ");

    $stmt->execute([$newStatus, $clanId]);

    $stmt = $ownerPdo->prepare("
        INSERT INTO owner_activity_logs
        (
            owner_id,
            action,
            entity_type,
            entity_id,
            description,
            ip_address,
            user_agent
        )
        VALUES
        (
            ?,
            'update_subscription_status',
            'subscription',
            ?,
            ?,
            ?,
            ?
        )
    ");

    $stmt->execute([

        (int)$_SESSION['owner_id'],
        $clanId,
        'Set subscription status to ' . $newStatus .
        ' for clan ' . $clan['clan_code'] .
        ' (' . $clan['clan_name'] . ')',
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null

    ]);

    $pdo->commit();

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {

        $pdo->rollBack();
    }

    http_response_code(400);

    exit(
        htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        )
    );
}

header(
    'Location: view.php?id=' . $clanId .
    '&success=' . urlencode(
        'Subscription status updated to ' . $newStatus . '.'
    )
);

exit;
