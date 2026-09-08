<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

requireOwnerLogin();

$id =
    (int)($_GET['id'] ?? 0);

if ($id <= 0) {

    exit('Invalid activation.');
}

$database = new OwnerDatabase();

$pdo = $database->connect();

$stmt = $pdo->prepare("
    SELECT
        a.*,
        i.installation_code,
        i.client_name,
        i.client_email,
        i.domain_name AS installation_domain,
        i.database_name,
        i.installation_status,
        i.installed_at,
        i.activated_at,
        i.revoked_at
    FROM owner_activations a
    INNER JOIN owner_installations i
        ON i.installation_id = a.installation_id
    WHERE a.activation_id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$activation =
    $stmt->fetch();

if (!$activation) {

    http_response_code(404);

    exit('Activation not found.');
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

<title>Activation Details</title>

<link
    rel="stylesheet"
    href="../../assets/plugins/bootstrap/css/bootstrap.min.css"
>

<link
    rel="stylesheet"
    href="../../assets/plugins/fontawesome-free/css/all.min.css"
>

</head>

<body style="background:#f4f6f9;">

<div class="container py-5">

<div class="d-flex justify-content-between mb-4">

<h2>

<i class="fas fa-key"></i>

Activation Details

</h2>

<a
    href="index.php"
    class="btn btn-outline-secondary"
>

Back

</a>

</div>

<div class="card border-0 shadow-sm">

<div class="card-body p-4">

<h5 class="mb-3">

Installation

</h5>

<div class="table-responsive">

<table class="table">

<tr>

<th>Installation Code</th>

<td>

<?= htmlspecialchars(
    $activation['installation_code']
) ?>

</td>

</tr>

<tr>

<th>Client</th>

<td>

<?= htmlspecialchars(
    $activation['client_name'] ?? ''
) ?>

</td>

</tr>

<tr>

<th>Email</th>

<td>

<?= htmlspecialchars(
    $activation['client_email'] ?? ''
) ?>

</td>

</tr>

<tr>

<th>Domain</th>

<td>

<?= htmlspecialchars(
    $activation['installation_domain'] ?? '—'
) ?>

</td>

</tr>

<tr>

<th>Database</th>

<td>

<?= htmlspecialchars(
    $activation['database_name'] ?? '—'
) ?>

</td>

</tr>

<tr>

<th>Installation Status</th>

<td>

<?= htmlspecialchars(
    $activation['installation_status']
) ?>

</td>

</tr>

<tr>

<th>Installed At</th>

<td>

<?= htmlspecialchars(
    $activation['installed_at'] ?? '—'
) ?>

</td>

</tr>

<tr>

<th>Activated At</th>

<td>

<?= htmlspecialchars(
    $activation['activated_at'] ?? '—'
) ?>

</td>

</tr>

</table>

</div>
<!-- /.table-responsive -->

<hr>

<h5 class="mb-3">

Activation

</h5>

<div class="table-responsive">

<table class="table">

<tr>

<th>Activation ID</th>

<td>

<?= (int)$activation['activation_id'] ?>

</td>

</tr>

<tr>

<th>Label</th>

<td>

<?= htmlspecialchars(
    $activation['token_label'] ?? '—'
) ?>

</td>

</tr>

<tr>

<th>Status</th>

<td>

<?= htmlspecialchars(
    $activation['status']
) ?>

</td>

</tr>

<tr>

<th>Expires At</th>

<td>

<?= htmlspecialchars(
    $activation['expires_at']
) ?>

</td>

</tr>

<tr>

<th>Used At</th>

<td>

<?= htmlspecialchars(
    $activation['used_at'] ?? '—'
) ?>

</td>

</tr>

<tr>

<th>Used IP</th>

<td>

<?= htmlspecialchars(
    $activation['used_ip'] ?? '—'
) ?>

</td>

</tr>

<tr>

<th>Created At</th>

<td>

<?= htmlspecialchars(
    $activation['created_at']
) ?>

</td>

</tr>

</table>

</div>
<!-- /.table-responsive -->

<?php if ($activation['status'] === 'generated'): ?>

<hr>

<form
    action="revoke.php"
    method="POST"
    onsubmit="return confirm('Revoke this activation?');"
>

<?= ownerCsrfField() ?>

<input
    type="hidden"
    name="activation_id"
    value="<?= (int)$activation['activation_id'] ?>"
>

<button
    type="submit"
    class="btn btn-danger"
>

<i class="fas fa-ban mr-1"></i>

Revoke Activation

</button>

</form>

<?php endif; ?>

</div>

</div>

</div>

</body>

</html>