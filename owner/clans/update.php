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

$clanName =
    trim($_POST['clan_name'] ?? '');

$contactEmail =
    trim($_POST['contact_email'] ?? '');

$clanPhone =
    trim($_POST['clan_phone'] ?? '');

$clanAddress =
    trim($_POST['clan_address'] ?? '');

$clanMotto =
    trim($_POST['clan_motto'] ?? '');

$activationStatus =
    trim($_POST['activation_status'] ?? 'pending');

$subscriptionStatus =
    trim($_POST['subscription_status'] ?? 'trial');

$subscriptionExpiresAt =
    trim($_POST['subscription_expires_at'] ?? '');

if ($clanId <= 0) {

    exit('Invalid clan.');
}

if ($clanName === '') {

    exit('Clan name is required.');
}

if (
    $contactEmail !== '' &&
    !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)
) {

    exit('Invalid contact email.');
}

$allowedActivation = [
    'pending',
    'active',
    'suspended',
    'revoked'
];

$allowedSubscription = [
    'trial',
    'active',
    'expired',
    'suspended',
    'cancelled'
];

if (!in_array($activationStatus, $allowedActivation, true)) {

    $activationStatus = 'pending';
}

if (!in_array($subscriptionStatus, $allowedSubscription, true)) {

    $subscriptionStatus = 'trial';
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
        SELECT clan_id
        FROM clans
        WHERE clan_id = ?
        LIMIT 1
    ");

    $stmt->execute([$clanId]);

    if (!$stmt->fetch()) {

        throw new RuntimeException('Clan not found.');
    }


    $stmt = $pdo->prepare("
        UPDATE clans
        SET
            clan_name = ?,
            contact_email = ?,
            clan_phone = ?,
            clan_address = ?,
            clan_motto = ?,
            activation_status = ?,
            subscription_status = ?,
            subscription_expires_at = ?
        WHERE clan_id = ?
    ");

    $stmt->execute([

        $clanName,
        $contactEmail !== '' ? $contactEmail : null,
        $clanPhone !== '' ? $clanPhone : null,
        $clanAddress !== '' ? $clanAddress : null,
        $clanMotto !== '' ? $clanMotto : null,
        $activationStatus,
        $subscriptionStatus,
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
            'update_clan',
            'clan',
            ?,
            ?,
            ?,
            ?
        )
    ");

    $stmt->execute([

        (int)$_SESSION['owner_id'],
        $clanId,
        'Updated clan #' . $clanId . ' (' . $clanName . ')',
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null

    ]);


    $pdo->commit();

    header(
        'Location: view.php?id=' . $clanId .
        '&action=updated'
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
