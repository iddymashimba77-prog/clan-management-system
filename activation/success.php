<?php

declare(strict_types=1);

session_start();


if (
    empty(
        $_SESSION['system_activated']
    )
) {

    header(
        'Location: ../index.php'
    );

    exit;
}


$username =
    $_SESSION['activated_username']
    ?? 'Administrator';

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Activation Complete</title>

<link
    rel="stylesheet"
    href="../assets/plugins/bootstrap/css/bootstrap.min.css"
>

<link
    rel="stylesheet"
    href="../assets/plugins/fontawesome-free/css/all.min.css"
>

<style>

body {
    background: #f4f6f9;
}

.wrapper {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card {
    max-width: 550px;
    width: 100%;
    border: none;
    border-radius: 14px;
    box-shadow: 0 5px 25px rgba(0,0,0,.08);
}

.success-icon {
    width: 85px;
    height: 85px;
    border-radius: 50%;
    background: #d4edda;
    color: #28a745;
    font-size: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: auto;
}

</style>

</head>

<body>

<div class="wrapper">

<div class="card">

<div class="card-body text-center p-5">

<div class="success-icon mb-4">

<i class="fas fa-check"></i>

</div>


<h2>

Activation Complete

</h2>


<p class="text-muted">

Your Clan Management System has been successfully activated.

</p>


<div class="alert alert-success text-left">

<strong>Administrator:</strong>

<?= htmlspecialchars(
    $username,
    ENT_QUOTES,
    'UTF-8'
) ?>

<br>

<strong>Status:</strong>

Active

</div>


<div class="alert alert-warning text-left">

<i class="fas fa-lock mr-2"></i>

The activation link has now been permanently consumed.

It cannot be used again.

</div>


<a
    href="../login.php"
    class="btn btn-primary btn-lg"
>

<i class="fas fa-sign-in-alt mr-2"></i>

Go to Login

</a>

</div>

</div>

</div>

</body>

</html>