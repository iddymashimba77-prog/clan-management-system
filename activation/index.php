<?php

declare(strict_types=1);

session_start();

require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/installation.php';
require_once '../config/activation.php';

$token =
    trim($_GET['token'] ?? '');

$error = null;
$activation = null;


try {

    $database =
        new Database();

    $pdo =
        $database->connect();


    if ($token === '') {

        throw new RuntimeException(
            'Activation token is missing.'
        );
    }


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


} catch (Throwable $e) {

    $error =
        $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Clan Management System - Activation</title>

<link
    rel="stylesheet"
    href="../assets/plugins/fontawesome-free/css/all.min.css"
>

<link
    rel="stylesheet"
    href="../assets/plugins/bootstrap/css/bootstrap.min.css"
>

<style>

body {
    background: #f4f6f9;
}

.activation-wrapper {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.activation-card {
    width: 100%;
    max-width: 550px;
    border-radius: 12px;
    border: none;
    box-shadow: 0 5px 25px rgba(0,0,0,.08);
}

.activation-icon {
    width: 75px;
    height: 75px;
    border-radius: 50%;
    background: #e8f1ff;
    color: #007bff;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 32px;
}

</style>

</head>

<body>

<div class="activation-wrapper">

<div class="card activation-card">

<div class="card-body p-5 text-center">

<div class="activation-icon">

<i class="fas fa-shield-alt"></i>

</div>


<h3 class="mb-2">

Clan Management System

</h3>


<p class="text-muted">

Production Installation Activation

</p>


<?php if ($error): ?>

<div class="alert alert-danger text-left">

<i class="fas fa-exclamation-circle mr-2"></i>

<?= htmlspecialchars(
    $error,
    ENT_QUOTES,
    'UTF-8'
) ?>

</div>


<a
    href="../index.php"
    class="btn btn-secondary"
>

<i class="fas fa-home mr-1"></i>

Return Home

</a>


<?php else: ?>


<div class="alert alert-success text-left">

<i class="fas fa-check-circle mr-2"></i>

Activation link verified successfully.

</div>


<div class="text-left mb-4">

<p class="mb-1">

<strong>Administrator:</strong>

<?= htmlspecialchars(
    trim(
        $activation['first_name'] .
        ' ' .
        $activation['last_name']
    ),
    ENT_QUOTES,
    'UTF-8'
) ?>

</p>


<p class="mb-1">

<strong>Username:</strong>

<?= htmlspecialchars(
    $activation['username'],
    ENT_QUOTES,
    'UTF-8'
) ?>

</p>


<p class="mb-0">

<strong>Domain:</strong>

<?= htmlspecialchars(
    $activation['domain_name'],
    ENT_QUOTES,
    'UTF-8'
) ?>

</p>

</div>


<form
    action="process.php"
    method="POST"
>

<input
    type="hidden"
    name="token"
    value="<?= htmlspecialchars(
        $token,
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
>


<button
    type="submit"
    class="btn btn-primary btn-lg btn-block"
>

<i class="fas fa-key mr-2"></i>

Activate System

</button>

</form>


<div class="alert alert-warning mt-4 text-left">

<i class="fas fa-lock mr-2"></i>

<strong>Important:</strong>

This activation link can only be used once.

After activation, the link will permanently stop working.

</div>


<?php endif; ?>

</div>

</div>

</div>

</body>

</html>