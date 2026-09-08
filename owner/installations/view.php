<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

requireOwnerLogin();

$id =
    (int)($_GET['id'] ?? 0);

if ($id <= 0) {

    exit('Invalid installation.');
}

$database = new OwnerDatabase();

$pdo = $database->connect();

$stmt = $pdo->prepare("
    SELECT *
    FROM owner_installations
    WHERE installation_id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$installation =
    $stmt->fetch();

if (!$installation) {

    http_response_code(404);

    exit('Installation not found.');
}

$stmt = $pdo->prepare("
    SELECT
        activation_id,
        token_label,
        domain_name,
        status,
        expires_at,
        used_at,
        used_ip,
        created_at
    FROM owner_activations
    WHERE installation_id = ?
    ORDER BY activation_id DESC
");

$stmt->execute([$id]);

$activations =
    $stmt->fetchAll();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Installation Details</title>

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

<i class="fas fa-server"></i>

Installation Details

</h2>

<a
    href="index.php"
    class="btn btn-outline-secondary"
>

Back

</a>

</div>

<div class="card border-0 shadow-sm mb-4">

<div class="card-body">

<h5>Installation Information</h5>

<div class="table-responsive">

<table class="table">

<tr>

<th>Installation Code</th>

<td>

<?= htmlspecialchars(
    $installation['installation_code']
) ?>

</td>

</tr>

<tr>

<th>Client</th>

<td>

<?= htmlspecialchars(
    $installation['client_name'] ?? '—'
) ?>

</td>

</tr>

<tr>

<th>Email</th>

<td>

<?= htmlspecialchars(
    $installation['client_email'] ?? '—'
) ?>

</td>

</tr>

<tr>

<th>Domain</th>

<td>

<?php

$clanUrl = clanAccessUrl(
    $installation['domain_name']
    ?? '-'
);

?>

<a
    href="<?= htmlspecialchars($clanUrl) ?>"
    target="_blank"
    rel="noopener"
>
    <?= htmlspecialchars($clanUrl) ?>
</a>

</td>

</tr>

<tr>

<th>Database</th>

<td>

<?= htmlspecialchars(
    $installation['database_name'] ?? '—'
) ?>

</td>

</tr>

<tr>

<th>Status</th>

<td>

<?= htmlspecialchars(
    $installation['installation_status']
) ?>

</td>

</tr>

<tr>

<th>Installed At</th>

<td>

<?= htmlspecialchars(
    $installation['installed_at'] ?? '—'
) ?>

</td>

</tr>

<tr>

<th>Activated At</th>

<td>

<?= htmlspecialchars(
    $installation['activated_at'] ?? '—'
) ?>

</td>

</tr>

<tr>

<th>Revoked At</th>

<td>

<?= htmlspecialchars(
    $installation['revoked_at'] ?? '—'
) ?>

</td>

</tr>

</table>

</div>
<!-- /.table-responsive -->

</div>

</div>

<div class="card border-0 shadow-sm">

<div class="card-body">

<h5>Activation History</h5>

<div class="table-responsive">

<table class="table table-hover">

<thead>

<tr>

<th>ID</th>

<th>Label</th>

<th>Status</th>

<th>Expires</th>

<th>Used At</th>

<th>Created</th>

</tr>

</thead>

<tbody>

<?php foreach ($activations as $activation): ?>

<tr>

<td>

<?= (int)$activation['activation_id'] ?>

</td>

<td>

<?= htmlspecialchars(
    $activation['token_label'] ?? '—'
) ?>

</td>

<td>

<?= htmlspecialchars(
    $activation['status']
) ?>

</td>

<td>

<?= htmlspecialchars(
    $activation['expires_at']
) ?>

</td>

<td>

<?= htmlspecialchars(
    $activation['used_at'] ?? '—'
) ?>

</td>

<td>

<?= htmlspecialchars(
    $activation['created_at']
) ?>

</td>

</tr>

<?php endforeach; ?>

<?php if (!$activations): ?>

<tr>

<td
    colspan="6"
    class="text-center text-muted"
>

No activation history.

</td>

</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

</body>

</html>