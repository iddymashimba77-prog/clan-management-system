<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/maindb.php';

requireOwnerLogin();

$id =
    (int)($_GET['id'] ?? 0);

if ($id <= 0) {

    exit('Invalid clan.');
}

$clanDatabase = new OwnerClanDatabase();

$pdo = $clanDatabase->connect();

$stmt = $pdo->prepare("
    SELECT *
    FROM clans
    WHERE clan_id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$clan = $stmt->fetch();

if (!$clan) {

    http_response_code(404);

    exit('Clan not found.');
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Clan | Owner Panel</title>

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
<i class="fas fa-edit page-title-icon"></i>
Edit Clan
</h3>

<p>
Update clan information for <?= htmlspecialchars($clan['clan_code']) ?>.
</p>

</div>

<div class="activation-body">

<form
    action="update.php"
    method="POST"
    autocomplete="off"
>

<?= ownerCsrfField() ?>

<input
    type="hidden"
    name="clan_id"
    value="<?= (int)$clan['clan_id'] ?>"
>

<div class="form-group">

<label>
Clan Name <span class="text-danger">*</span>
</label>

<input
    type="text"
    name="clan_name"
    class="form-control"
    maxlength="200"
    value="<?= htmlspecialchars($clan['clan_name']) ?>"
    required
>

</div>

<div class="form-group">

<label>
Clan Code
</label>

<input
    type="text"
    class="form-control"
    value="<?= htmlspecialchars($clan['clan_code']) ?>"
    disabled
>

<span class="help-text">
Clan code cannot be changed.
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
    value="<?= htmlspecialchars($clan['contact_email'] ?? '') ?>"
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
    value="<?= htmlspecialchars($clan['clan_phone'] ?? '') ?>"
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
><?= htmlspecialchars($clan['clan_address'] ?? '') ?></textarea>

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
    value="<?= htmlspecialchars($clan['clan_motto'] ?? '') ?>"
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

<?php
$activationOptions = ['pending', 'active', 'suspended', 'revoked'];
foreach ($activationOptions as $opt):
?>

<option
    value="<?= $opt ?>"
    <?= $clan['activation_status'] === $opt ? 'selected' : '' ?>
>

<?= ucfirst($opt) ?>

</option>

<?php endforeach; ?>

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

<?php
$subscriptionOptions = ['trial', 'active', 'expired', 'suspended', 'cancelled'];
foreach ($subscriptionOptions as $opt):
?>

<option
    value="<?= $opt ?>"
    <?= $clan['subscription_status'] === $opt ? 'selected' : '' ?>
>

<?= ucfirst($opt) ?>

</option>

<?php endforeach; ?>

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
    value="<?= htmlspecialchars(
        $clan['subscription_expires_at'] !== null
            ? date('Y-m-d', strtotime($clan['subscription_expires_at']))
            : ''
    ) ?>"
>

</div>

<div class="d-flex justify-content-between mt-4">

<a
    href="view.php?id=<?= (int)$clan['clan_id'] ?>"
    class="btn btn-outline-secondary"
>

<i class="fas fa-arrow-left mr-1"></i>
Back to Clan

</a>

<button
    type="submit"
    class="btn btn-primary"
>

<i class="fas fa-save mr-1"></i>
Update Clan

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
