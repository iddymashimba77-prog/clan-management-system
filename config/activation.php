<?php

declare(strict_types=1);

require_once __DIR__ . '/installation.php';


/*
|--------------------------------------------------------------------------
| Generate Activation Token
|--------------------------------------------------------------------------
*/

function generateActivationToken(
    PDO $pdo,
    int $adminUserId,
    ?string $domain = null
): array {

    /*
     * Check if installation already has activation.
     */

    $existingStmt = $pdo->query("
        SELECT *
        FROM installation_activations
        LIMIT 1
    ");

    $existing = $existingStmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {

        throw new RuntimeException(
            'This installation already has an activation record.'
        );
    }


    /*
     * Generate secure random token.
     */

    $plainToken = bin2hex(
        random_bytes(32)
    );


    $tokenHash = hash(
        'sha256',
        $plainToken
    );


    $installationId =
        getInstallationId();


    /*
     * Resolve the owning clan for the administrator.
     *
     * The token is bound to a tenant so that activation records
     * remain clan-scoped in a multi-tenant deployment.
     */

    $clanId = null;

    $stmt = $pdo->prepare("
        SELECT clan_id
        FROM users
        WHERE user_id = ?
        LIMIT 1
    ");

    $stmt->execute([
        $adminUserId
    ]);

    $clanId = (int)$stmt->fetchColumn();

    if ($clanId <= 0) {
        $clanId = null;
    }


    $domain =
        $domain !== null
            ? strtolower(trim($domain))
            : getCurrentDomain();


    /*
     * Token expires after 24 hours.
     */

    $expiresAt =
        date(
            'Y-m-d H:i:s',
            time() + (24 * 60 * 60)
        );


    $stmt = $pdo->prepare("
        INSERT INTO installation_activations
        (
            installation_id,
            clan_id,
            token_hash,
            token_label,
            domain_name,
            admin_user_id,
            status,
            expires_at
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            'generated',
            ?
        )
    ");


    $stmt->execute([
        $installationId,
        $clanId,
        $tokenHash,
        'Initial Production Activation',
        $domain,
        $adminUserId,
        $expiresAt
    ]);


    $activationId =
        (int) $pdo->lastInsertId();


    return [

        'activation_id' =>
            $activationId,

        'installation_id' =>
            $installationId,

        'token' =>
            $plainToken,

        'domain' =>
            $domain,

        'expires_at' =>
            $expiresAt

    ];
}


/*
|--------------------------------------------------------------------------
| Build Activation URL
|--------------------------------------------------------------------------
*/

function buildActivationUrl(
    string $token
): string {

    $scheme =
        (!empty($_SERVER['HTTPS']) &&
         $_SERVER['HTTPS'] !== 'off')
            ? 'https'
            : 'http';


    $host =
        $_SERVER['HTTP_HOST']
        ?? 'localhost';


    $basePath = '';

    /*
     * For your current development environment.
     *
     * When deployed to a real domain, this becomes:
     *
     * https://yourdomain.com/activation/index.php
     */

    if (
        strpos(
            $_SERVER['SCRIPT_NAME'] ?? '',
            '/Clan_Management_System/'
        ) !== false
    ) {

        $basePath =
            '/Clan_Management_System';
    }


    return $scheme .
        '://' .
        $host .
        $basePath .
        '/activation/index.php?token=' .
        urlencode($token);
}


/*
|--------------------------------------------------------------------------
| Find Activation
|--------------------------------------------------------------------------
*/

function findActivation(
    PDO $pdo,
    string $token
): ?array {

    if ($token === '') {
        return null;
    }


    $tokenHash =
        hash(
            'sha256',
            $token
        );


    $stmt = $pdo->prepare("
        SELECT
            ia.*,
            u.username,
            u.email,
            u.first_name,
            u.last_name,
            u.account_status
        FROM installation_activations ia

        INNER JOIN users u
            ON u.user_id = ia.admin_user_id

        WHERE ia.token_hash = ?

        LIMIT 1
    ");


    $stmt->execute([
        $tokenHash
    ]);


    $activation =
        $stmt->fetch(PDO::FETCH_ASSOC);


    return $activation ?: null;
}


/*
|--------------------------------------------------------------------------
| Validate Activation Token
|--------------------------------------------------------------------------
*/

function validateActivationToken(
    PDO $pdo,
    string $token
): array {

    $activation =
        findActivation(
            $pdo,
            $token
        );


    if (!$activation) {

        return [
            'valid' => false,
            'message' => 'Invalid activation token.'
        ];
    }


    /*
     * One-time protection.
     */

    if ($activation['status'] !== 'generated') {

        return [
            'valid' => false,
            'message' =>
                'This activation link has already been used or locked.'
        ];
    }


    /*
     * Expiry protection.
     */

    if (
        !empty($activation['expires_at']) &&
        strtotime($activation['expires_at']) < time()
    ) {

        $stmt = $pdo->prepare("
            UPDATE installation_activations
            SET status = 'expired'
            WHERE activation_id = ?
        ");

        $stmt->execute([
            $activation['activation_id']
        ]);


        return [
            'valid' => false,
            'message' => 'This activation link has expired.'
        ];
    }


    /*
     * Installation binding.
     */

    if (
        $activation['installation_id']
        !== getInstallationId()
    ) {

        return [
            'valid' => false,
            'message' =>
                'This activation token does not belong to this installation.'
        ];
    }


    /*
     * Domain binding.
     */

    $currentDomain =
        getCurrentDomain();


    if (
        !empty($activation['domain_name']) &&
        $activation['domain_name'] !== 'localhost' &&
        $activation['domain_name'] !== $currentDomain
    ) {

        return [
            'valid' => false,
            'message' =>
                'This activation token is bound to another domain.'
        ];
    }


    return [
        'valid' => true,
        'message' => 'Activation token is valid.',
        'activation' => $activation
    ];
}


/*
|--------------------------------------------------------------------------
| Consume Activation Token
|--------------------------------------------------------------------------
*/

function consumeActivationToken(
    PDO $pdo,
    string $token
): int {

    $result =
        validateActivationToken(
            $pdo,
            $token
        );


    if (!$result['valid']) {

        throw new RuntimeException(
            $result['message']
        );
    }


    $activation =
        $result['activation'];


    $stmt = $pdo->prepare("
        UPDATE installation_activations

        SET
            status = 'used',
            used_at = NOW(),
            used_ip = ?

        WHERE activation_id = ?

        AND status = 'generated'
    ");


    $stmt->execute([

        $_SERVER['REMOTE_ADDR']
            ?? null,

        $activation['activation_id']

    ]);


    if ($stmt->rowCount() !== 1) {

        throw new RuntimeException(
            'Activation token could not be consumed.'
        );
    }


    return
        (int) $activation['admin_user_id'];
}


/*
|--------------------------------------------------------------------------
| Lock Installation
|--------------------------------------------------------------------------
*/

function lockInstallation(
    PDO $pdo
): void {

    $stmt = $pdo->query("
        SELECT activation_id
        FROM installation_activations
        LIMIT 1
    ");

    $activation =
        $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$activation) {
        return;
    }


    $stmt = $pdo->prepare("
        UPDATE installation_activations

        SET status = 'locked'

        WHERE activation_id = ?
    ");


    $stmt->execute([
        $activation['activation_id']
    ]);
}