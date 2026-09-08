<?php

require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/language.php';
require_once '../config/auth.php';

requireLogin();
requireMember();

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

define('USER_ROLE','Member');

$user_id=$_SESSION['user_id'];

// Get member count
$memberCount=$pdo->prepare("SELECT COUNT(*) FROM users WHERE clan_id=? AND is_deleted=0");
$memberCount->execute([$clanId]);
$memberCount=$memberCount->fetchColumn();

// Get family events count
$eventsCount=$pdo->prepare("SELECT COUNT(*) FROM events WHERE clan_id=?");
$eventsCount->execute([$clanId]);
$eventsCount=$eventsCount->fetchColumn();

// Get active contributions count
$activeContributions=$pdo->prepare("SELECT COUNT(*) FROM event_contributions WHERE clan_id=? AND status='Open'");
$activeContributions->execute([$clanId]);
$activeContributions=$activeContributions->fetchColumn();

$totalPayments=$pdo->prepare("
SELECT COUNT(*)
FROM contribution_payments
WHERE member_user_id=?
AND clan_id=?
");
$totalPayments->execute([$user_id, $clanId]);
$totalPayments=$totalPayments->fetchColumn();

$totalGallery=$pdo->prepare("
SELECT COUNT(*)
FROM gallery
WHERE user_id=?
AND clan_id=?
");

$totalGallery->execute([$user_id, $clanId]);
$totalGallery=$totalGallery->fetchColumn();

$myWalletsCount=$pdo->prepare("
SELECT COUNT(*)
FROM family_wallet_members
WHERE user_id=?
AND clan_id=?
");
$myWalletsCount->execute([$user_id, $clanId]);
$myWalletsCount=(int)($myWalletsCount->fetchColumn() ?: 0);


require_once '../includes/dashboard/header.php';
require_once '../includes/dashboard/sidebar.php';
require_once '../includes/dashboard/navbar.php';


?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content" style="max-width: 1200px; margin: 0 auto;">

<div class="card">

<div class="row">

<div class="col-lg-3">

<div class="small-box bg-info">

<div class="inner">

<h3><?= $memberCount ?></h3>

<p><?= __('members') ?></p>

</div>

<div class="icon">

<i class="fas fa-users"></i>

</div>

</div>

</div>

<div class="col-lg-3">

<div class="small-box bg-success">

<div class="inner">

<h3><?= $eventsCount ?></h3>

<p><?= __('family_events') ?></p>

</div>

<div class="icon">

<i class="fas fa-calendar-alt"></i>

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

<div class="small-box bg-primary">

<div class="inner">

<h3><?= $totalPayments ?></h3>

<p><?= __('my_contributions') ?></p>

</div>

<div class="icon">

<i class="fas fa-receipt"></i>

</div>

</div>

</div>

<div class="col-lg-3">

<div class="small-box bg-secondary">

<div class="inner">

<h3><?= $myWalletsCount ?></h3>

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

<?= __('quick_actions') ?>

</h3>

</div>

<div class="card-body">

<a href="<?= BASE_URL ?>member/contributions/index.php"
class="btn btn-primary">

<?= __('pay_contribution') ?>

</a>

<a href="<?= BASE_URL ?>gallery/index.php"
class="btn btn-success">

<?= __('my_gallery') ?>

</a>

<a href="<?= BASE_URL ?>profile/index.php"
class="btn btn-warning">

<?= __('my_profile') ?>

</a>

</div>

</div>

</div>

</section>

</div>

<?php
require_once '../includes/dashboard/footer.php';