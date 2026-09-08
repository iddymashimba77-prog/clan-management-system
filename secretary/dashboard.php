<?php

require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/language.php';
require_once '../config/auth.php';

requireLogin();
requireSecretary();

require_once '../config/tenant.php';
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

define('USER_ROLE','Secretary');

$stmt=$pdo->prepare("SELECT COUNT(*) FROM users WHERE is_deleted=0 AND clan_id=?");
$stmt->execute([$clanId]);
$totalMembers=$stmt->fetchColumn();

$stmt=$pdo->prepare("SELECT COUNT(*) FROM events WHERE clan_id=?");
$stmt->execute([$clanId]);
$totalEvents=$stmt->fetchColumn();

$stmt=$pdo->prepare("SELECT COUNT(*) FROM event_contributions WHERE clan_id=? AND status='Open'");
$stmt->execute([$clanId]);
$activeContributions=$stmt->fetchColumn();

$stmt=$pdo->prepare("SELECT COUNT(*) FROM users WHERE is_deleted=0 AND is_deceased=1 AND clan_id=?");
$stmt->execute([$clanId]);
$deceasedMembers=$stmt->fetchColumn();

$stmt=$pdo->prepare("SELECT COUNT(*) FROM family_wallets WHERE clan_id=?");
$stmt->execute([$clanId]);
$totalWallets=$stmt->fetchColumn();

require_once '../includes/dashboard/header.php';
require_once '../includes/dashboard/sidebar.php';
require_once '../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid" style="max-width: 1200px; margin: 0 auto;">

<div class="row">

<div class="col-lg-3">

<div class="small-box bg-info">

<div class="inner">

<h3><?= $totalMembers ?></h3>

<p><?= __('total_members') ?></p>

</div>

<div class="icon">

<i class="fas fa-users"></i>

</div>

</div>

</div>

<div class="col-lg-3">

<div class="small-box bg-success">

<div class="inner">

<h3><?= $totalEvents ?></h3>

<p><?= __('family_events') ?></p>

</div>

<div class="icon">

<i class="fas fa-calendar"></i>

</div>

</div>

</div>

<div class="col-lg-3">

<div class="small-box bg-warning">

<div class="inner">

<h3><?= $activeContributions ?></h3>

<p><?= __('active_contributions') ?></p>

</div>

<div class="icon">

<i class="fas fa-hand-holding-usd"></i>

</div>

</div>

</div>

<div class="col-lg-3">

<div class="small-box bg-secondary">

<div class="inner">

<h3><?= $deceasedMembers ?></h3>

<p><?= __('deceased') ?></p>

</div>

<div class="icon">

<i class="fas fa-user-tag"></i>

</div>

</div>

</div>

<div class="col-lg-3">

<div class="small-box bg-primary">

<div class="inner">

<h3><?= $totalWallets ?></h3>

<p><?= __('wallets') ?></p>

</div>

<div class="icon">

<i class="fas fa-wallet"></i>

</div>

</div>

</div>

</div>

<div class="card">

<div class="card-header">

<h3 class="card-title">

<?= __('secretary_quick_actions') ?>

</h3>

</div>

<div class="card-body">

<a href="<?= BASE_URL ?>secretary/members/index.php"

class="btn btn-primary">

<?= __('members') ?>

</a>

<a href="<?= BASE_URL ?>events/index.php"
class="btn btn-success">

<?= __('events') ?>

</a>

<a href="<?= BASE_URL ?>gallery/index.php"
class="btn btn-warning">

<?= __('gallery') ?>

</a>

</div>

</div>

</div>

</section>

<?php
require_once '../includes/dashboard/footer.php';