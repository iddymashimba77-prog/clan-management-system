<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->query("
    SELECT
        fw.*,
        u.first_name,
        u.last_name
    FROM family_wallets fw
    LEFT JOIN users u
        ON u.user_id = fw.created_by_user_id
    ORDER BY fw.wallet_id DESC
");

$wallets = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

<i class="fas fa-wallet mr-2"></i>

<?= __('wallets') ?>

</h3>

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-striped">

<thead>

<tr>

<th>ID</th>
<th><?= __('wallet') ?></th>
<th><?= __('purpose') ?></th>
<th><?= __('target') ?></th>
<th><?= __('monthly') ?></th>
<th><?= __('balance') ?></th>
<th><?= __('status') ?></th>
<th><?= __('period') ?></th>
<th><?= __('action') ?></th>

</tr>

</thead>

<tbody>

<?php foreach ($wallets as $wallet): ?>

<tr>

<td>
<?= (int)$wallet['wallet_id']; ?>
</td>

<td>

<strong>
<?= htmlspecialchars(
$wallet['wallet_name']
); ?>
</strong>

</td>

<td>
<?= htmlspecialchars(
$wallet['purpose'] ?? '-'
); ?>
</td>

<td>
<?= number_format(
(float)$wallet['target_amount'],
2
); ?>
</td>

<td>
<?= number_format(
(float)$wallet['monthly_amount'],
2
); ?>
</td>

<td>
<?= number_format(
(float)$wallet['current_balance'],
2
); ?>
</td>

<td>

<span class="badge badge-<?=

$wallet['status'] === 'active'
? 'success'
: 'secondary'

?>">

<?= ucfirst(
$wallet['status']
); ?>

</span>

</td>

<td>

<?= htmlspecialchars(
$wallet['start_date']
); ?>

-

<?= htmlspecialchars(
$wallet['end_date']
); ?>

</td>

<td>

<a
href="wallet_view.php?id=<?= (int)$wallet['wallet_id']; ?>"
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

?><?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->query("
    SELECT
        fw.*,
        u.first_name,
        u.last_name
    FROM family_wallets fw
    LEFT JOIN users u
        ON u.user_id = fw.created_by_user_id
    ORDER BY fw.wallet_id DESC
");

$wallets = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

<i class="fas fa-wallet mr-2"></i>

<?= __('wallets') ?>

</h3>

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-striped">

<thead>

<tr>

<th>ID</th>
<th><?= __('wallet') ?></th>
<th><?= __('purpose') ?></th>
<th><?= __('target') ?></th>
<th><?= __('monthly') ?></th>
<th><?= __('balance') ?></th>
<th><?= __('status') ?></th>
<th><?= __('period') ?></th>
<th><?= __('action') ?></th>

</tr>

</thead>

<tbody>

<?php foreach ($wallets as $wallet): ?>

<tr>

<td>
<?= (int)$wallet['wallet_id']; ?>
</td>

<td>

<strong>
<?= htmlspecialchars(
$wallet['wallet_name']
); ?>
</strong>

</td>

<td>
<?= htmlspecialchars(
$wallet['purpose'] ?? '-'
); ?>
</td>

<td>
<?= number_format(
(float)$wallet['target_amount'],
2
); ?>
</td>

<td>
<?= number_format(
(float)$wallet['monthly_amount'],
2
); ?>
</td>

<td>
<?= number_format(
(float)$wallet['current_balance'],
2
); ?>
</td>

<td>

<span class="badge badge-<?=

$wallet['status'] === 'active'
? 'success'
: 'secondary'

?>">

<?= ucfirst(
$wallet['status']
); ?>

</span>

</td>

<td>

<?= htmlspecialchars(
$wallet['start_date']
); ?>

-

<?= htmlspecialchars(
$wallet['end_date']
); ?>

</td>

<td>

<a
href="wallet_view.php?id=<?= (int)$wallet['wallet_id']; ?>"
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