<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    header('Location: index.php?error=invalid_id');
    exit;
}

$log_id = (int) $_GET['id'];

$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->prepare("
    SELECT
        al.*,

        u.member_code,
        u.first_name,
        u.middle_name,
        u.last_name,
        u.phone,
        u.email

    FROM activity_logs al

    LEFT JOIN users u
        ON u.user_id = al.user_id

    WHERE al.log_id = ?

    LIMIT 1
");

$stmt->execute([$log_id]);

$log = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$log) {

    header('Location: index.php?error=log_not_found');
    exit;
}

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebar.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">

    <div class="card clan-page-card">

        <div class="card-header">

            <h3 class="card-title">

                <i class="fas fa-history mr-2"></i>

                <?= __('activity_log_details') ?>

            </h3>

            <div class="card-tools">

                <a
                    href="index.php"
                    class="btn btn-secondary btn-sm"
                >

                    <i class="fas fa-arrow-left mr-1"></i>

                    Back

                </a>

            </div>

        </div>


        <div class="card-body">

            <div class="row">

                <div class="col-md-6">

                    <table class="table table-bordered">

                        <tr>

                            <th width="40%">Log ID</th>

                            <td>
                                <?= (int)$log['log_id']; ?>
                            </td>

                        </tr>


                        <tr>

                            <th><?= __('user') ?></th>

                            <td>

                                <?php if (!empty($log['user_id'])): ?>

                                    <?= htmlspecialchars(
                                        trim(
                                            $log['first_name'] . ' ' .
                                            $log['middle_name'] . ' ' .
                                            $log['last_name']
                                        )
                                    ); ?>

                                <?php else: ?>

                                    <span class="text-muted">
                                        System
                                    </span>

                                <?php endif; ?>

                            </td>

                        </tr>


                        <tr>

                            <th>Member Code</th>

                            <td>
                                <?= htmlspecialchars(
                                    $log['member_code'] ?? '-'
                                ); ?>
                            </td>

                        </tr>


                        <tr>

                            <th><?= __('action') ?></th>

                            <td>

                                <span class="badge badge-primary">

                                    <?= htmlspecialchars(
                                        $log['action']
                                    ); ?>

                                </span>

                            </td>

                        </tr>


                        <tr>

                            <th><?= __('table') ?></th>

                            <td>
                                <?= htmlspecialchars(
                                    $log['table_name'] ?? '-'
                                ); ?>
                            </td>

                        </tr>


                        <tr>

                            <th>Record ID</th>

                            <td>
                                <?= htmlspecialchars(
                                    $log['record_id'] ?? '-'
                                ); ?>
                            </td>

                        </tr>

                    </table>

                </div>


                <div class="col-md-6">

                    <table class="table table-bordered">

                        <tr>

                            <th width="40%">IP Address</th>

                            <td>
                                <?= htmlspecialchars(
                                    $log['ip_address'] ?? '-'
                                ); ?>
                            </td>

                        </tr>


                        <tr>

                            <th>Date & Time</th>

                            <td>

                                <?= htmlspecialchars(
                                    date(
                                        'd M Y H:i:s',
                                        strtotime($log['created_at'])
                                    )
                                ); ?>

                            </td>

                        </tr>


                        <tr>

                            <th><?= __('phone') ?></th>

                            <td>
                                <?= htmlspecialchars(
                                    $log['phone'] ?? '-'
                                ); ?>
                            </td>

                        </tr>


                        <tr>

                            <th><?= __('email') ?></th>

                            <td>
                                <?= htmlspecialchars(
                                    $log['email'] ?? '-'
                                ); ?>
                            </td>

                        </tr>

                    </table>

                </div>

            </div>


            <div class="card card-outline card-primary">

                <div class="card-header">

                    <h3 class="card-title">

                        Description

                    </h3>

                </div>

                <div class="card-body">

                    <?= nl2br(
                        htmlspecialchars(
                            $log['description'] ?? '-'
                        )
                    ); ?>

                </div>

            </div>


            <div class="mt-3">

                <a
                    href="delete.php?id=<?= $log_id; ?>"
                    class="btn btn-danger"
                    onclick="return confirm('Delete this activity log?');"
                >

                    <i class="fas fa-trash mr-1"></i>

                    Delete Log

                </a>

                <a
                    href="index.php"
                    class="btn btn-secondary"
                >

                    Back

                </a>

            </div>

        </div>

    </div>

</div>

</section>

</div>

<?php

require_once '../../includes/dashboard/footer.php';

?>