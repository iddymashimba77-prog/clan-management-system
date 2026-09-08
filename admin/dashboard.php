<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| ADMIN DASHBOARD
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/language.php';
require_once __DIR__ . '/../config/auth.php';

requireAdministrator();
/*
|--------------------------------------------------------------------------
| SESSION / LOGIN
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

requireLogin();


/*
|--------------------------------------------------------------------------
| DATABASE
|--------------------------------------------------------------------------
| database.php yako tayari inatengeneza $pdo.
| Hivyo hatutengenezi connection nyingine.
|--------------------------------------------------------------------------
*/

if (!isset($pdo) || !($pdo instanceof PDO)) {
    die('Database connection is not available.');
}


/*
|--------------------------------------------------------------------------
| CHECK CURRENT USER
|--------------------------------------------------------------------------
*/

$userId = (int)($_SESSION['user_id'] ?? 0);

if ($userId <= 0) {
    header('Location: ../login.php');
    exit;
}

$clanId = getCurrentClanId();


/*
|--------------------------------------------------------------------------
| GET USER + ROLE
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        u.user_id,
        u.username,
        u.role_id,
        u.account_status,
        r.role_name
    FROM users u
    LEFT JOIN roles r
        ON r.role_id = u.role_id
    WHERE u.user_id = ?
      AND u.is_deleted = 0
      AND u.clan_id = ?
    LIMIT 1
");

$stmt->execute([$userId, $clanId]);

$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$currentUser) {
    session_destroy();

    header('Location: ../login.php');
    exit;
}





/*
|--------------------------------------------------------------------------
| ADMIN SIDEBAR
|--------------------------------------------------------------------------
| Administrator sidebar ndiyo itumike kila ukurasa wa admin.
|--------------------------------------------------------------------------
*/

$adminSidebar = __DIR__ . '/../includes/dashboard/sidebars/administrator.php';


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/dashboard/header.php';


/*
|--------------------------------------------------------------------------
| SIDEBAR
|--------------------------------------------------------------------------
*/

if (file_exists($adminSidebar)) {

    require_once $adminSidebar;

} else {

    /*
     * Fallback kama administrator.php haipo.
     * Hii inazuia page kuvunjika kwa sababu ya missing sidebar.
     */

    $defaultSidebar = __DIR__ . '/../includes/dashboard/sidebar.php';

    if (file_exists($defaultSidebar)) {
        require_once $defaultSidebar;
    }
}


/*
|--------------------------------------------------------------------------
| NAVBAR
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/dashboard/navbar.php';


/*
|--------------------------------------------------------------------------
| BASIC STATISTICS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM users
    WHERE is_deleted = 0
      AND clan_id = ?
");
$stmt->execute([$clanId]);
$totalMembers = (int)$stmt->fetchColumn();


$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM users
    WHERE is_deleted = 0
      AND account_status = 'active'
      AND clan_id = ?
");
$stmt->execute([$clanId]);
$activeMembers = (int)$stmt->fetchColumn();


$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM users
    WHERE is_deleted = 0
      AND gender = 'Male'
      AND clan_id = ?
");
$stmt->execute([$clanId]);
$maleMembers = (int)$stmt->fetchColumn();


$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM users
    WHERE is_deleted = 0
      AND gender = 'Female'
      AND clan_id = ?
");
$stmt->execute([$clanId]);
$femaleMembers = (int)$stmt->fetchColumn();


$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM users
    WHERE is_deleted = 0
      AND is_deceased = 1
      AND clan_id = ?
");
$stmt->execute([$clanId]);
$deceasedMembers = (int)$stmt->fetchColumn();


$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM family_wallets
    WHERE clan_id = ?
");
$stmt->execute([$clanId]);
$totalWallets = (int)$stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| ADMIN DASHBOARD
|--------------------------------------------------------------------------
*/

?>

<div class="content-wrapper">

    <section class="content">

        <div class="container-fluid clan-page-content">


            <!-- =====================================================
                 PAGE HEADER
            ====================================================== -->

            <div class="card clan-page-card">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center flex-wrap">

                        <div>

                            <h3 class="mb-1">

                                <i class="fas fa-tachometer-alt mr-2 text-primary"></i>

                                <?= htmlspecialchars(
                                    function_exists('__')
                                        ? __('administrator_dashboard')
                                        : 'Administrator Dashboard'
                                ) ?>

                            </h3>

                            <p class="text-muted mb-0">

                                <?= htmlspecialchars(
                                    function_exists('__')
                                        ? __('admin_dashboard_description')
                                        : 'Manage and monitor the Clan Management System.'
                                ) ?>

                            </p>

                        </div>


                        <div class="mt-2 mt-md-0">

                            <span class="badge badge-primary p-2">

                                <i class="fas fa-user-shield mr-1"></i>

                                Administrator

                            </span>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =====================================================
                 MEMBER STATISTICS
            ====================================================== -->

            <div class="row">


                <!-- TOTAL MEMBERS -->

                <div class="col-lg-3 col-md-6">

                    <div class="small-box bg-info">

                        <div class="inner">

                            <h3><?= $totalMembers ?></h3>

                            <p>Total Members</p>

                        </div>

                        <div class="icon">

                            <i class="fas fa-users"></i>

                        </div>

                        <a
                            href="/Clan_Management_System/admin/members/index.php"
                            class="small-box-footer"
                        >

                            Manage Members

                            <i class="fas fa-arrow-circle-right"></i>

                        </a>

                    </div>

                </div>


                <!-- ACTIVE MEMBERS -->

                <div class="col-lg-3 col-md-6">

                    <div class="small-box bg-success">

                        <div class="inner">

                            <h3><?= $activeMembers ?></h3>

                            <p>Active Members</p>

                        </div>

                        <div class="icon">

                            <i class="fas fa-user-check"></i>

                        </div>

                        <a
                            href="/Clan_Management_System/admin/members/index.php"
                            class="small-box-footer"
                        >

                            View Members

                            <i class="fas fa-arrow-circle-right"></i>

                        </a>

                    </div>

                </div>


                <!-- MALE MEMBERS -->

                <div class="col-lg-3 col-md-6">

                    <div class="small-box bg-primary">

                        <div class="inner">

                            <h3><?= $maleMembers ?></h3>

                            <p>Male Members</p>

                        </div>

                        <div class="icon">

                            <i class="fas fa-male"></i>

                        </div>

                        <a
                            href="/Clan_Management_System/admin/members/index.php?gender=Male"
                            class="small-box-footer"
                        >

                            View

                            <i class="fas fa-arrow-circle-right"></i>

                        </a>

                    </div>

                </div>


                <!-- FEMALE MEMBERS -->

                <div class="col-lg-3 col-md-6">

                    <div class="small-box bg-danger">

                        <div class="inner">

                            <h3><?= $femaleMembers ?></h3>

                            <p>Female Members</p>

                        </div>

                        <div class="icon">

                            <i class="fas fa-female"></i>

                        </div>

                        <a
                            href="/Clan_Management_System/admin/members/index.php?gender=Female"
                            class="small-box-footer"
                        >

                            View

                            <i class="fas fa-arrow-circle-right"></i>

                        </a>

                    </div>

                </div>


                <!-- WALLETS -->

                <div class="col-lg-3 col-md-6">

                    <div class="small-box bg-warning">

                        <div class="inner">

                            <h3><?= $totalWallets ?></h3>

                            <p>Wallets</p>

                        </div>

                        <div class="icon">

                            <i class="fas fa-wallet"></i>

                        </div>

                        <a
                            href="/Clan_Management_System/admin/wallets/index.php"
                            class="small-box-footer"
                        >

                            Manage Wallets

                            <i class="fas fa-arrow-circle-right"></i>

                        </a>

                    </div>

                </div>

            </div>


            <!-- =====================================================
                 QUICK MANAGEMENT
            ====================================================== -->

            <div class="card clan-page-card">

                <div class="card-header">

                    <h3 class="card-title">

                        <i class="fas fa-bolt mr-2 text-warning"></i>

                        Quick Management

                    </h3>

                </div>


                <div class="card-body">

                    <div class="row">


                        <!-- MEMBERS -->

                        <div class="col-md-3 col-sm-6 mb-3">

                            <a
                                href="/Clan_Management_System/admin/members/index.php"
                                class="btn btn-outline-primary btn-block p-3"
                            >

                                <i class="fas fa-users fa-lg mr-2"></i>

                                Members

                            </a>

                        </div>


                        <!-- FAMILY TREE -->

                        <div class="col-md-3 col-sm-6 mb-3">

                            <a
                                href="/Clan_Management_System/admin/family_tree/index.php"
                                class="btn btn-outline-success btn-block p-3"
                            >

                                <i class="fas fa-sitemap fa-lg mr-2"></i>

                                Family Tree

                            </a>

                        </div>


                        <!-- CONTRIBUTIONS -->

                        <div class="col-md-3 col-sm-6 mb-3">

                            <a
                                href="/Clan_Management_System/admin/contributions/index.php"
                                class="btn btn-outline-warning btn-block p-3"
                            >

                                <i class="fas fa-hand-holding-usd fa-lg mr-2"></i>

                                Contributions

                            </a>

                        </div>


                        <!-- WALLETS -->

                        <div class="col-md-3 col-sm-6 mb-3">

                            <a
                                href="/Clan_Management_System/admin/wallets/index.php"
                                class="btn btn-outline-info btn-block p-3"
                            >

                                <i class="fas fa-wallet fa-lg mr-2"></i>

                                Wallets

                            </a>

                        </div>


                    </div>

                </div>

            </div>


            <!-- =====================================================
                 MEMBER SUMMARY
            ====================================================== -->

            <div class="card clan-page-card">

                <div class="card-header">

                    <h3 class="card-title">

                        <i class="fas fa-chart-pie mr-2 text-primary"></i>

                        Member Summary

                    </h3>

                </div>


                <div class="card-body">

                    <div class="row">


                        <div class="col-md-4 text-center">

                            <h2 class="text-primary">

                                <?= $totalMembers ?>

                            </h2>

                            <p class="text-muted">

                                Total Members

                            </p>

                        </div>


                        <div class="col-md-4 text-center">

                            <h2 class="text-success">

                                <?= $activeMembers ?>

                            </h2>

                            <p class="text-muted">

                                Active Members

                            </p>

                        </div>


                        <div class="col-md-4 text-center">

                            <h2 class="text-secondary">

                                <?= $deceasedMembers ?>

                            </h2>

                            <p class="text-muted">

                                Deceased Members

                            </p>

                        </div>


                    </div>

                </div>

            </div>


        </div>

    </section>

</div>


<?php

/*
|--------------------------------------------------------------------------
| FOOTER
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/dashboard/footer.php';

?>