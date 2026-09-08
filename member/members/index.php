<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireMember();

require_once '../../config/tenant.php';
$clanId = getCurrentClanId();

$databaseName = $_SESSION['database_name'] ?? null;

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

        if ($databaseName && session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['database_name'] = $databaseName;
        }
    } catch (Exception $e) {
        $databaseName = null;
    }
}

try {
    $database = new Database($databaseName);
    $pdo = $database->connect();
} catch (Exception $e) {
    die(__('db_connection_failed') . " " . $e->getMessage());
}

define('USER_ROLE','Member');

$stmt = $pdo->prepare("
SELECT
u.user_id,
u.member_code,
u.first_name,
u.middle_name,
u.last_name,
u.username,
u.account_status,
r.role_name
FROM users u
LEFT JOIN roles r
ON u.role_id = r.role_id
WHERE u.is_deleted = 0
AND u.clan_id = ?
ORDER BY u.first_name ASC
");
$stmt->execute([$clanId]);
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebar.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid" style="max-width: 1200px; margin: 0 auto;">

<div class="card">

<div class="card-header">

<h3 class="card-title">

<i class="fas fa-users mr-2"></i>

<?= __('family_members') ?>

</h3>

</div>

<div class="card-body">

<?php if(count($members) === 0): ?>

<div class="alert alert-info">

<?= __('no_members_found') ?>

</div>

<?php else: ?>

<table class="table table-bordered table-striped">

<thead>

<tr>

<th>#</th>

<th><?= __('member_code') ?></th>

<th><?= __('full_name') ?></th>

<th><?= __('role') ?></th>

<th><?= __('username') ?></th>

<th><?= __('status') ?></th>

</tr>

</thead>

<tbody>

<?php foreach($members as $i => $member): ?>

<tr>

<td><?= $i + 1 ?></td>

<td><?= htmlspecialchars($member['member_code']) ?></td>

<td><?= htmlspecialchars(trim($member['first_name'].' '.$member['middle_name'].' '.$member['last_name'])) ?></td>

<td>

<span class="badge badge-info">

<?= htmlspecialchars($member['role_name']) ?>

</span>

</td>

<td><?= htmlspecialchars($member['username']) ?></td>

<td>

<?php if($member['account_status'] === 'active'): ?>

<span class="badge badge-success">

<?= __('active') ?>

</span>

<?php else: ?>

<span class="badge badge-danger">

<?= __('inactive') ?>

</span>

<?php endif; ?>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

<?php endif; ?>

</div>

</div>

</div>

</section>

</div>

<?php
require_once '../../includes/dashboard/footer.php';
?>
