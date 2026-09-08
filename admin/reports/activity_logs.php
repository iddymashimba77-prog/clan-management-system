<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$pdo = $database->connect();

$totalLogs = (int)$pdo->query("
    SELECT COUNT(*)
    FROM activity_logs
")->fetchColumn();

$logs = $pdo->query("
    SELECT
        al.*,
        CONCAT(
            COALESCE(u.first_name,''),' ',
            COALESCE(u.middle_name,''),' ',
            COALESCE(u.last_name,'')
        ) AS user_name
    FROM activity_logs al
    LEFT JOIN users u
        ON u.user_id = al.user_id
    ORDER BY al.created_at DESC
    LIMIT 1000
")->fetchAll(PDO::FETCH_ASSOC);

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/admin.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">
<section class="content">
<div class="container-fluid clan-page-content">

<div class="card clan-page-card">

<div class="card-header d-flex justify-content-between">

<h4 class="mb-0">
<i class="fas fa-history mr-2 text-danger"></i>
Activity Logs Report
</h4>

<a href="export.php?report=activity_logs"
class="btn btn-success btn-sm">
<i class="fas fa-download mr-1"></i>
Export
</a>

</div>

<div class="card-body">

<div class="alert alert-info">
<strong><?= $totalLogs ?></strong>
total activity records.
</div>

<div class="table-responsive">

<table class="table table-bordered table-hover table-sm">

<thead>
<tr>
<th>#</th>
<th><?= __('user') ?></th>
<th><?= __('action') ?></th>
<th><?= __('table') ?></th>
<th>Record ID</th>
<th>IP Address</th>
<th><?= __('date') ?></th>
</tr>
</thead>

<tbody>

<?php foreach ($logs as $i => $log): ?>

<tr>

<td><?= $i + 1 ?></td>

<td><?= htmlspecialchars(trim($log['user_name'] ?? 'System')) ?></td>

<td><?= htmlspecialchars($log['action']) ?></td>

<td><?= htmlspecialchars($log['table_name'] ?? '-') ?></td>

<td><?= htmlspecialchars($log['record_id'] ?? '-') ?></td>

<td><?= htmlspecialchars($log['ip_address'] ?? '-') ?></td>

<td><?= htmlspecialchars($log['created_at']) ?></td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>
</div>

</div>
</section>
</div>

<?php require_once '../../includes/dashboard/footer.php'; ?>