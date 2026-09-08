<?php

require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/language.php';
require_once '../config/auth.php';

requireLogin();
requireChairperson();

$database = new Database();
$pdo = $database->connect();

$clanId = getCurrentClanId();

$stmt = $pdo->prepare("
SELECT COUNT(*)
FROM users
WHERE is_deleted=0
AND clan_id = ?
");
$stmt->execute([$clanId]);
$totalMembers = $stmt->fetchColumn();

$stmt = $pdo->prepare("
SELECT COUNT(*)
FROM users
WHERE father_user_id IS NULL
AND mother_user_id IS NULL
AND is_deleted=0
AND clan_id = ?
");
$stmt->execute([$clanId]);
$totalFamilies = $stmt->fetchColumn();

$stmt = $pdo->prepare("
SELECT COUNT(*)
FROM events
WHERE clan_id = ?
");
$stmt->execute([$clanId]);
$totalEvents = $stmt->fetchColumn();

$stmt = $pdo->prepare("
SELECT COUNT(*)
FROM gallery
WHERE clan_id = ?
");
$stmt->execute([$clanId]);
$totalGallery = $stmt->fetchColumn();

$stmt = $pdo->prepare("
SELECT COUNT(*)
FROM event_contributions
WHERE clan_id = ?
AND status = 'Open'
");
$stmt->execute([$clanId]);
$activeContributions = $stmt->fetchColumn();

$stmt = $pdo->prepare("
SELECT COUNT(*)
FROM users
WHERE is_deleted=0
AND is_deceased=1
AND clan_id = ?
");
$stmt->execute([$clanId]);
$deceasedMembers = $stmt->fetchColumn();

$stmt = $pdo->prepare("
SELECT COUNT(*)
FROM family_wallets
WHERE clan_id = ?
");
$stmt->execute([$clanId]);
$totalWallets = $stmt->fetchColumn();

define('USER_ROLE','Chairperson');

require_once '../includes/dashboard/header.php';
require_once '../includes/dashboard/sidebar.php';
require_once '../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid" style="max-width: 1200px; margin: 0 auto;">

<div class="row">

<div class="col-lg-3 col-6">

<div class="small-box bg-primary">

<div class="inner">

<h3><?= $totalMembers ?></h3>

<p><?= __('total_members') ?></p>

</div>

<div class="icon">

<i class="fas fa-users"></i>

</div>

</div>

</div>

<div class="col-lg-3 col-6">

<div class="small-box bg-success">

<div class="inner">

<h3><?= $totalFamilies ?></h3>

<p><?= __('families') ?></p>

</div>

<div class="icon">

<i class="fas fa-sitemap"></i>

</div>

</div>

</div>

<div class="col-lg-3 col-6">

<div class="small-box bg-warning">

<div class="inner">

<h3><?= $totalEvents ?></h3>

<p><?= __('events') ?></p>

</div>

<div class="icon">

<i class="fas fa-calendar"></i>

</div>

</div>

</div>


<div class="col-lg-3 col-6">

<div class="small-box bg-danger">

<div class="inner">

<h3><?= $totalGallery ?></h3>

<p><?= __('gallery_photos') ?></p>

</div>

<div class="icon">

<i class="fas fa-images"></i>

</div>

</div>

</div>

<div class="col-lg-3 col-6">

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

<div class="col-lg-3 col-6">

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

<div class="col-lg-3 col-6">

<div class="small-box bg-info">

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

<?= __('chairperson_quick_actions') ?>

</h3>

</div>

</a>

<div class="card-body">

<a href="<?= BASE_URL ?>chairperson/member/index.php"
class="btn btn-primary">

<i class="fas fa-user-plus"></i>

<?= __('all_members') ?>

</a>

<a href="<?= BASE_URL ?>chairperson/member/create.php"
class="btn btn-success">

<i class="fas fa-user-plus"></i>

<?= __('add_member') ?>

</a>

<a href="<?= BASE_URL ?>chairperson/events/index.php"
class="btn btn-primary">

<i class="fas fa-calendar-plus"></i>

<?= __('manage_events') ?>

</a>

<a href="<?= BASE_URL ?>gallery/index.php"

class="btn btn-success">

<i class="fas fa-images"></i>

<?= __('manage_gallery') ?>

</a>

<a href="<?= BASE_URL ?>family_tree/index.php"
class="btn btn-info">

<i class="fas fa-sitemap"></i>

<?= __('family_tree') ?>

</a>

</div>

</div>

</div>

</section>

<?php
require_once '../includes/dashboard/footer.php';