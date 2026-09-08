<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';
require_once '../../config/tenant.php';
require_once '../../config/permissions.php';

requireLogin();
requireTenantContext();

$clanId = (int) getCurrentClanId();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = $database->connect();

$userId = $_SESSION['user_id'] ?? 0;

/*
|--------------------------------------------------------------------------
| Check admin
|--------------------------------------------------------------------------
*/

if (!isAdministrator()) {
    http_response_code(403);
    exit('Access denied.');
}


/*
|--------------------------------------------------------------------------
| Database information
|--------------------------------------------------------------------------
*/

$dbName = $pdo->query("SELECT DATABASE()")->fetchColumn() ?: '';

$tableCount = 0;
$memberCount = 0;
$logCount = 0;

try {

    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM information_schema.tables
        WHERE table_schema = DATABASE()
    ");

    $tableCount = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM users
        WHERE is_deleted = 0
          AND clan_id = ?
    ");

    $stmt->execute([$clanId]);

    $memberCount = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM activity_logs
        WHERE clan_id = ?
    ");

    $stmt->execute([$clanId]);

    $logCount = (int)$stmt->fetchColumn();

} catch (PDOException $e) {
    // Keep dashboard alive if one statistic fails.
}


/*
|--------------------------------------------------------------------------
| Recent activity
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        al.action,
        al.description,
        al.created_at,
        u.first_name,
        u.middle_name,
        u.last_name
    FROM activity_logs al
    LEFT JOIN users u ON u.user_id = al.user_id
    WHERE al.clan_id = ?
    ORDER BY al.created_at DESC
    LIMIT 10
");

$stmt->execute([$clanId]);

$recentLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);


require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/admin.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h3 class="mb-1">
            <i class="fas fa-cogs text-primary mr-2"></i>
            <?= __('system') ?>
        </h3>

        <p class="text-muted mb-0">
            Manage system configuration, database protection and system maintenance.
        </p>
    </div>

</div>


<!-- SYSTEM CARDS -->

<div class="row">

    <div class="col-lg-3 col-md-6 mb-4">

        <div class="card clan-page-card h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>
                        <small class="text-muted">
                            Database Tables
                        </small>

                        <h3 class="mt-2 mb-0">
                            <?= $tableCount ?>
                        </h3>
                    </div>

                    <div class="text-primary">
                        <i class="fas fa-database fa-2x"></i>
                    </div>

                </div>

            </div>

        </div>

    </div>


    <div class="col-lg-3 col-md-6 mb-4">

        <div class="card clan-page-card h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>

                        <small class="text-muted">
                            Active Members
                        </small>

                        <h3 class="mt-2 mb-0">
                            <?= $memberCount ?>
                        </h3>

                    </div>

                    <div class="text-success">
                        <i class="fas fa-users fa-2x"></i>
                    </div>

                </div>

            </div>

        </div>

    </div>


    <div class="col-lg-3 col-md-6 mb-4">

        <div class="card clan-page-card h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>

                        <small class="text-muted">
                            Activity Logs
                        </small>

                        <h3 class="mt-2 mb-0">
                            <?= $logCount ?>
                        </h3>

                    </div>

                    <div class="text-warning">
                        <i class="fas fa-history fa-2x"></i>
                    </div>

                </div>

            </div>

        </div>

    </div>


    <div class="col-lg-3 col-md-6 mb-4">

        <div class="card clan-page-card h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>

                        <small class="text-muted">
                            System Status
                        </small>

                        <h3 class="mt-2 mb-0 text-success">
                            Online
                        </h3>

                    </div>

                    <div class="text-success">
                        <i class="fas fa-server fa-2x"></i>
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<!-- SYSTEM MANAGEMENT -->

<div class="row">

    <div class="col-lg-4 col-md-6 mb-4">

        <div class="card clan-page-card h-100">

            <div class="card-body">

                <i class="fas fa-sliders-h fa-2x text-primary mb-3"></i>

                <h5>
                    System Settings
                </h5>

                <p class="text-muted">
                    Manage system name, contact information and general configuration.
                </p>

                <a href="settings.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-cog mr-1"></i>
                    Manage Settings
                </a>

            </div>

        </div>

    </div>


    <div class="col-lg-4 col-md-6 mb-4">

        <div class="card clan-page-card h-100">

            <div class="card-body">

                <i class="fas fa-download fa-2x text-success mb-3"></i>

                <h5>
                    Database Backup
                </h5>

                <p class="text-muted">
                    Create a secure database backup before major system changes.
                </p>

                <a href="backup.php" class="btn btn-success btn-sm">
                    <i class="fas fa-database mr-1"></i>
                    Backup Database
                </a>

            </div>

        </div>

    </div>


    <div class="col-lg-4 col-md-6 mb-4">

        <div class="card clan-page-card h-100">

            <div class="card-body">

                <i class="fas fa-upload fa-2x text-warning mb-3"></i>

                <h5>
                    Restore Database
                </h5>

                <p class="text-muted">
                    Restore the database from a verified backup file.
                </p>

                <a href="restore.php" class="btn btn-warning btn-sm">
                    <i class="fas fa-upload mr-1"></i>
                    Restore
                </a>

            </div>

        </div>

    </div>


    <div class="col-lg-4 col-md-6 mb-4">

        <div class="card clan-page-card h-100">

            <div class="card-body">

                <i class="fas fa-broom fa-2x text-info mb-3"></i>

                <h5>
                    Clear Cache
                </h5>

                <p class="text-muted">
                    Clear temporary system files and cached data.
                </p>

                <a href="clear_cache.php"
                   class="btn btn-info btn-sm"
                   onclick="return confirm('Clear system cache?');">

                    <i class="fas fa-trash-alt mr-1"></i>
                    Clear Cache

                </a>

            </div>

        </div>

    </div>


    <div class="col-lg-4 col-md-6 mb-4">

        <div class="card clan-page-card h-100">

            <div class="card-body">

                <i class="fas fa-history fa-2x text-secondary mb-3"></i>

                <h5>
                    System Logs
                </h5>

                <p class="text-muted">
                    Review important actions performed inside the system.
                </p>

                <a href="system_logs.php"
                   class="btn btn-secondary btn-sm">

                    <i class="fas fa-list mr-1"></i>
                    View Logs

                </a>

            </div>

        </div>

    </div>

</div>


<!-- RECENT LOGS -->

<div class="card clan-page-card">

    <div class="card-header">

        <h5 class="mb-0">
            <i class="fas fa-history mr-2 text-primary"></i>
            Recent System Activity
        </h5>

    </div>

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-hover mb-0">

                <thead>
                    <tr>
                        <th><?= __('action') ?></th>
                        <th><?= __('user') ?></th>
                        <th><?= __('description') ?></th>
                        <th><?= __('date') ?></th>
                    </tr>
                </thead>

                <tbody>

                <?php if ($recentLogs): ?>

                    <?php foreach ($recentLogs as $log): ?>

                        <tr>

                            <td>
                                <span class="badge badge-primary">
                                    <?= htmlspecialchars($log['action']) ?>
                                </span>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    trim(
                                        $log['first_name'] . ' ' .
                                        ($log['middle_name'] ?? '') . ' ' .
                                        $log['last_name']
                                    )
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($log['description'] ?? '') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($log['created_at']) ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            No system activity recorded.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</div>

</section>

</div>

<?php require_once '../../includes/dashboard/footer.php'; ?>