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

$clientName =
    trim($_POST['client_name'] ?? '');

$clientEmail =
    trim($_POST['client_email'] ?? '');

$domainName =
    trim($_POST['domain_name'] ?? '');

$databaseName =
    trim($_POST['database_name'] ?? '');

$tokenLabel =
    trim($_POST['token_label'] ?? '');

$validityDays =
    (int)($_POST['validity_days'] ?? 365);

$adminUsername =
    trim($_POST['admin_username'] ?? '');

$adminEmail =
    trim($_POST['admin_email'] ?? '');

$adminPassword =
    (string)($_POST['admin_password'] ?? '');

$adminPasswordConfirm =
    (string)($_POST['admin_password_confirm'] ?? '');


/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

if ($clientName === '') {

    exit('Client name is required.');
}

if ($clientEmail !== '' &&
    !filter_var($clientEmail, FILTER_VALIDATE_EMAIL)
) {

    exit('Invalid client email.');
}

$allowedValidity = [
    1,
    7,
    30,
    90,
    365
];

if (!in_array($validityDays, $allowedValidity, true)) {

    $validityDays = 365;
}

if ($adminUsername === '') {

    exit('Clan administrator username is required.');
}

if (strlen($adminUsername) > 100) {

    exit('Clan administrator username is too long.');
}

if (
    $adminEmail !== '' &&
    !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)
) {

    exit('Invalid clan administrator email.');
}

if (strlen($adminPassword) < 8) {

    exit('Clan administrator password must be at least 8 characters.');
}

if ($adminPassword !== $adminPasswordConfirm) {

    exit('Clan administrator passwords do not match.');
}


/*
|--------------------------------------------------------------------------
| Resolve dedicated clan database name
|--------------------------------------------------------------------------
|
| Database-per-clan: the requested database_name (user-supplied) is
| sanitized and used verbatim. If none was provided, the installation
| will default to clan_production_test (legacy shared DB) unless the
| caller supplies one.
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


/*
|--------------------------------------------------------------------------
| Database
|--------------------------------------------------------------------------
*/

$database = new OwnerDatabase();

$pdo = $database->connect();


try {

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | Check duplicate domain
    |--------------------------------------------------------------------------
    */

    if ($domainName !== '') {

        $stmt = $pdo->prepare("
            SELECT installation_id
            FROM owner_installations
            WHERE domain_name = ?
            LIMIT 1
        ");

        $stmt->execute([
            $domainName
        ]);

        if ($stmt->fetch()) {

            throw new RuntimeException(
                'An installation with this domain already exists.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Installation Code
    |--------------------------------------------------------------------------
    */

    do {

        $installationCode =
            'CMS-' .
            strtoupper(
                bin2hex(
                    random_bytes(6)
                )
            );

        $stmt = $pdo->prepare("
            SELECT installation_id
            FROM owner_installations
            WHERE installation_code = ?
            LIMIT 1
        ");

        $stmt->execute([
            $installationCode
        ]);

    } while ($stmt->fetch());


    /*
    |--------------------------------------------------------------------------
    | Create Clan (Tenant) Record + Dedicated Database
    |--------------------------------------------------------------------------
    |
    | Database-per-clan: when the Owner provides a custom database_name, the
    | clan gets its OWN dedicated MySQL database created automatically with
    | exactly the same structure as the original (base schema + migrations +
    | seed). The clan administrator becomes the first member of that clan.
    |
    | If no custom database_name is supplied, the installation falls back to
    | the legacy shared database (clan_production_test).
    |
    */

    $clanName = $clientName !== '' ? $clientName : $installationCode;

    $adminNameParts = preg_split(
        '/\s+/',
        trim($clientName)
    );

    $adminFirstName = ucwords(strtolower($adminNameParts[0]
        ?? $adminUsername));

    $adminLastName = isset($adminNameParts[1])
        ? ucwords(strtolower(implode(' ', array_slice($adminNameParts, 1))))
        : $adminUsername;

    $adminUserId = null;

    if ($resolvedDatabaseName !== '') {

        $stmt = $pdo->query("SELECT COALESCE(MAX(clan_id), 0) FROM owner_installations");
        $clanId = (int)$stmt->fetchColumn() + 1;

        $clanCode = 'CLN-' . str_pad((string)$clanId, 5, '0', STR_PAD_LEFT);

        createClanDatabase([
            'db_name'            => $resolvedDatabaseName,
            'clan_id'            => $clanId,
            'clan_name'          => $clanName,
            'clan_code'          => $clanCode,
            'activation_status'  => 'pending',
            'subscription_status'=> 'trial',
            'admin_name'         => $clientName !== '' ? $clientName : $adminUsername,
            'admin_username'     => $adminUsername,
            'admin_password'     => $adminPassword,
            'admin_email'        => $adminEmail,
        ]);

        $clanPdo = clanDbConnection('localhost', 'root', '', $resolvedDatabaseName);

        $stmt = $clanPdo->prepare("
            SELECT user_id
            FROM users
            WHERE username = ?
            LIMIT 1
        ");

        $stmt->execute([$adminUsername]);

        $adminUserId = (int)$stmt->fetchColumn();

        $databaseNameForInstallation = $resolvedDatabaseName;

    } else {

        $clanDatabase = new OwnerClanDatabase();

        $clanPdo = $clanDatabase->connect();

        $stmt = $clanPdo->query("SELECT MAX(clan_id) FROM clans");
        $nextId = (int)$stmt->fetchColumn() + 1;

        $clanId = $nextId;
        $clanCode = 'CLN-' . str_pad((string)$nextId, 5, '0', STR_PAD_LEFT);

        $stmt = $clanPdo->prepare("
            INSERT INTO clans
            (
                clan_code,
                clan_name,
                contact_email,
                activation_status,
                subscription_status
            )
            VALUES
            (
                ?,
                ?,
                ?,
                'pending',
                'trial'
            )
        ");

        $stmt->execute([
            $clanCode,
            $clanName,
            $clientEmail !== '' ? $clientEmail : null
        ]);

        $clanId = (int)$clanPdo->lastInsertId();

        $adminMemberCode = 'CLAN-' . str_pad((string)$clanId, 7, '0', STR_PAD_LEFT);

        $stmt = $clanPdo->prepare("
            INSERT INTO users
            (
                clan_id,
                member_code,
                first_name,
                last_name,
                gender,
                email,
                username,
                password_hash,
                role_id,
                account_status,
                family_member_type,
                is_deceased
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                'Male',
                ?,
                ?,
                ?,
                5,
                'active',
                'blood',
                0
            )
        ");

        $stmt->execute([
            $clanId,
            $adminMemberCode,
            $adminFirstName,
            $adminLastName,
            $adminEmail !== '' ? $adminEmail : null,
            $adminUsername,
            password_hash($adminPassword, PASSWORD_DEFAULT)
        ]);

        $adminUserId = (int)$clanPdo->lastInsertId();

        $stmt = $clanPdo->prepare("
            INSERT INTO user_roles
            (
                clan_id,
                user_id,
                role_id
            )
            VALUES
            (
                ?,
                ?,
                5
            )
        ");

        $stmt->execute([
            $clanId,
            $adminUserId
        ]);

        $databaseNameForInstallation = '';
    }


    /*
    |--------------------------------------------------------------------------
    | Create Installation
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO owner_installations
        (
            installation_code,
            clan_id,
            client_name,
            client_email,
            domain_name,
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
            ?,
            'pending'
        )
    ");

    $stmt->execute([

        $installationCode,

        $clanId,

        $clientName,

        $clientEmail !== ''
            ? $clientEmail
            : null,

        $domainName !== ''
            ? $domainName
            : null,

        $databaseNameForInstallation !== ''
            ? $databaseNameForInstallation
            : null

    ]);


    $installationId =
        (int)$pdo->lastInsertId();


    /*
    |--------------------------------------------------------------------------
    | Activity Log
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
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
            'create_installation',
            'installation',
            ?,
            ?,
            ?,
            ?
        )
    ");

    $description =
        'Created installation ' .
        $installationCode .
        ' for client ' .
        $clientName .
        '. Activation validity: ' .
        $validityDays .
        ' days.';

    $stmt->execute([

        (int)$_SESSION['owner_id'],

        $installationId,

        $description,

        $_SERVER['REMOTE_ADDR']
            ?? null,

        $_SERVER['HTTP_USER_AGENT']
            ?? null

    ]);


    $pdo->commit();


    /*
    |--------------------------------------------------------------------------
    | Continue to Token Generation
    |--------------------------------------------------------------------------
    */

    header(
        'Location: generate.php?id=' .
        $installationId .
        '&days=' .
        $validityDays .
        '&label=' .
        urlencode($tokenLabel)
    );

    exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {

        $pdo->rollBack();
    }

    http_response_code(500);

    exit(
        'Unable to create activation: ' .
        htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        )
    );
}