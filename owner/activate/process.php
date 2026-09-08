<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/maindb.php';
require_once __DIR__ . '/../config/constants.php';

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');


/*
|--------------------------------------------------------------------------
| ONLY POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    exit(
        'Method Not Allowed'
    );
}


/*
|--------------------------------------------------------------------------
| INPUT
|--------------------------------------------------------------------------
*/

$token =
    trim(
        (string)($_POST['token'] ?? '')
    );

$clientName =
    trim(
        (string)($_POST['client_name'] ?? '')
    );

$clientEmail =
    trim(
        (string)($_POST['client_email'] ?? '')
    );


if ($token === '') {

    exit(
        'Activation token is required.'
    );
}


/*
|--------------------------------------------------------------------------
| VALIDATE EMAIL
|--------------------------------------------------------------------------
*/

if (
    $clientEmail !== '' &&
    !filter_var(
        $clientEmail,
        FILTER_VALIDATE_EMAIL
    )
) {

    exit(
        'Invalid email address.'
    );
}


/*
|--------------------------------------------------------------------------
| DOMAIN
|--------------------------------------------------------------------------
*/

function getClientDomain(): string
{
    $host =
        $_SERVER['HTTP_HOST']
        ?? 'localhost';

    $host =
        strtolower(
            trim($host)
        );

    $host =
        preg_replace(
            '#:\d+$#',
            '',
            $host
        );

    return $host;
}


$domain =
    getClientDomain();


/*
|--------------------------------------------------------------------------
| INSTALLATION CODE
|--------------------------------------------------------------------------
|
| Generated locally if this installation has not been activated.
|
*/

$installationCode =
    'CLAN-' .
    strtoupper(
        bin2hex(
            random_bytes(8)
        )
    );


/*
|--------------------------------------------------------------------------
| OWNER API URL
|--------------------------------------------------------------------------
|
| When the Owner Panel is deployed online, set OWNER_ACTIVATION_API in
| owner/config/constants.php. The runtime default is derived from the
| current request so local installs reached via localhost, 127.0.0.1 or
| a LAN IP can always connect to the API.
|
*/

function resolveOwnerApiUrl(): string
{
    if (defined('OWNER_ACTIVATION_API')) {

        return OWNER_ACTIVATION_API;
    }

    $scheme =
        (
            !empty($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== 'off'
        )
            ? 'https'
            : 'http';

    $host =
        (string)($_SERVER['HTTP_HOST'] ?? '');

    if ($host === '') {

        $host =
            'localhost';
    }

    $clientPath =
        defined('CLIENT_APP_PATH')
            ? CLIENT_APP_PATH
            : '/Clan_Management_System';

    return $scheme . '://' . $host . $clientPath . '/owner/api/activation.php';
}

$ownerApiUrl =
    resolveOwnerApiUrl();


/*
|--------------------------------------------------------------------------
| HTTP API CALL
|--------------------------------------------------------------------------
*/

function callOwnerApi(
    string $url,
    array $payload
): array {

    $json =
        json_encode(
            $payload,
            JSON_UNESCAPED_SLASHES
        );


    /*
    |--------------------------------------------------------------------------
    | Prefer cURL; fall back to HTTP stream wrapper when cURL is not
    | available (e.g. minimal PHP builds).
    |--------------------------------------------------------------------------
    */

    if (
        function_exists('curl_init')
    ) {

        $ch =
            curl_init($url);


        curl_setopt_array(
            $ch,
            [
                CURLOPT_POST => true,

                CURLOPT_POSTFIELDS =>
                    $json,

                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json'
                ],

                CURLOPT_RETURNTRANSFER => true,

                CURLOPT_CONNECTTIMEOUT => 10,

                CURLOPT_TIMEOUT => 30,

                CURLOPT_SSL_VERIFYPEER => true,

                CURLOPT_SSL_VERIFYHOST => 2
            ]
        );


        $response =
            curl_exec($ch);


        if ($response === false) {

            $error =
                curl_error($ch);

            curl_close($ch);

            throw new RuntimeException(
                'Owner API connection failed: ' .
                $error
            );
        }


        $httpCode =
            (int)curl_getinfo(
                $ch,
                CURLINFO_HTTP_CODE
            );


        curl_close($ch);

    } else {

        $context =
            stream_context_create(
                [
                    'http' => [
                        'method'  => 'POST',
                        'header'  => implode("\r\n", [
                            'Content-Type: application/json',
                            'Accept: application/json'
                        ]),
                        'content' => $json,
                        'ignore_errors' => true,
                        'timeout'  => 30
                    ]
                ]
            );

        $response =
            @file_get_contents(
                $url,
                false,
                $context
            );


        $httpCode = 0;

        if (
            isset($http_response_header[0]) &&
            preg_match(
                '#\s(\d{3})\s#',
                $http_response_header[0],
                $m
            )
        ) {

            $httpCode =
                (int)$m[1];
        }


        if ($response === false) {

            throw new RuntimeException(
                'Owner API connection failed.'
            );
        }
    }


    $data =
        json_decode(
            $response,
            true
        );


    if (!is_array($data)) {

        throw new RuntimeException(
            'Invalid response from Owner API.'
        );
    }


    if (
        $httpCode < 200 ||
        $httpCode >= 300 ||
        empty($data['success'])
    ) {

        throw new RuntimeException(
            (string)(
                $data['message']
                ?? 'Owner API rejected the request.'
            )
        );
    }


    return $data;
}


/*
|--------------------------------------------------------------------------
| STEP 1 — VALIDATE TOKEN WITH OWNER
|--------------------------------------------------------------------------
*/

try {

    $validation =
        callOwnerApi(
            $ownerApiUrl,
            [
                'action' =>
                    'validate',

                'token' =>
                    $token,

                'domain_name' =>
                    $domain,

                'installation_code' =>
                    $installationCode,

                'client_name' =>
                    $clientName,

                'client_email' =>
                    $clientEmail
            ]
        );


    $ownerData =
        $validation['data']
        ?? [];


} catch (
    Throwable $e
) {

    http_response_code(400);

    exit(
        htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        )
    );
}


/*
|--------------------------------------------------------------------------
| INSTALLATION DATA
|--------------------------------------------------------------------------
*/

$ownerInstallationId =
    (int)(
        $ownerData['installation_id']
        ?? 0
    );

$installationCode =
    (string)(
        $ownerData['installation_code']
        ?? $installationCode
    );

$ownerClanId =
    (int)(
        $ownerData['clan_id']
        ?? 0
    );

$ownerDatabaseName =
    (string)(
        $ownerData['database_name']
        ?? ''
    );


if ($ownerInstallationId <= 0) {

    exit(
        'Invalid installation information returned by Owner API.'
    );
}


/*
|--------------------------------------------------------------------------
| CLIENT DATABASE
|--------------------------------------------------------------------------
*/

$database =
    new OwnerClanDatabase(
        $ownerDatabaseName !== '' ? $ownerDatabaseName : null
    );

$pdo =
    $database->connect();


/*
|--------------------------------------------------------------------------
| CLIENT TRANSACTION
|--------------------------------------------------------------------------
*/

try {

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | Check existing activation
    |--------------------------------------------------------------------------
    */

    $stmt =
        $pdo->prepare("
            SELECT
                activation_id,
                status,
                domain_name
            FROM installation_activations
            WHERE installation_id = ?
            LIMIT 1
            FOR UPDATE
        ");

    $stmt->execute([
        $installationCode
    ]);

    $existing =
        $stmt->fetch(PDO::FETCH_ASSOC);


    /*
    |--------------------------------------------------------------------------
    | Prevent local replay
    |--------------------------------------------------------------------------
    */

    if (
        $existing &&
        $existing['status'] === 'used'
    ) {

        $pdo->rollBack();

        exit(
            'This installation is already activated.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create / update client activation record
    |--------------------------------------------------------------------------
    */

    $tokenHash =
        password_hash(
            $token,
            PASSWORD_DEFAULT
        );


    if ($existing) {

        $stmt =
            $pdo->prepare("
                UPDATE installation_activations

                SET
                    token_hash = ?,
                    domain_name = ?,
                    clan_id = ?,
                    status = 'generated',
                    expires_at = ?,
                    used_at = NULL,
                    used_ip = NULL

                WHERE activation_id = ?
            ");

        $stmt->execute([
            $tokenHash,
            $domain,
            $ownerClanId > 0 ? $ownerClanId : null,
            $ownerData['expires_at']
                ?? date(
                    'Y-m-d H:i:s',
                    time() + 86400
                ),
            $existing['activation_id']
        ]);

    } else {

        /*
        |--------------------------------------------------------------------------
        | IMPORTANT
        |--------------------------------------------------------------------------
        | admin_user_id is required by the existing schema.
        |
        | We prefer the administrator owned by this clan (created by the
        | Owner during activation creation); fall back to any existing
        | administrator only if the clan has none yet.
        |--------------------------------------------------------------------------
        */

        $admin = null;

        if ($ownerClanId > 0) {

            $stmt =
                $pdo->prepare("
                    SELECT user_id
                    FROM users
                    WHERE role_id = 5
                      AND clan_id = ?
                      AND account_status = 'active'
                      AND is_deleted = 0
                    ORDER BY user_id ASC
                    LIMIT 1
                ");

            $stmt->execute([
                $ownerClanId
            ]);

            $admin =
                $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$admin) {

            $stmt =
                $pdo->query("
                    SELECT user_id
                    FROM users
                    WHERE role_id = 5
                      AND account_status = 'active'
                      AND is_deleted = 0
                    ORDER BY user_id ASC
                    LIMIT 1
                ");

            $admin =
                $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$admin) {

            throw new RuntimeException(
                'No active Administrator exists on this installation.'
            );
        }


        $stmt =
            $pdo->prepare("
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
                    'OWNER_ACTIVATION',
                    ?,
                    ?,
                    'generated',
                    ?
                )
            ");

        $stmt->execute([
            $installationCode,
            $ownerClanId > 0 ? $ownerClanId : null,
            $tokenHash,
            $domain,
            $admin['user_id'],
            $ownerData['expires_at']
                ?? date(
                    'Y-m-d H:i:s',
                    time() + 86400
                )
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Save installation metadata if table supports it
    |--------------------------------------------------------------------------
    |
    | We do not modify schema here.
    |
    */


    /*
    |--------------------------------------------------------------------------
    | COMMIT CLIENT TRANSACTION
    |--------------------------------------------------------------------------
    */

    $pdo->commit();


} catch (
    Throwable $e
) {

    if (
        $pdo->inTransaction()
    ) {

        $pdo->rollBack();
    }


    http_response_code(500);

    exit(
        'Client activation transaction failed: ' .
        htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        )
    );
}


/*
|--------------------------------------------------------------------------
| STEP 2 — FINALIZE WITH OWNER
|--------------------------------------------------------------------------
*/

try {

    $finalization =
        callOwnerApi(
            $ownerApiUrl,
            [
                'action' =>
                    'finalize',

                'token' =>
                    $token,

                'domain_name' =>
                    $domain,

                'installation_code' =>
                    $installationCode,

                'client_name' =>
                    $clientName,

                'client_email' =>
                    $clientEmail
            ]
        );


} catch (
    Throwable $e
) {

    /*
    |--------------------------------------------------------------------------
    | IMPORTANT
    |--------------------------------------------------------------------------
    |
    | Client DB has already committed.
    |
    | Do NOT delete the client activation here.
    |
    | The owner finalization can safely be retried with the same
    | token while the owner token remains in "generated" state.
    |
    */

    http_response_code(503);

    exit(
        'Local installation was prepared, but Owner activation could not ' .
        'be finalized. Please retry activation. Details: ' .
        htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        )
    );
}


/*
|--------------------------------------------------------------------------
| STEP 3 — MARK CLIENT ACTIVATION USED
|--------------------------------------------------------------------------
*/

try {

    $stmt =
        $pdo->prepare("
            UPDATE installation_activations

            SET
                status = 'used',
                used_at = NOW(),
                used_ip = ?

            WHERE installation_id = ?
              AND status = 'generated'
        ");

    $stmt->execute([
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $installationCode
    ]);

} catch (
    Throwable $e
) {

    /*
    |--------------------------------------------------------------------------
    | Owner is already active.
    |--------------------------------------------------------------------------
    |
    | This is not a reason to revoke the owner activation.
    | We report it so it can be repaired locally.
    |
    */

    http_response_code(500);

    exit(
        'Owner activation succeeded but local activation status could not ' .
        'be finalized.'
    );
}


/*
|--------------------------------------------------------------------------
| STEP 4 — SET CLAN ACTIVE
|--------------------------------------------------------------------------
*/

if ($ownerClanId > 0) {

    try {

        $stmt =
            $pdo->prepare("
                UPDATE clans

                SET
                    activation_status = 'active',
                    subscription_status = 'active',
                    activated_at = NOW()

                WHERE clan_id = ?
            ");

        $stmt->execute([
            $ownerClanId
        ]);

    } catch (
        Throwable $e
    ) {

        error_log(
            'Clan activation status update failed: ' .
            $e->getMessage()
        );
    }
}


/*
|--------------------------------------------------------------------------
| SUCCESS
|--------------------------------------------------------------------------
*/

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Activation Successful
    </title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
        }

        .card {
            background: white;
            padding: 40px;
            max-width: 550px;
            border-radius: 14px;
            box-shadow: 0 10px 35px rgba(0,0,0,.08);
            text-align: center;
        }

        .success {
            font-size: 50px;
        }

        h1 {
            color: #16803c;
        }

        .code {
            background: #f1f3f5;
            padding: 12px;
            border-radius: 8px;
            font-family: monospace;
            margin-top: 15px;
        }

        a {
            display: inline-block;
            margin-top: 25px;
            text-decoration: none;
            background: #1769aa;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
        }

    </style>

</head>

<body>

<div class="card">

    <div class="success">
        ✓
    </div>

    <h1>
        Activation Successful
    </h1>

    <p>
        Your Clan Management System installation
        has been successfully activated.
    </p>

    <p>
        Installation Code:
    </p>

    <div class="code">
        <?= htmlspecialchars(
            $installationCode,
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    </div>

    <p>
        Domain:
        <strong>
            <?= htmlspecialchars(
                $domain,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </strong>
    </p>

    <a href="../login.php">
        Continue to Login
    </a>

</div>

</body>

</html>