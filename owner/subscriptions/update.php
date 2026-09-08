<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/maindb.php';

requireOwnerLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: index.php');

    exit;
}

requireOwnerCsrf();

$clanId =
    (int)($_POST['clan_id'] ?? 0);

$planName =
    trim($_POST['plan_name'] ?? '');

$subscriptionStatus =
    trim($_POST['subscription_status'] ?? 'active');

$activationStatus =
    trim($_POST['activation_status'] ?? 'active');

$maxMembers =
    (int)($_POST['max_members'] ?? 0);

$subscriptionExpiresAt =
    trim($_POST['subscription_expires_at'] ?? '');

if ($clanId <= 0) {

    exit('Invalid clan.');
}

if ($planName === '') {

    exit('Plan name is required.');
}

$allowedSubscription = [
    'trial',
    'active',
    'expired',
    'suspended',
    'cancelled'
];

$allowedActivation = [
    'pending',
    'active',
    'suspended',
    'revoked'
];

if (!in_array($subscriptionStatus, $allowedSubscription, true)) {

    $subscriptionStatus = 'active';
}

if (!in_array($activationStatus, $allowedActivation, true)) {

    $activationStatus = 'active';
}

if ($maxMembers < 0) {

    $maxMembers = 0;
}

if ($subscriptionExpiresAt !== '' &&
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $subscriptionExpiresAt)
) {

    $subscriptionExpiresAt = '';
}

$clanDatabase = new OwnerClanDatabase();
$pdo = $clanDatabase->connect();

$ownerDatabase = new OwnerDatabase();
$ownerPdo = $ownerDatabase->connect();

try {

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        SELECT clan_id, clan_name, clan_code
        FROM clans
        WHERE clan_id = ?
        LIMIT 1
    ");

    $stmt->execute([$clanId]);

    $clan = $stmt->fetch();

    if (!$clan) {

        throw new RuntimeException('Clan not found.');
    }

    $stmt = $pdo->prepare("
        UPDATE clans
        SET
            plan_id = ?,
            plan_name = ?,
            max_members = ?,
            subscription_status = ?,
            activation_status = ?,
            subscription_expires_at = ?
        WHERE clan_id = ?
    ");

    $stmt->execute([

        $clanId,
        $planName,
        $maxMembers,
        $subscriptionStatus,
        $activationStatus,
        $subscriptionExpiresAt !== ''
            ? $subscriptionExpiresAt
            : null,
        $clanId

    ]);

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
            'update_subscription',
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
        'Updated ' . $planName . ' subscription for clan ' .
        $clan['clan_code'] . ' (' . $clan['clan_name'] . ').',
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null

    ]);

    $pdo->commit();

    header(
        'Location: index.php?success=' .
        urlencode('Subscription updated successfully.')
    );

    exit;

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
