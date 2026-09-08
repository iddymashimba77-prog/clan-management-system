<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireClanUser();

require_once '../../config/tenant.php';
$clanId = getCurrentClanId();

// Get database name from session first
$databaseName = $_SESSION['database_name'] ?? null;

// If session doesn't have database name, try to get from clan_owner
if ($databaseName === null || $databaseName === '') {
    try {
        $ownerPdo = new PDO(
            'mysql:unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=clan_owner;charset=utf8mb4',
            'root',
            '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
        $stmt = $ownerPdo->prepare("SELECT database_name FROM owner_installations WHERE clan_id = ? LIMIT 1");
        $stmt->execute([$clanId]);
        $databaseName = $stmt->fetchColumn();
        
        // Set it in session for future requests
        if ($databaseName && session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['database_name'] = $databaseName;
        }
    } catch (Exception $e) {
        // Fallback to default
        $databaseName = null;
    }
}

try {
    $database = new Database($databaseName);
    $pdo = $database->connect();
} catch (Exception $e) {
    die(__('db_connection_failed') . " " . $e->getMessage());
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT

        ec.contribution_id,

        e.title AS event_name,

        ec.title,

        ec.required_amount,

        ec.deadline,

        ec.status,

        COALESCE((
            SELECT SUM(cp.amount)
            FROM contribution_payments cp
            WHERE cp.contribution_id = ec.contribution_id
            AND cp.clan_id = ?
            AND cp.payment_status = 'approved'
        ), 0) AS amount_paid

    FROM event_contributions ec

    INNER JOIN events e
        ON ec.event_id = e.event_id

    WHERE ec.status = 'Open'
    AND ec.clan_id = ?

    ORDER BY ec.deadline ASC
");

$stmt->execute([$clanId, $clanId]);

$contributions = $stmt->fetchAll(PDO::FETCH_ASSOC);




if(!defined('USER_ROLE')){
    define('USER_ROLE','Member');
}

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/'.strtolower(USER_ROLE).'.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content" style="max-width: 1200px; margin: 0 auto;">

<div class="card">

<div class="card-header">

<h3 class="card-title">

<?= __('available_contributions') ?>

</h3>

</div>

<div class="card-body">

<table class="table table-bordered table-striped">

<thead>

<tr>

<th><?= __('event') ?></th>

<th><?= __('contribution') ?></th>

<th><?= __('required') ?></th>

<th><?= __('paid') ?></th>

<th><?= __('remaining') ?></th>

<th><?= __('progress') ?></th>

<th><?= __('deadline') ?></th>

<th><?= __('status') ?></th>

<th><?= __('action') ?></th>

</tr>

</thead>

<tbody>

<?php foreach($contributions as $row): ?>

<tr>

<td><?= htmlspecialchars($row['event_name']) ?></td>

<td><?= htmlspecialchars($row['title']) ?></td>

<?php

$remaining = $row['required_amount'] - $row['amount_paid'];

$progress = 0;

if($row['required_amount']>0){

    $progress = ($row['amount_paid']/$row['required_amount'])*100;

}

?>

<td>

TZS <?= number_format($row['required_amount'],2) ?>

</td>

<td>

TZS <?= number_format($row['amount_paid'],2) ?>

</td>

<td>

TZS <?= number_format($remaining,2) ?>

<td style="width:220px;">

    <div class="progress" style="height: 22px;">

        <div
            class="progress-bar bg-success"
            role="progressbar"
            style="width:<?= min(max($progress, 0), 100) ?>%;"
            aria-valuenow="<?= number_format($progress, 2) ?>"
            aria-valuemin="0"
            aria-valuemax="100"
        >

            <?= number_format($progress, 2) ?>%

        </div>

    </div>

</td>

<td><?= $row['deadline'] ?></td>

<td>

<span class="badge badge-success">

<?= __(strtolower($row['status'])) ?>

</span>

</td>

<td>

<a
href="pay.php?id=<?= $row['contribution_id'] ?>"
class="btn btn-primary btn-sm">

<?= __('pay') ?>

</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

</div>

</section>

</div>

<?php
require_once '../../includes/dashboard/footer.php';