<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

/*
|--------------------------------------------------------------------------
| API HELPERS
|--------------------------------------------------------------------------
*/

function apiResponse(
    bool $success,
    string $message,
    array $data = [],
    int $status = 200
): never {

    http_response_code($status);

    echo json_encode(
        [
            'success' => $success,
            'message' => $message,
            'data'    => $data
        ],
        JSON_UNESCAPED_SLASHES
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| ONLY POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    apiResponse(
        false,
        'Method Not Allowed.',
        [],
        405
    );
}


/*
|--------------------------------------------------------------------------
| READ JSON
|--------------------------------------------------------------------------
*/

$raw = file_get_contents('php://input');

$input = json_decode(
    $raw,
    true
);

if (!is_array($input)) {

    apiResponse(
        false,
        'Invalid JSON request.',
        [],
        400
    );
}


/*
|--------------------------------------------------------------------------
| ACTION
|--------------------------------------------------------------------------
*/

$action =
    trim(
        (string)($input['action'] ?? '')
    );


/*
|--------------------------------------------------------------------------
| TOKEN
|--------------------------------------------------------------------------
*/

$token =
    trim(
        (string)($input['token'] ?? '')
    );


if ($token === '') {

    apiResponse(
        false,
        'Activation token is required.',
        [],
        400
    );
}


/*
|--------------------------------------------------------------------------
| OWNER DATABASE
|--------------------------------------------------------------------------
*/

$database =
    new OwnerDatabase();

$pdo =
    $database->connect();


/*
|--------------------------------------------------------------------------
| CLIENT INFORMATION
|--------------------------------------------------------------------------
*/

$domain =
    trim(
        (string)($input['domain_name'] ?? '')
    );

$installationCode =
    trim(
        (string)($input['installation_code'] ?? '')
    );

$clientName =
    trim(
        (string)($input['client_name'] ?? '')
    );

$clientEmail =
    trim(
        (string)($input['client_email'] ?? '')
    );


/*
|--------------------------------------------------------------------------
| IP
|--------------------------------------------------------------------------
*/

$clientIp =
    $_SERVER['REMOTE_ADDR'] ?? 'unknown';


/*
|--------------------------------------------------------------------------
| USER AGENT
|--------------------------------------------------------------------------
*/

$userAgent =
    $_SERVER['HTTP_USER_AGENT'] ?? '';


/*
|--------------------------------------------------------------------------
| FIND ACTIVATION
|--------------------------------------------------------------------------
|
| We retrieve candidate activations and verify the plaintext token
| against token_hash using password_verify().
|
*/

$stmt = $pdo->prepare("
    SELECT
        oa.activation_id,
        oa.installation_id,
        oa.clan_id,
        oa.token_hash,
        oa.token_label,
        oa.domain_name,
        oa.status,
        oa.expires_at,

        oi.installation_code,
        oi.client_name,
        oi.client_email,
        oi.domain_name AS installation_domain,
        oi.database_name,
        oi.installation_status,
        oi.installed_at,
        oi.activated_at

    FROM owner_activations oa

    INNER JOIN owner_installations oi
        ON oi.installation_id = oa.installation_id

    WHERE oa.status = 'generated'

    ORDER BY oa.activation_id DESC
");

$stmt->execute();

$activation = null;


/*
|--------------------------------------------------------------------------
| VERIFY TOKEN
|--------------------------------------------------------------------------
*/

while (
    $row = $stmt->fetch(PDO::FETCH_ASSOC)
) {

    if (
        password_verify(
            $token,
            $row['token_hash']
        )
    ) {

        $activation = $row;

        break;
    }
}


if (!$activation) {

    apiResponse(
        false,
        'Invalid or already used activation token.',
        [],
        401
    );
}


/*
|--------------------------------------------------------------------------
| EXPIRATION
|--------------------------------------------------------------------------
*/

if (
    !empty($activation['expires_at']) &&
    strtotime($activation['expires_at']) < time()
) {

    $update = $pdo->prepare("
        UPDATE owner_activations
        SET status = 'expired'
        WHERE activation_id = ?
          AND status = 'generated'
    ");

    $update->execute([
        $activation['activation_id']
    ]);

    apiResponse(
        false,
        'Activation token has expired.',
        [],
        410
    );
}


/*
|--------------------------------------------------------------------------
| DOMAIN NORMALIZATION
|--------------------------------------------------------------------------
*/

function normalizeDomain(
    string $domain
): string {

    $domain =
        strtolower(
            trim($domain)
        );

    $domain =
        preg_replace(
            '#^https?://#',
            '',
            $domain
        );

    $domain =
        preg_replace(
            '#/.*$#',
            '',
            $domain
        );

    $domain =
        preg_replace(
            '#:\d+$#',
            '',
            $domain
        );

    /*
    |--------------------------------------------------------------------------
    | Local loopback aliases are treated as the same host
    |--------------------------------------------------------------------------
    |
    | In local development the same machine is reachable as "localhost"
    | AND "127.0.0.1" (and sometimes "::1"). A client must not be blocked
    | from activating just because they opened the URL via the other alias.
    |--------------------------------------------------------------------------
    */

    if (
        $domain === 'localhost' ||
        $domain === '127.0.0.1' ||
        $domain === '::1'
    ) {
        return 'localhost';
    }

    return trim($domain);
}


/*
|--------------------------------------------------------------------------
| DOMAIN MATCH
|--------------------------------------------------------------------------
*/

function domainsMatch(
    string $a,
    string $b
): bool {

    $a = normalizeDomain($a);
    $b = normalizeDomain($b);

    if ($a === '' || $b === '') {
        return true;
    }

    return $a === $b;
}


if ($domain !== '') {

    $domain =
        normalizeDomain($domain);
}


/*
|--------------------------------------------------------------------------
| VALIDATE ACTION
|--------------------------------------------------------------------------
*/

if ($action === 'validate') {

    /*
    |--------------------------------------------------------------------------
    | DOMAIN BINDING
    |--------------------------------------------------------------------------
    */

    $boundDomain =
        normalizeDomain(
            (string)(
                $activation['domain_name']
                ?: $activation['installation_domain']
            )
        );


    /*
    |--------------------------------------------------------------------------
    | If owner already specified a domain,
    | client must match it.
    |--------------------------------------------------------------------------
    */

    if (!domainsMatch($boundDomain, $domain)) {

        apiResponse(
            false,
            'This activation token is bound to another domain.',
            [],
            409
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

    apiResponse(
        true,
        'Activation token is valid.',
        [
            'activation_id' =>
                (int)$activation['activation_id'],

            'installation_id' =>
                (int)$activation['installation_id'],

            'clan_id' =>
                !empty($activation['clan_id'])
                    ? (int)$activation['clan_id']
                    : null,

            'installation_code' =>
                $activation['installation_code'],

            'client_name' =>
                $activation['client_name'],

            'client_email' =>
                $activation['client_email'],

            'database_name' =>
                !empty($activation['database_name'])
                    ? $activation['database_name']
                    : null,

            'domain_name' =>
                $boundDomain,

            'expires_at' =>
                $activation['expires_at']
        ]
    );
}


/*
|--------------------------------------------------------------------------
| FINALIZE ACTIVATION
|--------------------------------------------------------------------------
*/

if ($action === 'finalize') {

    if ($domain === '') {

        apiResponse(
            false,
            'Domain is required.',
            [],
            400
        );
    }


    if ($installationCode === '') {

        apiResponse(
            false,
            'Installation code is required.',
            [],
            400
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Domain must match owner binding when already defined.
    |--------------------------------------------------------------------------
    */

    $boundDomain =
        normalizeDomain(
            (string)(
                $activation['domain_name']
                ?: $activation['installation_domain']
            )
        );


    if (!domainsMatch($boundDomain, $domain)) {

        apiResponse(
            false,
            'Domain binding mismatch.',
            [],
            409
        );
    }


    /*
    |--------------------------------------------------------------------------
    | TRANSACTION
    |--------------------------------------------------------------------------
    */

    try {

        $pdo->beginTransaction();


        /*
        |--------------------------------------------------------------------------
        | Lock activation row
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            SELECT
                activation_id,
                installation_id,
                clan_id,
                token_hash,
                status,
                expires_at
            FROM owner_activations
            WHERE activation_id = ?
            FOR UPDATE
        ");

        $stmt->execute([
            $activation['activation_id']
        ]);

        $locked =
            $stmt->fetch(PDO::FETCH_ASSOC);


        /*
        |--------------------------------------------------------------------------
        | Replay protection
        |--------------------------------------------------------------------------
        */

        if (
            !$locked ||
            $locked['status'] !== 'generated'
        ) {

            $pdo->rollBack();

            apiResponse(
                false,
                'Activation token has already been used or revoked.',
                [],
                409
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Check expiry again inside transaction
        |--------------------------------------------------------------------------
        */

        if (
            !empty($locked['expires_at']) &&
            strtotime($locked['expires_at']) < time()
        ) {

            $expire = $pdo->prepare("
                UPDATE owner_activations
                SET status = 'expired'
                WHERE activation_id = ?
            ");

            $expire->execute([
                $locked['activation_id']
            ]);

            $pdo->commit();

            apiResponse(
                false,
                'Activation token has expired.',
                [],
                410
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Verify token again
        |--------------------------------------------------------------------------
        */

        if (
            !password_verify(
                $token,
                $locked['token_hash']
            )
        ) {

            $pdo->rollBack();

            apiResponse(
                false,
                'Invalid activation token.',
                [],
                401
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Update installation
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            UPDATE owner_installations

            SET
                domain_name = ?,
                client_name = COALESCE(NULLIF(?, ''), client_name),
                client_email = COALESCE(NULLIF(?, ''), client_email),
                installation_status = 'active',
                activated_at = NOW(),
                updated_at = NOW()

            WHERE installation_id = ?
        ");

        $stmt->execute([
            $domain,
            $clientName,
            $clientEmail,
            $locked['installation_id']
        ]);


        /*
        |--------------------------------------------------------------------------
        | Mark token USED
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            UPDATE owner_activations

            SET
                status = 'used',
                domain_name = ?,
                used_at = NOW(),
                used_ip = ?

            WHERE activation_id = ?
              AND status = 'generated'
        ");

        $stmt->execute([
            $domain,
            $clientIp,
            $locked['activation_id']
        ]);


        if ($stmt->rowCount() !== 1) {

            throw new RuntimeException(
                'Activation token could not be finalized.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | ACTIVITY LOG
        |--------------------------------------------------------------------------
        */

        $ownerId =
            !empty($_SESSION['owner_id'])
                ? (int)$_SESSION['owner_id']
                : null;


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
                'ACTIVATION_USED',
                'owner_activation',
                ?,
                ?,
                ?,
                ?
            )
        ");

        $stmt->execute([
            $ownerId,
            $locked['activation_id'],
            'Activation completed for installation ID ' .
            $locked['installation_id'] .
            ' on domain ' .
            $domain,
            $clientIp,
            $userAgent
        ]);


        /*
        |--------------------------------------------------------------------------
        | COMMIT
        |--------------------------------------------------------------------------
        */

        $pdo->commit();


        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        apiResponse(
            true,
            'Activation completed successfully.',
            [
                'activation_id' =>
                    (int)$locked['activation_id'],

                'installation_id' =>
                    (int)$locked['installation_id'],

                'clan_id' =>
                    !empty($locked['clan_id'])
                        ? (int)$locked['clan_id']
                        : null,

                'domain_name' =>
                    $domain,

                'status' =>
                    'active',

                'activated_at' =>
                    date('Y-m-d H:i:s')
            ]
        );


    } catch (
        Throwable $e
    ) {

        if (
            $pdo->inTransaction()
        ) {

            $pdo->rollBack();
        }


        error_log(
            'Activation API error: ' .
            $e->getMessage()
        );


        apiResponse(
            false,
            'Activation could not be completed.',
            [],
            500
        );
    }
}


/*
|--------------------------------------------------------------------------
| UNKNOWN ACTION
|--------------------------------------------------------------------------
*/

apiResponse(
    false,
    'Unknown activation action.',
    [],
    400
);