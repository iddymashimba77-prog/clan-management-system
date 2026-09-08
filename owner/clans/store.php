<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/maindb.php';
require_once __DIR__ . '/../../config/clan_db.php';

requireOwnerLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: create.php');

    exit;
}

requireOwnerCsrf();

$clanName =
    trim($_POST['clan_name'] ?? '');

$clanCode =
    trim($_POST['clan_code'] ?? '');

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

$databaseName =
    trim($_POST['database_name'] ?? '');

$adminUsername =
    trim($_POST['admin_username'] ?? '');

$adminPassword =
    (string)($_POST['admin_password'] ?? '');

$adminEmail =
    trim($_POST['admin_email'] ?? '');

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

if ($clanName === '') {

    exit('Clan name is required.');
}

if (
    $contactEmail !== '' &&
    !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)
) {

    exit('Invalid contact email.');
}

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


/*
|--------------------------------------------------------------------------
| Dedicated database name resolution
|--------------------------------------------------------------------------
*/

$resolvedDatabaseName = '';

if ($databaseName !== '') {

    $sanitized = preg_replace('/[^a-zA-Z0-9_]/', '', $databaseName);

    if ($sanitized === '') {

        exit('Invalid database name. Only letters, numbers and underscores are allowed.');
    }

    // Force a "clan_" prefix so every tenant database is grouped
    // together under the "clan" node in phpMyAdmin.
    if (stripos($sanitized, 'clan_') !== 0) {
        $sanitized = 'clan_' . $sanitized;
    }

    $resolvedDatabaseName = $sanitized;
}

if ($resolvedDatabaseName !== '') {

    if ($adminUsername === '') {

        exit('Clan administrator username is required when creating a dedicated database.');
    }

    if (strlen($adminPassword) < 8) {

        exit('Clan administrator password must be at least 8 characters.');
    }

    if (
        $adminEmail !== '' &&
        !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)
    ) {

        exit('Invalid clan administrator email.');
    }
}


$clanDatabase = new OwnerClanDatabase();

$pdo = $clanDatabase->connect();

$ownerDatabase = new OwnerDatabase();
$ownerPdo = $ownerDatabase->connect();

try {

    if ($resolvedDatabaseName === '') {

        $pdo->beginTransaction();
    }

    $clanId = null;

    if ($resolvedDatabaseName !== '') {

        /*
         |--------------------------------------------------------------------------
         | Database-per-clan: dedicated database
         |--------------------------------------------------------------------------
         |
         | Determine the next globally-unique clan_id from the owner
         | installations sequence, then auto-create the clan's own database
         | with the exact same structure as the original. The clan
         | administrator becomes the first member of the clan.
         |
         */

        $stmt = $ownerPdo->query("SELECT COALESCE(MAX(clan_id), 0) FROM owner_installations");
        $clanId = (int)$stmt->fetchColumn() + 1;

        $autoClanCode = 'CLN-' . str_pad((string)$clanId, 5, '0', STR_PAD_LEFT);

        if ($clanCode === '') {
            $clanCode = $autoClanCode;
        } else {
            $clanCode = strtoupper($clanCode);
        }

        createClanDatabase([
            'db_name'             => $resolvedDatabaseName,
            'clan_id'             => $clanId,
            'clan_name'           => $clanName,
            'clan_code'           => $clanCode,
            'contact_email'       => $contactEmail,
            'activation_status'   => $activationStatus,
            'subscription_status' => $subscriptionStatus,
            'admin_name'          => $clanName,
            'admin_username'      => $adminUsername,
            'admin_password'      => $adminPassword,
            'admin_email'         => $adminEmail,
        ]);


        /*
         |--------------------------------------------------------------------------
         | Register installation for login routing
         |--------------------------------------------------------------------------
         |
         | A dedicated database must be discoverable at login, so we record
         | an owner installation that maps database_name -> clan_id.
         |
         */

        $installationCode =
            'CMS-' .
            strtoupper(
                bin2hex(random_bytes(6))
            );

        $stmt = $ownerPdo->prepare("
            INSERT INTO owner_installations
            (
                installation_code,
                clan_id,
                client_name,
                client_email,
                database_name,
                installation_status
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
            )
        ");

        $stmt->execute([
            $installationCode,
            $clanId,
            $clanName,
            $contactEmail !== '' ? $contactEmail : null,
            $resolvedDatabaseName,
            ($activationStatus === 'active') ? 'active' : 'pending'
        ]);

        $installationId = (int)$ownerPdo->lastInsertId();

    } else {

        /*
         |--------------------------------------------------------------------------
         | Legacy: clan stored in the shared database
         |--------------------------------------------------------------------------
         */

        if ($clanCode === '') {

            $stmt = $pdo->query("SELECT MAX(clan_id) FROM clans");
            $nextId = (int)$stmt->fetchColumn() + 1;
            $clanCode = 'CLN-' . str_pad((string)$nextId, 5, '0', STR_PAD_LEFT);

        } else {

            $clanCode = strtoupper($clanCode);

            $stmt = $pdo->prepare("
                SELECT clan_id FROM clans
                WHERE clan_code = ?
                LIMIT 1
            ");

            $stmt->execute([$clanCode]);

            if ($stmt->fetch()) {

                throw new RuntimeException(
                    'Clan code already exists.'
                );
            }
        }

        $stmt = $pdo->prepare("
            INSERT INTO clans
            (
                clan_code,
                clan_name,
                contact_email,
                clan_phone,
                clan_address,
                clan_motto,
                activation_status,
                subscription_status,
                subscription_expires_at
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
            )
        ");

        $stmt->execute([

            $clanCode,
            $clanName,
            $contactEmail !== '' ? $contactEmail : null,
            $clanPhone !== '' ? $clanPhone : null,
            $clanAddress !== '' ? $clanAddress : null,
            $clanMotto !== '' ? $clanMotto : null,
            $activationStatus,
            $subscriptionStatus,
            $subscriptionExpiresAt !== ''
                ? $subscriptionExpiresAt
                : null

        ]);

        $clanId = (int)$pdo->lastInsertId();

        if ($pdo->inTransaction()) {
            $pdo->commit();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Activity Log
    |--------------------------------------------------------------------------
    */

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
            'create_clan',
            'clan',
            ?,
            ?,
            ?,
            ?
        )
    ");

    $dbNote = $resolvedDatabaseName !== ''
        ? ' (dedicated database: ' . $resolvedDatabaseName . ')'
        : '';

    $stmt->execute([

        (int)$_SESSION['owner_id'],
        $clanId,
        'Created clan ' . $clanCode . ' (' . $clanName . ')' . $dbNote,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null

    ]);


    header(
        'Location: index.php?success=' .
        urlencode(
            'Clan created successfully.'
            . ($resolvedDatabaseName !== ''
                ? ' A dedicated database was created (' . $resolvedDatabaseName . ').'
                : '')
        )
    );

    exit;

} catch (Throwable $e) {

    if (isset($pdo) && $pdo->inTransaction()) {

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
