<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$pdo = $database->connect();

/* =========================================================
   MEMBERS
========================================================= */

$totalMembers = (int)$pdo->query("
    SELECT COUNT(*)
    FROM users
    WHERE is_deleted = 0
")->fetchColumn();

$activeMembers = (int)$pdo->query("
    SELECT COUNT(*)
    FROM users
    WHERE is_deleted = 0
      AND account_status = 'active'
")->fetchColumn();

$deceasedMembers = (int)$pdo->query("
    SELECT COUNT(*)
    FROM users
    WHERE is_deleted = 0
      AND is_deceased = 1
")->fetchColumn();

/* =========================================================
   CONTRIBUTIONS
========================================================= */

$totalContributions = (int)$pdo->query("
    SELECT COUNT(*)
    FROM contributions
")->fetchColumn();

$totalContributionTarget = (float)$pdo->query("
    SELECT COALESCE(SUM(target_amount),0)
    FROM contributions
")->fetchColumn();

$totalContributionPaid = (float)$pdo->query("
    SELECT COALESCE(SUM(amount),0)
    FROM contribution_payments
    WHERE payment_status = 'approved'
")->fetchColumn();

/* =========================================================
   WALLETS
========================================================= */

$totalWallets = (int)$pdo->query("
    SELECT COUNT(*)
    FROM family_wallets
")->fetchColumn();

$totalWalletBalance = (float)$pdo->query("
    SELECT COALESCE(SUM(current_balance),0)
    FROM family_wallets
    WHERE status = 'active'
")->fetchColumn();

$totalWalletPayments = (float)$pdo->query("
    SELECT COALESCE(SUM(amount),0)
    FROM family_wallet_payments
    WHERE payment_status = 'approved'
")->fetchColumn();

/* =========================================================
   EVENTS
========================================================= */

$totalEvents = (int)$pdo->query("
    SELECT COUNT(*)
    FROM events
")->fetchColumn();

$upcomingEvents = (int)$pdo->query("
    SELECT COUNT(*)
    FROM events
    WHERE event_status = 'upcoming'
")->fetchColumn();

/* =========================================================
   ACTIVITY
========================================================= */

$totalLogs = (int)$pdo->query("
    SELECT COUNT(*)
    FROM activity_logs
")->fetchColumn();

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/admin.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">

<div class="card clan-page-card">

<div class="card-header">
    <h4 class="mb-0">
        <i class="fas fa-chart-bar mr-2 text-primary"></i>
        System Reports
    </h4>
</div>

<div class="card-body">

<div class="row">

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3><?= $totalMembers ?></h3>
                <p>Total Members</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="members.php" class="small-box-footer">
                View Report <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="small-box bg-success">
            <div class="inner">
                <h3><?= number_format($totalContributionPaid, 2) ?></h3>
                <p>Contribution Payments</p>
            </div>
            <div class="icon">
                <i class="fas fa-hand-holding-usd"></i>
            </div>
            <a href="contributions.php" class="small-box-footer">
                View Report <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?= number_format($totalWalletBalance, 2) ?></h3>
                <p>Wallet Balance</p>
            </div>
            <div class="icon">
                <i class="fas fa-wallet"></i>
            </div>
            <a href="wallets.php" class="small-box-footer">
                View Report <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3><?= $totalEvents ?></h3>
                <p>Total Events</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <a href="events.php" class="small-box-footer">
                View Report <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

</div>

<div class="row">

    <div class="col-md-4 mb-3">
        <a href="members.php" class="btn btn-outline-primary btn-block">
            <i class="fas fa-users mr-2"></i>
            Members Report
        </a>
    </div>

    <div class="col-md-4 mb-3">
        <a href="contributions.php" class="btn btn-outline-success btn-block">
            <i class="fas fa-hand-holding-usd mr-2"></i>
            Contributions Report
        </a>
    </div>

    <div class="col-md-4 mb-3">
        <a href="wallets.php" class="btn btn-outline-info btn-block">
            <i class="fas fa-wallet mr-2"></i>
            Wallets Report
        </a>
    </div>

    <div class="col-md-4 mb-3">
        <a href="events.php" class="btn btn-outline-warning btn-block">
            <i class="fas fa-calendar mr-2"></i>
            Events Report
        </a>
    </div>

    <div class="col-md-4 mb-3">
        <a href="family_tree.php" class="btn btn-outline-secondary btn-block">
            <i class="fas fa-sitemap mr-2"></i>
            Family Tree Report
        </a>
    </div>

    <div class="col-md-4 mb-3">
        <a href="finance.php" class="btn btn-outline-dark btn-block">
            <i class="fas fa-money-bill-wave mr-2"></i>
            Financial Report
        </a>
    </div>

    <div class="col-md-4 mb-3">
        <a href="activity_logs.php" class="btn btn-outline-danger btn-block">
            <i class="fas fa-history mr-2"></i>
            Activity Report
        </a>
    </div>

</div>

</div>
</div>

</div>

</section>

</div>

<?php require_once '../../includes/dashboard/footer.php'; ?>