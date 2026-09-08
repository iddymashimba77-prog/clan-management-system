<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/maindb.php';

requireOwnerLogin();

$database = new OwnerDatabase();

$pdo = $database->connect();

$installationId =
    (int)($_GET['id'] ?? 0);

$validityDays =
    (int)($_GET['days'] ?? 365);

$tokenLabel =
    trim($_GET['label'] ?? '');


/*
|--------------------------------------------------------------------------
| Validate installation ID
|--------------------------------------------------------------------------
*/

if ($installationId <= 0) {

    http_response_code(400);

    exit('Invalid installation.');
}


/*
|--------------------------------------------------------------------------
| Validate validity
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| Get Installation
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        installation_id,
        clan_id,
        installation_code,
        client_name,
        client_email,
        domain_name,
        database_name,
        installation_status
    FROM owner_installations
    WHERE installation_id = ?
    LIMIT 1
");

$stmt->execute([
    $installationId
]);

$installation =
    $stmt->fetch();


if (!$installation) {

    http_response_code(404);

    exit('Installation not found.');
}


/*
|--------------------------------------------------------------------------
| Get Clan Administrator
|--------------------------------------------------------------------------
|
| The Owner set the clan administrator login during activation creation.
| We surface it here so the Owner can hand the credentials to the client.
|
*/

$adminUsername = '';
$adminEmail = '';

if (!empty($installation['clan_id'])) {

    try {

        $clanDatabase = new OwnerClanDatabase();

        $clanPdo = $clanDatabase->connect();

        $stmt = $clanPdo->prepare("
            SELECT
                u.username,
                u.email
            FROM users u
            WHERE u.clan_id = ?
              AND u.role_id = 5
              AND u.is_deleted = 0
              AND u.account_status = 'active'
            ORDER BY u.user_id ASC
            LIMIT 1
        ");

        $stmt->execute([
            (int)$installation['clan_id']
        ]);

        $admin = $stmt->fetch();

        if ($admin) {

            $adminUsername =
                (string)($admin['username'] ?? '');

            $adminEmail =
                (string)($admin['email'] ?? '');
        }

    } catch (Throwable $e) {

        $adminUsername = '';
        $adminEmail = '';
    }
}


/*
|--------------------------------------------------------------------------
| Check Existing Active Token
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT activation_id
    FROM owner_activations
    WHERE installation_id = ?
      AND status = 'generated'
      AND expires_at > NOW()
    LIMIT 1
");

$stmt->execute([
    $installationId
]);

$existingActivation =
    $stmt->fetchColumn();


if ($existingActivation) {

    exit(
        'This installation already has an active activation token.'
    );
}


/*
|--------------------------------------------------------------------------
| Generate Token
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
| Hash Token
|--------------------------------------------------------------------------
*/

$tokenHash =
    password_hash(
        $plainToken,
        PASSWORD_DEFAULT
    );


/*
|--------------------------------------------------------------------------
| Expiration
|--------------------------------------------------------------------------
*/

$expiresAt =
    date(
        'Y-m-d H:i:s',
        time() +
        (
            $validityDays *
            24 *
            60 *
            60
        )
    );


/*
|--------------------------------------------------------------------------
| Domain
|--------------------------------------------------------------------------
*/

$domainName =
    trim(
        $installation['domain_name']
        ?? ''
    );


/*
|--------------------------------------------------------------------------
| Create Activation
|--------------------------------------------------------------------------
*/

try {

    $pdo->beginTransaction();


    $stmt = $pdo->prepare("
        INSERT INTO owner_activations
        (
            installation_id,
            clan_id,
            token_hash,
            token_label,
            domain_name,
            status,
            expires_at,
            created_by
        )
        VALUES
        (
            ?,
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

        !empty($installation['clan_id'])
            ? (int)$installation['clan_id']
            : null,

        $tokenHash,

        $tokenLabel !== ''
            ? $tokenLabel
            : 'Client License',

        $domainName !== ''
            ? $domainName
            : null,

        $expiresAt,

        (int)$_SESSION['owner_id']

    ]);


    $activationId =
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
            'generate_activation',
            'activation',
            ?,
            ?,
            ?,
            ?
        )
    ");


    $description =
        'Generated activation for installation ' .
        $installation['installation_code'] .
        '. Valid for ' .
        $validityDays .
        ' days.';


    $stmt->execute([

        (int)$_SESSION['owner_id'],

        $activationId,

        $description,

        $_SERVER['REMOTE_ADDR']
            ?? null,

        $_SERVER['HTTP_USER_AGENT']
            ?? null

    ]);


    $pdo->commit();

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {

        $pdo->rollBack();
    }

    http_response_code(500);

    exit(
        'Activation generation failed: ' .
        htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        )
    );
}


/*
|--------------------------------------------------------------------------
| Activation URL
|--------------------------------------------------------------------------
|
| Client will open this URL.
|
*/

$activationUrl =
    rtrim(
        defined('BASE_URL')
            ? BASE_URL
            : 'http://localhost/Clan_Management_System',
        '/'
    ) .
    '/owner/activate/?token=' .
    urlencode($plainToken);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Activation Generated | Owner Panel</title>

<link
    rel="stylesheet"
    href="../../assets/plugins/bootstrap/css/bootstrap.min.css"
>

<link
    rel="stylesheet"
    href="../../assets/plugins/fontawesome-free/css/all.min.css"
>

<style>

body {
    background: #f4f6f9;
    font-family: Arial, Helvetica, sans-serif;
}

.page-wrapper {
    padding: 40px 20px;
}

.activation-card {
    border: none;
    border-radius: 14px;
    box-shadow: 0 5px 20px rgba(0,0,0,.07);
    overflow: hidden;
}

.card-header-custom {
    background: #ffffff;
    padding: 25px 30px;
    border-bottom: 1px solid #e5e7eb;
}

.card-header-custom h3 {
    margin: 0;
    color: #1f2937;
    font-weight: 600;
}

.card-body-custom {
    background: #ffffff;
    padding: 30px;
}

.info-box {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 18px;
    margin-bottom: 20px;
}

.info-row {
    margin-bottom: 8px;
}

.info-row:last-child {
    margin-bottom: 0;
}

.info-label {
    font-weight: 600;
    color: #4b5563;
}

.url-box {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.url-input {
    flex: 1;
    border: 1px solid #ced4da;
    border-radius: 8px;
    padding: 11px 13px;
    background: #f8f9fa;
    font-size: 14px;
}

.copy-btn {
    min-width: 110px;
}

.warning-box {
    margin-top: 20px;
    padding: 15px;
    border-radius: 8px;
    background: #fff8e1;
    border: 1px solid #ffe082;
    color: #795548;
}

.success-icon {
    color: #28a745;
    margin-right: 8px;
}

.btn {
    border-radius: 8px;
}

@media (max-width: 575.98px) {
    .url-box {
        flex-direction: column;
        align-items: stretch;
    }
    .copy-btn {
        min-width: 0;
        width: 100%;
    }
}

</style>

</head>

<body>

<div class="container-fluid page-wrapper">

<div class="row justify-content-center">

<div class="col-lg-9 col-md-11">

<div class="card activation-card">

<div class="card-header-custom">

<h3>

<i class="fas fa-check-circle success-icon"></i>

Activation Generated Successfully

</h3>

</div>

<div class="card-body-custom">

<div class="info-box">

<div class="info-row">

<span class="info-label">
Installation:
</span>

<?= htmlspecialchars(
    $installation['installation_code']
) ?>

</div>

<div class="info-row">

<span class="info-label">
Client:
</span>

<?= htmlspecialchars(
    $installation['client_name']
) ?>

</div>

<div class="info-row">

<span class="info-label">
Domain:
</span>

<?= htmlspecialchars(
    $installation['domain_name']
    ?? 'Not specified'
) ?>

</div>

<div class="info-row">

<span class="info-label">
Validity:
</span>

<?= $validityDays ?> days

</div>

<div class="info-row">

<span class="info-label">
Expires:
</span>

<?= htmlspecialchars(
    $expiresAt
) ?>

</div>

</div>


<?php if ($adminUsername !== ''): ?>

    <h5 class="mt-4">

    <i class="fas fa-user-shield text-primary mr-2"></i>

    Clan Administrator Login

    </h5>

    <div class="info-box">

    <div class="info-row">

    <span class="info-label">
    Username:
    </span>

    <?= htmlspecialchars($adminUsername) ?>

    </div>

    <?php if ($adminEmail !== ''): ?>

        <div class="info-row">

        <span class="info-label">
        Email:
        </span>

        <?= htmlspecialchars($adminEmail) ?>

        </div>

    <?php endif; ?>

    <div class="info-row">

    <span class="info-label">
    Password:
    </span>

    <em>
    As set by you (Owner) when creating the clan.
    </em>

    </div>

    <div class="info-row">

    <span class="info-label">
    Login URL:
    </span>

    <?= htmlspecialchars(
        rtrim(
            defined('BASE_URL')
                ? BASE_URL
                : 'http://localhost/Clan_Management_System',
            '/'
        ),
        ENT_QUOTES,
        'UTF-8'
    ) ?>

    </div>

    </div>

<?php endif; ?>


<h5 class="mt-4">

<i class="fas fa-link text-primary mr-2"></i>

Client Activation URL

</h5>

<div class="url-box">

<input
    type="text"
    id="activationUrl"
    class="url-input"
    value="<?= htmlspecialchars(
        $activationUrl,
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
    readonly
>

<button
    type="button"
    class="btn btn-primary copy-btn"
    onclick="copyActivationUrl()"
>

<i class="fas fa-copy mr-1"></i>

Copy

</button>

</div>


<div
    id="copyMessage"
    class="text-success mt-2"
    style="display:none;"
>

<i class="fas fa-check"></i>

Activation URL copied successfully.

</div>


<div class="warning-box">

<strong>
<i class="fas fa-exclamation-triangle mr-1"></i>
Important:
</strong>

This activation URL contains a one-time activation token.
Store and send it securely to the client.
Once the token is used, it cannot be reused.

</div>


<div class="d-flex justify-content-between mt-4">

<a
    href="../index.php"
    class="btn btn-outline-secondary"
>

<i class="fas fa-home mr-1"></i>

Owner Dashboard

</a>

<a
    href="index.php"
    class="btn btn-primary"
>

<i class="fas fa-list mr-1"></i>

View Activations

</a>

</div>

</div>

</div>

</div>

</div>

</div>


<script>

function copyActivationUrl()
{
    const input =
        document.getElementById(
            'activationUrl'
        );

    const message =
        document.getElementById(
            'copyMessage'
        );

    navigator.clipboard.writeText(
        input.value
    ).then(function () {

        message.style.display = 'block';

        setTimeout(function () {

            message.style.display = 'none';

        }, 3000);

    }).catch(function () {

        input.select();

        document.execCommand('copy');

        message.style.display = 'block';

    });
}

</script>

</body>

</html>