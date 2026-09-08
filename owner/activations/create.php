<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

requireOwnerLogin();

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Create Activation | Owner Panel</title>

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

.activation-header {
    background: #ffffff;
    border-bottom: 1px solid #e5e7eb;
    padding: 25px 30px;
}

.activation-header h3 {
    margin: 0;
    font-weight: 600;
    color: #1f2937;
}

.activation-header p {
    margin: 7px 0 0;
    color: #6b7280;
}

.activation-body {
    background: #ffffff;
    padding: 30px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 7px;
}

.form-control {
    border-radius: 8px;
    min-height: 44px;
    border: 1px solid #d1d5db;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.15rem rgba(0,123,255,.12);
}

.help-text {
    display: block;
    margin-top: 6px;
    color: #6b7280;
    font-size: 13px;
}

.btn {
    border-radius: 8px;
    padding: 9px 18px;
}

.page-title-icon {
    color: #007bff;
    margin-right: 8px;
}

</style>

</head>

<body>

<div class="container-fluid page-wrapper">

<div class="row justify-content-center">

<div class="col-lg-8 col-md-10">

<div class="card activation-card">

<div class="activation-header">

<h3>
<i class="fas fa-key page-title-icon"></i>
Create New Activation
</h3>

<p>
Create an activation license for a new Clan Management System installation.
</p>

</div>

<div class="activation-body">

<form
    action="store.php"
    method="POST"
    autocomplete="off"
>

<?= ownerCsrfField() ?>

<div class="form-group">

<label>
Client Name
</label>

<input
    type="text"
    name="client_name"
    class="form-control"
    maxlength="200"
    placeholder="Enter client name"
    required
>

</div>

<div class="form-group">

<label>
Client Email
</label>

<input
    type="email"
    name="client_email"
    class="form-control"
    maxlength="255"
    placeholder="client@example.com"
>

</div>

<div class="form-group">

<label>
Domain Name
</label>

<input
    type="text"
    name="domain_name"
    class="form-control"
    maxlength="255"
    placeholder="example.com"
>

<span class="help-text">
Leave empty if the client has not provided the domain yet.
</span>

</div>

<div class="form-group">

<label>
Database Name
</label>

<input
    type="text"
    name="database_name"
    class="form-control"
    maxlength="255"
    placeholder="clan_production"
>

<span class="help-text">
The database is created with a "clan_" prefix automatically (e.g. "clan_&lt;your_name&gt;").

</div>

<div class="form-group">

<label>
Activation Label
</label>

<input
    type="text"
    name="token_label"
    class="form-control"
    maxlength="100"
    placeholder="Client License"
>

</div>

<div class="form-group">

<label>
Activation Validity
</label>

<select
    name="validity_days"
    class="form-control"
>

<option value="1">1 Day</option>
<option value="7">7 Days</option>
<option value="30">30 Days</option>
<option value="90">90 Days</option>
<option value="365" selected>1 Year</option>

</select>

</div>

<hr>

<h5>
<i class="fas fa-user-shield page-title-icon"></i>
Clan Administrator Login
</h5>

<p class="help-text">
Set the login credentials the clan administrator will use to access
the system at the installation URL.
</p>

<div class="form-group">

<label>
Administrator Username
</label>

<input
    type="text"
    name="admin_username"
    class="form-control"
    maxlength="100"
    placeholder="e.g. shabani_admin"
    required
>

<span class="help-text">
Username the clan administrator will use to log in.
</span>

</div>

<div class="form-group">

<label>
Administrator Email
</label>

<input
    type="email"
    name="admin_email"
    class="form-control"
    maxlength="255"
    placeholder="admin@example.com"
>

<span class="help-text">
Optional. Used if the administrator prefers to log in with email.
</span>

</div>

<div class="form-group">

<label>
Administrator Password
</label>

<input
    type="password"
    name="admin_password"
    class="form-control"
    minlength="8"
    maxlength="255"
    placeholder="Minimum 8 characters"
    required
>

<span class="help-text">
The clan administrator will use this password to log in.
</span>

</div>

<div class="form-group">

<label>
Confirm Administrator Password
</label>

<input
    type="password"
    name="admin_password_confirm"
    class="form-control"
    minlength="8"
    maxlength="255"
    placeholder="Re-enter the password"
    required
>

</div>

<div class="d-flex justify-content-between mt-4">

<a
    href="../index.php"
    class="btn btn-outline-secondary"
>

<i class="fas fa-arrow-left mr-1"></i>
Back to Dashboard

</a>

<button
    type="submit"
    class="btn btn-primary"
>

<i class="fas fa-key mr-1"></i>
Create Activation

</button>

</div>

</form>

</div>

</div>

</div>

</div>

</div>

</body>

</html>