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


$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM users
    WHERE clan_id = ?
");

$stmt->execute([$id]);
$memberCount = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM events
    WHERE clan_id = ?
");

$stmt->execute([$id]);
$eventCount = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM contributions
    WHERE clan_id = ?
");

$stmt->execute([$id]);
$contributionCount = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM family_wallets
    WHERE clan_id = ?
");

$stmt->execute([$id]);
$walletCount = (int)$stmt->fetchColumn();

$status = $clan['activation_status'];
$subStatus = $clan['subscription_status'];

?>
<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Clan Details - Owner Panel</title>

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

<i class="fas fa-users"></i>

Clan Details

</h2>

<a
    href="index.php"
    class="btn btn-outline-secondary"
>

Back to Clans

</a>

</div>


<?php if (!empty($_GET['action'])): ?>

<div class="alert alert-success alert-dismissible fade show" role="alert">

Clan has been <?= htmlspecialchars($_GET['action']) ?> successfully.

<button type="button" class="close" data-dismiss="alert">
<span>&times;</span>
</button>

</div>

<?php endif; ?>


<div class="card border-0 shadow-sm mb-4">

<div class="card-body">

<h5 class="mb-3">

Clan Information

</h5>

<div class="table-responsive">

<table class="table">

<tr>

<th>Clan ID</th>

<td><?= (int)$clan['clan_id'] ?></td>

</tr>

<tr>

<th>Clan Code</th>

<td>

<strong>

<?= htmlspecialchars(
    $clan['clan_code']
) ?>

</strong>

</td>

</tr>

<tr>

<th>Clan Name</th>

<td>

<?= htmlspecialchars(
    $clan['clan_name']
) ?>

</td>

</tr>

<tr>

<th>Contact Email</th>

<td>

<?= htmlspecialchars(
    $clan['contact_email'] ?? '—'
) ?>

</td>

</tr>

<tr>

<th>Phone</th>

<td>

<?= htmlspecialchars(
    $clan['clan_phone'] ?? '—'
) ?>

</td>

</tr>

<tr>

<th>Address</th>

<td>

<?= htmlspecialchars(
    $clan['clan_address'] ?? '—'
) ?>

</td>

</tr>

<tr>

<th>Motto</th>

<td>

<?= htmlspecialchars(
    $clan['clan_motto'] ?? '—'
) ?>

</td>

</tr>

<tr>

<th>Domain</th>

<td>

<?= htmlspecialchars(
    $clan['domain_name'] ?? '—'
) ?>

</td>

</tr>

<tr>

<th>Installation Code</th>

<td>

<?= htmlspecialchars(
    $clan['installation_code'] ?? '—'
) ?>

</td>

</tr>

<tr>

<th>Activation Status</th>

<td>

<span class="badge badge-<?= $status === 'active' ? 'success' : ($status === 'suspended' ? 'warning' : ($status === 'revoked' ? 'danger' : 'secondary')) ?>">

<?= htmlspecialchars(
    ucfirst($status)
) ?>

</span>

</td>

</tr>

<tr>

<th>Subscription Status</th>

<td>

<span class="badge badge-<?= $subStatus === 'active' ? 'success' : ($subStatus === 'suspended' ? 'warning' : ($subStatus === 'cancelled' || $subStatus === 'expired' ? 'danger' : 'info')) ?>">

<?= htmlspecialchars(
    ucfirst($subStatus)
) ?>

</span>

</td>

</tr>

<tr>

<th>Subscription Expires</th>

<td>

<?= htmlspecialchars(
    $clan['subscription_expires_at'] ?? '—'
) ?>

</td>

</tr>

<tr>

<th>Max Members</th>

<td>

<?= (int)($clan['max_members'] ?? 0) ?>

</td>

</tr>

<tr>

<th>Activated At</th>

<td>

<?= htmlspecialchars(
    $clan['activated_at'] ?? '—'
) ?>

</td>

</tr>

<tr>

<th>Created At</th>

<td>

<?= htmlspecialchars(
    $clan['created_at']
) ?>

</td>

</tr>

<tr>

<th>Updated At</th>

<td>

<?= htmlspecialchars(
    $clan['updated_at']
) ?>

</td>

</tr>

</table>

</div>
<!-- /.table-responsive -->

</div>

</div>


<div class="card border-0 shadow-sm mb-4">

<div class="card-body">

<h5 class="mb-3">

Clan Statistics

</h5>

<div class="row">

<div class="col-md-3 mb-3">

<div class="card border-0 shadow-sm text-center p-3">

<h3 class="text-primary"><?= $memberCount ?></h3>

<small class="text-muted">Members</small>

</div>

</div>

<div class="col-md-3 mb-3">

<div class="card border-0 shadow-sm text-center p-3">

<h3 class="text-success"><?= $eventCount ?></h3>

<small class="text-muted">Events</small>

</div>

</div>

<div class="col-md-3 mb-3">

<div class="card border-0 shadow-sm text-center p-3">

<h3 class="text-info"><?= $contributionCount ?></h3>

<small class="text-muted">Contributions</small>

</div>

</div>

<div class="col-md-3 mb-3">

<div class="card border-0 shadow-sm text-center p-3">

<h3 class="text-warning"><?= $walletCount ?></h3>

<small class="text-muted">Wallets</small>

</div>

</div>

</div>

</div>

</div>


<div class="card border-0 shadow-sm mb-4">

<div class="card-body">

<h5 class="mb-3">

Actions

</h5>

<div class="d-flex flex-wrap gap-2">

<a
    href="edit.php?id=<?= (int)$clan['clan_id'] ?>"
    class="btn btn-primary"
>

<i class="fas fa-edit mr-1"></i>
Edit Clan

</a>

<?php if ($status !== 'suspended'): ?>

<form
    action="suspend.php"
    method="POST"
    class="d-inline"
    onsubmit="return confirm('Suspend this clan?');"
>

<?= ownerCsrfField() ?>

<input
    type="hidden"
    name="clan_id"
    value="<?= (int)$clan['clan_id'] ?>"
>

<button
    type="submit"
    class="btn btn-warning"
>

<i class="fas fa-pause mr-1"></i>
Suspend

</button>

</form>

<?php endif; ?>

<?php if ($status !== 'active'): ?>

<form
    action="activate.php"
    method="POST"
    class="d-inline"
    onsubmit="return confirm('Activate this clan?');"
>

<?= ownerCsrfField() ?>

<input
    type="hidden"
    name="clan_id"
    value="<?= (int)$clan['clan_id'] ?>"
>

<button
    type="submit"
    class="btn btn-success"
>

<i class="fas fa-play mr-1"></i>
Activate

</button>

</form>

<?php endif; ?>

<?php if ($status !== 'revoked'): ?>

<form
    action="revoke.php"
    method="POST"
    class="d-inline"
    onsubmit="return confirm('Revoke this clan? This cannot be undone.');"
>

<?= ownerCsrfField() ?>

<input
    type="hidden"
    name="clan_id"
    value="<?= (int)$clan['clan_id'] ?>"
>

<button
    type="submit"
    class="btn btn-danger"
>

<i class="fas fa-ban mr-1"></i>
Revoke

</button>

</form>

<?php endif; ?>

</div>

</div>

</div>

</div>

</body>

</html>
