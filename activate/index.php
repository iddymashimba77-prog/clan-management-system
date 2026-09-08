<?php
declare(strict_types=1);

session_start();

require_once '../config/database.php';
require_once '../config/constants.php';

$token = trim(
    $_GET['token'] ?? ''
);

$error = '';
$success = false;

if ($token === '') {

    $error =
        'Invalid activation token.';

} else {

    try {

        $database = new Database();

        $pdo = $database->connect();


        /*
        |--------------------------------------------------------------------------
        | Find active tokens
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->query("
            SELECT *
            FROM activation_tokens
            WHERE token_status = 'active'
            ORDER BY activation_id DESC
        ");

        $tokens = $stmt->fetchAll();


        $matchedToken = null;


        foreach ($tokens as $row) {

            if (
                password_verify(
                    $token,
                    $row['token_hash']
                )
            ) {

                $matchedToken = $row;

                break;
            }
        }


        if (!$matchedToken) {

            $error =
                'This activation token is invalid, already used, or revoked.';

        } else {


            /*
            |--------------------------------------------------------------------------
            | Check expiration
            |--------------------------------------------------------------------------
            */

            if (
                !empty($matchedToken['expires_at']) &&
                strtotime(
                    $matchedToken['expires_at']
                ) < time()
            ) {

                $stmt = $pdo->prepare("
                    UPDATE activation_tokens
                    SET token_status = 'expired'
                    WHERE activation_id = ?
                ");

                $stmt->execute([
                    $matchedToken['activation_id']
                ]);

                $error =
                    'This activation token has expired.';

            } else {

                /*
                |--------------------------------------------------------------------------
                | One-time activation
                |--------------------------------------------------------------------------
                */

                $pdo->beginTransaction();

                try {

                    $stmt = $pdo->prepare("
                        UPDATE activation_tokens
                        SET
                            token_status = 'used',
                            used_at = NOW(),
                            ip_address = ?,
                            user_agent = ?
                        WHERE activation_id = ?
                        AND token_status = 'active'
                    ");

                    $stmt->execute([

                        $_SERVER['REMOTE_ADDR']
                            ?? null,

                        $_SERVER['HTTP_USER_AGENT']
                            ?? null,

                        $matchedToken['activation_id']

                    ]);


                    if ($stmt->rowCount() !== 1) {

                        throw new RuntimeException(
                            'Activation token has already been used.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Save installation binding
                    |--------------------------------------------------------------------------
                    */

                    $_SESSION[
                        'activation_verified'
                    ] = true;

                    $_SESSION[
                        'installation_id'
                    ] =
                        $matchedToken[
                            'installation_id'
                        ];


                    $pdo->commit();


                    $success = true;


                } catch (Throwable $e) {

                    if (
                        $pdo->inTransaction()
                    ) {

                        $pdo->rollBack();
                    }

                    throw $e;
                }
            }
        }

    } catch (Throwable $e) {

        $error =
            'Activation failed: ' .
            $e->getMessage();
    }
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

<title>
    Clan Management System - Activation
</title>

<style>

body {
    margin:0;
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#f4f7fb;
    font-family:Arial, sans-serif;
}

.card {
    width:90%;
    max-width:600px;
    background:#fff;
    padding:40px;
    border-radius:14px;
    box-shadow:0 10px 35px rgba(0,0,0,.10);
    text-align:center;
}

.success {
    color:#198754;
}

.error {
    color:#dc3545;
}

</style>

</head>

<body>

<div class="card">

<?php if ($success): ?>

    <h1 class="success">
        Activation Successful
    </h1>

    <p>
        This installation has been successfully activated.
    </p>

    <p>
        Installation ID:
    </p>

    <strong>
        <?= htmlspecialchars(
            $_SESSION['installation_id']
        ) ?>
    </strong>

    <p>
        This activation token has now been permanently locked.
    </p>

<?php else: ?>

    <h1 class="error">
        Activation Failed
    </h1>

    <p>
        <?= htmlspecialchars($error) ?>
    </p>

<?php endif; ?>

</div>

</body>

</html>