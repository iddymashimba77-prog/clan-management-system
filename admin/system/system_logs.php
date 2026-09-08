<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = $database->connect();

$userId = $_SESSION['user_id'] ?? 0;


/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT r.role_name
    FROM users u
    LEFT JOIN roles r ON r.role_id = u.role_id
    WHERE u.user_id = ?
      AND u.is_deleted = 0
      AND u.account_status = 'active'
    LIMIT 1
");

$stmt->execute([$userId]);

$role = $stmt->fetchColumn();

if (!$role || strtolower($role) !== 'administrator') {
    http_response_code(403);
    exit('Access denied.');
}


/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

$page = max(
    1,
    (int)($_GET['page'] ?? 1)
);

$limit = 25;

$offset = ($page - 1) * $limit;


/*
|--------------------------------------------------------------------------
| Total
|--------------------------------------------------------------------------
*/

$total = (int)$pdo->query("
    SELECT COUNT(*)
    FROM activity_logs
")->fetchColumn();

$totalPages = max(
    1,
    (int)ceil($total / $limit)
);


/*
|--------------------------------------------------------------------------
| Logs
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        al.*,
        u.member_code,
        u.first_name,
        u.middle_name,
        u.last_name
    FROM activity_logs al
    LEFT JOIN users u
        ON u.user_id = al.user_id
    ORDER BY al.created_at DESC
    LIMIT ? OFFSET ?
");

$stmt->bindValue(
    1,
    $limit,
    PDO::PARAM_INT
);

$stmt->bindValue(
    2,
    $offset,
    PDO::PARAM_INT
);

$stmt->execute();

$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);


require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/admin.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">

<div class="card clan-page-card">

<div class="card-header d-flex justify-content-between align-items-center">

    <h5 class="mb-0">

        <i class="fas fa-history text-primary mr-2"></i>

        System Activity Logs

    </h5>

    <span class="badge badge-primary">
        <?= $total ?> Records
    </span>

</div>


<div class="card-body p-0">

<div class="table-responsive">

<table class="table table-hover table-striped mb-0">

<thead>

<tr>

    <th>#</th>

    <th><?= __('action') ?></th>

    <th><?= __('user') ?></th>

    <th><?= __('table') ?></th>

    <th><?= __('description') ?></th>

    <th>IP Address</th>

    <th><?= __('date') ?></th>

</tr>

</thead>


<tbody>

<?php if ($logs): ?>

<?php foreach ($logs as $index => $log): ?>

<tr>

    <td>
        <?= $offset + $index + 1 ?>
    </td>

    <td>

        <span class="badge badge-primary">

            <?= htmlspecialchars(
                $log['action']
            ) ?>

        </span>

    </td>


    <td>

        <?php if ($log['user_id']): ?>

            <?= htmlspecialchars(
                trim(
                    $log['first_name'] . ' ' .
                    ($log['middle_name'] ?? '') . ' ' .
                    $log['last_name']
                )
            ) ?>

            <small class="d-block text-muted">

                <?= htmlspecialchars(
                    $log['member_code'] ?? ''
                ) ?>

            </small>

        <?php else: ?>

            System

        <?php endif; ?>

    </td>


    <td>

        <?= htmlspecialchars(
            $log['table_name'] ?? '-'
        ) ?>

    </td>


    <td>

        <?= htmlspecialchars(
            $log['description'] ?? ''
        ) ?>

    </td>


    <td>

        <?= htmlspecialchars(
            $log['ip_address'] ?? '-'
        ) ?>

    </td>


    <td>

        <?= htmlspecialchars(
            $log['created_at']
        ) ?>

    </td>

</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>

    <td
        colspan="7"
        class="text-center text-muted py-5"
    >

        <i class="fas fa-history fa-2x mb-2"></i>

        <div>
            <?= __('no_activity_logs_found') ?>
        </div>

    </td>

</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</div>


<!-- PAGINATION -->

<?php if ($totalPages > 1): ?>

<div class="card-footer">

<nav>

<ul class="pagination mb-0">

<?php if ($page > 1): ?>

<li class="page-item">

<a
    class="page-link"
    href="?page=<?= $page - 1 ?>"
>

    Previous

</a>

</li>

<?php endif; ?>


<?php for ($i = 1; $i <= $totalPages; $i++): ?>

<li
    class="page-item
    <?= $i === $page ? 'active' : '' ?>"
>

<a
    class="page-link"
    href="?page=<?= $i ?>"
>

    <?= $i ?>

</a>

</li>

<?php endfor; ?>


<?php if ($page < $totalPages): ?>

<li class="page-item">

<a
    class="page-link"
    href="?page=<?= $page + 1 ?>"
>

    Next

</a>

</li>

<?php endif; ?>

</ul>

</nav>

</div>

<?php endif; ?>

</div>

</div>

</div>

</section>

</div>

<?php require_once '../../includes/dashboard/footer.php'; ?>