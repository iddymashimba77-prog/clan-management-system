<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->query("
    SELECT
        c.*,
        u.first_name,
        u.last_name
    FROM contributions c
    LEFT JOIN users u
        ON u.user_id = c.created_by_user_id
    ORDER BY c.contribution_id DESC
");

$contributions = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/administrator.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">

<div class="card clan-page-card">

<div class="card-header">

<h3 class="card-title">
<i class="fas fa-hand-holding-usd mr-2"></i>
<?= __('all_contributions') ?>
</h3>

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-striped">

<thead>

<tr>
<th>ID</th>
<th><?= __('title') ?></th>
<th><?= __('target') ?></th>
<th><?= __('collected') ?></th>
<th><?= __('progress') ?></th>
<th><?= __('status') ?></th>
<th><?= __('start') ?></th>
<th><?= __('end') ?></th>
<th><?= __('actions') ?></th>
</tr>

</thead>

<tbody>

<?php foreach ($contributions as $row): ?>

<?php

$target = (float)$row['target_amount'];
$current = (float)$row['current_amount'];

$progress =
    $target > 0
    ? min(100, ($current / $target) * 100)
    : 0;

?>

<tr>

<td><?= (int)$row['contribution_id']; ?></td>

<td>
<?= htmlspecialchars($row['title']); ?>
</td>

<td>
<?= number_format($target, 2); ?>
</td>

<td>
<?= number_format($current, 2); ?>
</td>

<td style="min-width:150px;">

<div class="progress">

<div
class="progress-bar bg-success"
style="width:<?= $progress ?>%"
>

<?= number_format($progress, 1); ?>%

</div>

</div>

</td>

<td>

<span class="badge badge-<?=

$row['status'] === 'active'
? 'success'
: 'secondary'

?>">

<?= ucfirst($row['status']); ?>

</span>

</td>

<td>
<?= htmlspecialchars($row['start_date'] ?? '-'); ?>
</td>

<td>
<?= htmlspecialchars($row['end_date'] ?? '-'); ?>
</td>

<td>

<a
href="contribution_view.php?id=<?= (int)$row['contribution_id']; ?>"
class="btn btn-info btn-sm"
>

<i class="fas fa-eye"></i>

</a>

</td>

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

<?php

require_once '../../includes/dashboard/footer.php';

?>