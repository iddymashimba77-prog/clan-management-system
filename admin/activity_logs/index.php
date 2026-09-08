<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

/*
|--------------------------------------------------------------------------
| SEARCH & FILTERS
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? '');
$action = trim($_GET['action'] ?? '');
$table  = trim($_GET['table_name'] ?? '');

$where = [];
$params = [];

if ($search !== '') {

    $where[] = "
        (
            al.action LIKE :search
            OR al.table_name LIKE :search
            OR al.description LIKE :search
            OR al.ip_address LIKE :search
            OR u.first_name LIKE :search
            OR u.middle_name LIKE :search
            OR u.last_name LIKE :search
            OR u.member_code LIKE :search
        )
    ";

    $params[':search'] = '%' . $search . '%';
}

if ($action !== '') {

    $where[] = "al.action = :action";

    $params[':action'] = $action;
}

if ($table !== '') {

    $where[] = "al.table_name = :table_name";

    $params[':table_name'] = $table;
}

$whereSQL = '';

if (!empty($where)) {
    $whereSQL = 'WHERE ' . implode(' AND ', $where);
}

/*
|--------------------------------------------------------------------------
| GET LOGS
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        al.*,

        u.member_code,
        u.first_name,
        u.middle_name,
        u.last_name

    FROM activity_logs al

    LEFT JOIN users u
        ON u.user_id = al.user_id

    $whereSQL

    ORDER BY al.created_at DESC, al.log_id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| FILTER OPTIONS
|--------------------------------------------------------------------------
*/

$actions = $pdo->query("
    SELECT DISTINCT action
    FROM activity_logs
    WHERE action IS NOT NULL
      AND action != ''
    ORDER BY action ASC
")->fetchAll(PDO::FETCH_COLUMN);

$tables = $pdo->query("
    SELECT DISTINCT table_name
    FROM activity_logs
    WHERE table_name IS NOT NULL
      AND table_name != ''
    ORDER BY table_name ASC
")->fetchAll(PDO::FETCH_COLUMN);

/*
|--------------------------------------------------------------------------
| STATISTICS
|--------------------------------------------------------------------------
*/

$totalLogs = (int) $pdo->query("
    SELECT COUNT(*)
    FROM activity_logs
")->fetchColumn();

$todayLogs = (int) $pdo->query("
    SELECT COUNT(*)
    FROM activity_logs
    WHERE DATE(created_at) = CURDATE()
")->fetchColumn();

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebar.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">

    <!-- HEADER -->

    <div class="card clan-page-card">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center flex-wrap">

                <div>

                    <h3 class="mb-1">

                        <i class="fas fa-history text-primary mr-2"></i>

                        <?= __('activity_logs') ?>

                    </h3>

                    <p class="text-muted mb-0">

                        Monitor important activities performed in the system.

                    </p>

                </div>

                <div class="mt-2">

                    <a
                        href="export.php?search=<?= urlencode($search) ?>&action=<?= urlencode($action) ?>&table_name=<?= urlencode($table) ?>"
                        class="btn btn-success btn-sm"
                    >

                        <i class="fas fa-file-csv mr-1"></i>

                        Export

                    </a>

                </div>

            </div>

        </div>

    </div>


    <!-- STATISTICS -->

    <div class="row">

        <div class="col-md-6">

            <div class="small-box bg-info">

                <div class="inner">

                    <h3><?= number_format($totalLogs); ?></h3>

                    <p>Total Activities</p>

                </div>

                <div class="icon">

                    <i class="fas fa-history"></i>

                </div>

            </div>

        </div>


        <div class="col-md-6">

            <div class="small-box bg-success">

                <div class="inner">

                    <h3><?= number_format($todayLogs); ?></h3>

                    <p>Activities Today</p>

                </div>

                <div class="icon">

                    <i class="fas fa-calendar-day"></i>

                </div>

            </div>

        </div>

    </div>


    <!-- FILTER -->

    <div class="card clan-page-card">

        <div class="card-header">

            <h3 class="card-title">

                <i class="fas fa-filter mr-2"></i>

                Search & Filter

            </h3>

        </div>

        <div class="card-body">

            <form method="GET">

                <div class="row">

                    <div class="col-md-4">

                        <label><?= __('search') ?></label>

                        <input
                            type="search"
                            name="search"
                            value="<?= htmlspecialchars($search); ?>"
                            class="form-control"
                            placeholder="Action, user, IP, description..."
                        >

                    </div>


                    <div class="col-md-3">

                        <label><?= __('action') ?></label>

                        <select name="action" class="form-control">

                            <option value="">All Actions</option>

                            <?php foreach ($actions as $item): ?>

                                <option
                                    value="<?= htmlspecialchars($item); ?>"
                                    <?= $action === $item ? 'selected' : ''; ?>
                                >
                                    <?= htmlspecialchars($item); ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="col-md-3">

                        <label><?= __('table') ?></label>

                        <select name="table_name" class="form-control">

                            <option value="">All Tables</option>

                            <?php foreach ($tables as $item): ?>

                                <option
                                    value="<?= htmlspecialchars($item); ?>"
                                    <?= $table === $item ? 'selected' : ''; ?>
                                >
                                    <?= htmlspecialchars($item); ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="col-md-2 d-flex align-items-end">

                        <button
                            type="submit"
                            class="btn btn-primary mr-2"
                        >

                            <i class="fas fa-search"></i>

                        </button>

                        <a
                            href="index.php"
                            class="btn btn-secondary"
                        >

                            <i class="fas fa-sync"></i>

                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- LOG TABLE -->

    <div class="card clan-page-card">

        <div class="card-header">

            <h3 class="card-title">

                System Activities

            </h3>

        </div>

        <div class="card-body">

            <?php if (!empty($logs)): ?>

                <div class="table-responsive">

                    <table class="table table-bordered table-striped">

                        <thead>

                        <tr>

                            <th>#</th>
                            <th><?= __('user') ?></th>
                            <th><?= __('action') ?></th>
                            <th><?= __('table') ?></th>
                            <th><?= __('record') ?></th>
                            <th>IP Address</th>
                            <th><?= __('date') ?></th>
                            <th width="130"><?= __('actions') ?></th>

                        </tr>

                        </thead>

                        <tbody>

                        <?php foreach ($logs as $log): ?>

                            <tr>

                                <td>
                                    <?= (int) $log['log_id']; ?>
                                </td>

                                <td>

                                    <?php if (!empty($log['user_id'])): ?>

                                        <?= htmlspecialchars(
                                            trim(
                                                $log['first_name'] . ' ' .
                                                $log['middle_name'] . ' ' .
                                                $log['last_name']
                                            )
                                        ); ?>

                                        <br>

                                        <small class="text-muted">

                                            <?= htmlspecialchars(
                                                $log['member_code'] ?? ''
                                            ); ?>

                                        </small>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            System
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <span class="badge badge-primary">

                                        <?= htmlspecialchars(
                                            $log['action']
                                        ); ?>

                                    </span>

                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $log['table_name'] ?? '-'
                                    ); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $log['record_id'] ?? '-'
                                    ); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $log['ip_address'] ?? '-'
                                    ); ?>
                                </td>

                                <td>

                                    <?= htmlspecialchars(
                                        date(
                                            'd M Y H:i',
                                            strtotime($log['created_at'])
                                        )
                                    ); ?>

                                </td>

                                <td>

                                    <a
                                        href="view.php?id=<?= (int)$log['log_id']; ?>"
                                        class="btn btn-info btn-sm"
                                        title="View"
                                    >

                                        <i class="fas fa-eye"></i>

                                    </a>

                                    <a
                                        href="delete.php?id=<?= (int)$log['log_id']; ?>"
                                        class="btn btn-danger btn-sm"
                                        title="Delete"
                                        onclick="return confirm('Delete this activity log?');"
                                    >

                                        <i class="fas fa-trash"></i>

                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="text-center py-5">

                    <i class="fas fa-history fa-3x text-muted mb-3"></i>

                    <h5><?= __('no_activity_logs_found') ?></h5>

                    <p class="text-muted mb-0">
                        There are no activities matching your search.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

</section>

</div>

<?php

require_once '../../includes/dashboard/footer.php';

?>