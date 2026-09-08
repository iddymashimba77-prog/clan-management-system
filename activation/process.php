<?php

declare(strict_types=1);

session_start();

require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/installation.php';
require_once '../config/activation.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header(
        'Location: index.php'
    );

    exit;
}


$token =
    trim($_POST['token'] ?? '');


if ($token === '') {

    $_SESSION['activation_error'] =
        'Activation token is missing.';

    header(
        'Location: index.php'
    );

    exit;
}


try {

    $database =
        new Database();

    $pdo =
        $database->connect();


    /*
     * Validate and consume token.
     */

    $adminUserId =
        consumeActivationToken(
            $pdo,
            $token
        );


    /*
     * Verify administrator account.
     */

    $stmt = $pdo->prepare("
        SELECT
            u.user_id,
            u.username,
            u.email,
            u.first_name,
            u.last_name,
            u.role_id,
            r.role_name
        FROM users u

        INNER JOIN roles r
            ON r.role_id = u.role_id

        WHERE
            u.user_id = ?

            AND u.role_id = 5

            AND u.is_deleted = 0

        LIMIT 1
    ");


    $stmt->execute([
        $adminUserId
    ]);


    $admin =
        $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$admin) {

        throw new RuntimeException(
            'Administrator account could not be verified.'
        );
    }


    /*
     * Activate account.
     */

    $stmt = $pdo->prepare("
        UPDATE users

        SET account_status = 'active'

        WHERE user_id = ?
    ");


    $stmt->execute([
        $adminUserId
    ]);


    /*
     * Log activation.
     */

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

        $adminUserId,

        'SYSTEM_ACTIVATION',

        'installation_activations',

        $adminUserId,

        'Production installation activated successfully.',

        $_SERVER['REMOTE_ADDR'] ?? null

    ]);


    /*
     * Store activation session.
     */

    $_SESSION['system_activated'] =
        true;

    $_SESSION['activated_admin_id'] =
        $adminUserId;

    $_SESSION['activated_username'] =
        $admin['username'];


    header(
        'Location: success.php'
    );

    exit;


} catch (Throwable $e) {

    $_SESSION['activation_error'] =
        $e->getMessage();

    header(
        'Location: index.php'
    );

    exit;
}