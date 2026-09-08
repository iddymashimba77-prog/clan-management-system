<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

requireOwnerLogin();

$database = new OwnerDatabase();

$pdo = $database->connect();

$stmt = $pdo->query("
    SELECT
        l.*,
        u.username
    FROM owner_activity_logs l
    LEFT JOIN owner_users u
        ON u.owner_id = l.owner_id
    ORDER BY l.log_id DESC
    LIMIT 200
");

$logs =
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

<title>Owner Activity Logs</title>

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

<div class="container-fluid py-4">

<div class="d-flex justify-content-between mb-4">

<h2>

<i class="fas fa-history"></i>

Owner Activity Logs

</h2>

<a
    href="../index.php"
    class="btn btn-outline-secondary"
>

Dashboard

</a>

</div>

<div class="card border-0 shadow-sm">

<div class="card-body">

<div class="table-responsive">

<table class="table table-hover">

<thead>

<tr>

<th>ID</th>

<th>Owner</th>

<th>Action</th>

<th>Entity</th>

<th>Description</th>

<th>IP</th>

<th>Date</th>

</tr>

</thead>

<tbody>

<?php foreach ($logs as $log): ?>

<tr>

<td>

<?= (int)$log['log_id'] ?>

</td>

<td>

<?= htmlspecialchars(
    $log['username'] ?? 'System'
) ?>

</td>

<td>

<span class="badge badge-primary">

<?= htmlspecialchars(
    $log['action']
) ?>

</span>

</td>

<td>

<?= htmlspecialchars(
    $log['entity_type'] ?? '—'
) ?>

#

<?= htmlspecialchars(
    (string)($log['entity_id'] ?? '—')
) ?>

</td>

<td>

<?= htmlspecialchars(
    $log['description'] ?? ''
) ?>

</td>

<td>

<?= htmlspecialchars(
    $log['ip_address'] ?? '—'
) ?>

</td>

<td>

<?= htmlspecialchars(
    $log['created_at']
) ?>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

</body>

</html>