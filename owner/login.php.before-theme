<?php

declare(strict_types=1);

require_once __DIR__ . '/config/bootstrap.php';

if (ownerIsLoggedIn()) {

    header('Location: dashboard.php');

    exit;
}

$error = $_SESSION['owner_error'] ?? null;

unset($_SESSION['owner_error']);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Owner Panel - Login</title>

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

.owner-login {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.owner-card {
    width: 100%;
    max-width: 430px;
    border: none;
    border-radius: 14px;
    box-shadow: 0 8px 30px rgba(0,0,0,.08);
}

.owner-icon {
    width: 75px;
    height: 75px;
    margin: 0 auto 20px;
    border-radius: 50%;
    background: #e8f1ff;
    color: #007bff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
}

</style>

</head>

<body>

<div class="owner-login">

<div class="card owner-card">

<div class="card-body p-5">

<div class="owner-icon">

<i class="fas fa-user-shield"></i>

</div>

<h3 class="text-center">
Owner Panel
</h3>

<p class="text-center text-muted mb-4">
Clan Management System Developer Control
</p>

<?php if ($error): ?>

<div class="alert alert-danger">

<?= htmlspecialchars(
    $error,
    ENT_QUOTES,
    'UTF-8'
) ?>

</div>

<?php endif; ?>

<form
    action="login_process.php"
    method="POST"
>

<?= ownerCsrfField() ?>

<div class="form-group">

<label>Username or Email</label>

<input
    type="text"
    name="login"
    class="form-control"
    required
>

</div>

<div class="form-group">

<label>Password</label>

<input
    type="password"
    name="password"
    class="form-control"
    required
>

</div>

<button
    type="submit"
    class="btn btn-primary btn-block"
>

<i class="fas fa-sign-in-alt mr-1"></i>

Sign In

</button>

</form>

</div>

</div>

</div>

</body>

</html>