<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

requireOwnerLogin();
requireOwnerCsrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    exit('Method Not Allowed');
}

$activationId =
    (int)($_POST['activation_id'] ?? 0);

if ($activationId <= 0) {

    exit('Invalid activation.');
}

$database = new OwnerDatabase();

$pdo = $database->connect();

try {

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        SELECT
            activation_id,
            installation_id,
            status
        FROM owner_activations
        WHERE activation_id = ?
        FOR UPDATE
    ");

    $stmt->execute([
        $activationId
    ]);

    $activation =
        $stmt->fetch();

    if (!$activation) {

        throw new RuntimeException(
            'Activation not found.'
        );
    }

    if ($activation['status'] !== 'generated') {

        throw new RuntimeException(
            'Only generated activations can be revoked.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Revoke Activation
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE owner_activations
        SET status = 'revoked'
        WHERE activation_id = ?
    ");

    $stmt->execute([
        $activationId
    ]);

    /*
    |--------------------------------------------------------------------------
    | Log
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
            'REVOKE_ACTIVATION',
            'activation',
            ?,
            ?,
            ?,
            ?
        )
    ");

    $stmt->execute([
        (int)$_SESSION['owner_id'],
        $activationId,
        'Revoked activation #' . $activationId,
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
    'Location: view.php?id=' .
    $activationId .
    '&revoked=1'
);

exit;