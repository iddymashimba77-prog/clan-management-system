<?php

declare(strict_types=1);

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';
require_once '../../config/installation.php';
require_once '../../config/activation.php';

requireAdministrator();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

try {

    $database = new Database();
    $pdo = $database->connect();

    $clanId = getCurrentClanId();

    $adminUserId = (int) $_SESSION['user_id'];

    /*
    |--------------------------------------------------------------------------
    | Check existing activation
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            activation_id,
            status,
            expires_at,
            used_at
        FROM installation_activations
        WHERE admin_user_id = ?
          AND clan_id = ?
        ORDER BY activation_id DESC
        LIMIT 1
    ");

    $stmt->execute([$adminUserId, $clanId]);

    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
        $existing &&
        $existing['status'] === 'generated' &&
        empty($existing['used_at']) &&
        strtotime($existing['expires_at']) > time()
    ) {

        exit(
            'This installation already has an active activation token.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Installation information
    |--------------------------------------------------------------------------
    */

    $installationId = getInstallationId();

    $domainName = getCurrentDomain();


    /*
    |--------------------------------------------------------------------------
    | Generate token
    |--------------------------------------------------------------------------
    */

    $plainToken =
        'CMS-ACT-' .
        strtoupper(
            bin2hex(
                random_bytes(32)
            )
        );


    /*
    |--------------------------------------------------------------------------
    | Hash token
    |--------------------------------------------------------------------------
    */

    $tokenHash = password_hash(
        $plainToken,
        PASSWORD_DEFAULT
    );


    /*
    |--------------------------------------------------------------------------
    | Expiration
    |--------------------------------------------------------------------------
    */

    $expiresAt = date(
        'Y-m-d H:i:s',
        time() + (24 * 60 * 60)
    );


    /*
    |--------------------------------------------------------------------------
    | Save activation
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO installation_activations
        (
            installation_id,
            domain_name,
            admin_user_id,
            token_hash,
            status,
            expires_at,
            clan_id
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            'generated',
            ?,
            ?
        )
    ");

    $stmt->execute([
        $installationId,
        $domainName,
        $adminUserId,
        $tokenHash,
        $expiresAt,
        $clanId
    ]);


    /*
    |--------------------------------------------------------------------------
    | Activation URL
    |--------------------------------------------------------------------------
    */

    $activationUrl =
        rtrim(BASE_URL, '/') .
        '/activation/index.php?token=' .
        urlencode($plainToken);


} catch (Throwable $e) {

    http_response_code(500);

    exit(
        'Unable to generate activation token: ' .
        htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        )
    );
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Activation Token</title>

<style>

body {
    font-family: Arial, sans-serif;
    background: #f4f6f9;
    padding: 40px;
}

.container {
    max-width: 700px;
    margin: auto;
    background: #ffffff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 5px 25px rgba(0,0,0,.08);
}

input {
    width: 100%;
    box-sizing: border-box;
    padding: 12px;
    margin-top: 10px;
}

.success {
    background: #d4edda;
    padding: 15px;
    border-radius: 6px;
}

.warning {
    background: #fff3cd;
    padding: 15px;
    border-radius: 6px;
    margin-top: 20px;
}

</style>

</head>

<body>

<div class="container">

<h2>Activation Token Generated</h2>

<div class="success">

<strong>Activation token generated successfully.</strong>

</div>

<p>
The activation link expires on:
<strong>
<?= htmlspecialchars(
    $expiresAt,
    ENT_QUOTES,
    'UTF-8'
) ?>
</strong>
</p>

<p>
<strong>Activation Link</strong>
</p>

<input
    type="text"
    value="<?= htmlspecialchars(
        $activationUrl,
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
    readonly
>

<div class="warning">

<strong>IMPORTANT:</strong>

This activation link can only be used once.

Save it securely.

It will not be displayed again.

</div>

</div>

</body>

</html>
