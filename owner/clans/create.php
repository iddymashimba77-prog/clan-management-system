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

<title>Create Clan | Owner Panel</title>

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
<i class="fas fa-users page-title-icon"></i>
Create New Clan
</h3>

<p>
Register a new clan (tenant) in the system.
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
Clan Name <span class="text-danger">*</span>
</label>

<input
    type="text"
    name="clan_name"
    class="form-control"
    maxlength="200"
    placeholder="Enter clan name"
    required
>

</div>

<div class="form-group">

<label>
Clan Code
</label>

<input
    type="text"
    name="clan_code"
    class="form-control"
    maxlength="50"
    placeholder="Auto-generated if left empty"
>

<span class="help-text">
Leave empty to auto-generate (CLN-XXXX).
</span>

</div>

<div class="form-group">

<label>
Dedicated Database Name
</label>

<input
    type="text"
    name="database_name"
    class="form-control"
    maxlength="64"
    placeholder="e.g. clan_ukoo_wa_shabani"
>

<span class="help-text">
Optional. If provided, a dedicated MySQL database is auto-created
(same tables/structure as the original) and this clan is isolated in its
own database. A "clan_" prefix is added automatically (e.g. "clan_&lt;your_name&gt;").
Leave empty to keep the clan in the shared database.
Only letters, numbers and underscores are allowed.
</span>

</div>

<div class="form-group">

<label>
Contact Email
</label>

<input
    type="email"
    name="contact_email"
    class="form-control"
    maxlength="255"
    placeholder="contact@example.com"
>

</div>

<div class="form-group">

<label>
Phone
</label>

<input
    type="text"
    name="clan_phone"
    class="form-control"
    maxlength="50"
    placeholder="+255 700 000 000"
>

</div>

<div class="form-group">

<label>
Address
</label>

<textarea
    name="clan_address"
    class="form-control"
    rows="3"
    placeholder="Clan address"
></textarea>

</div>

<div class="form-group">

<label>
Clan Motto
</label>

<input
    type="text"
    name="clan_motto"
    class="form-control"
    maxlength="255"
    placeholder="Enter clan motto"
>

</div>

<div class="form-group">

<label>
Clan Administrator Username
</label>

<input
    type="text"
    name="admin_username"
    class="form-control"
    maxlength="100"
    placeholder="e.g. admin_ukoo"
>

<span class="help-text">
Required when creating a dedicated database. This user becomes the first
member (administrator) of the clan.
</span>

</div>

<div class="form-group">

<label>
Clan Administrator Password
</label>

<input
    type="password"
    name="admin_password"
    class="form-control"
    maxlength="100"
    placeholder="Minimum 8 characters"
>

</div>

<div class="form-group">

<label>
Clan Administrator Email
</label>

<input
    type="email"
    name="admin_email"
    class="form-control"
    maxlength="255"
    placeholder="admin@ukoo.example"
>

</div>

<div class="form-group">

<label>
Activation Status
</label>

<select
    name="activation_status"
    class="form-control"
>

<option value="pending" selected>Pending</option>
<option value="active">Active</option>
<option value="suspended">Suspended</option>
<option value="revoked">Revoked</option>

</select>

</div>

<div class="form-group">

<label>
Subscription Status
</label>

<select
    name="subscription_status"
    class="form-control"
>

<option value="trial" selected>Trial</option>
<option value="active">Active</option>
<option value="expired">Expired</option>
<option value="suspended">Suspended</option>
<option value="cancelled">Cancelled</option>

</select>

</div>

<div class="form-group">

<label>
Subscription Expires At
</label>

<input
    type="date"
    name="subscription_expires_at"
    class="form-control"
>

</div>

<div class="d-flex justify-content-between mt-4">

<a
    href="index.php"
    class="btn btn-outline-secondary"
>

<i class="fas fa-arrow-left mr-1"></i>
Back to Clans

</a>

<button
    type="submit"
    class="btn btn-primary"
>

<i class="fas fa-users mr-1"></i>
Create Clan

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
