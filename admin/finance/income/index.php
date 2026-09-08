<?php

require_once '../../../config/database.php';
require_once '../../../config/constants.php';
require_once '../../../config/auth.php';

requireLogin();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$database = new Database();
$pdo = $database->connect();

/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? '');

$where = '';
$params = [];

if ($search !== '') {

    $where = "
        WHERE
            income_title LIKE ?
            OR income_category LIKE ?
            OR received_from LIKE ?
            OR reference_number LIKE ?
            OR description LIKE ?
    ";

    $searchValue = '%' . $search . '%';

    $params = [
        $searchValue,
        $searchValue,
        $searchValue,
        $searchValue,
        $searchValue
    ];
}

/*
|--------------------------------------------------------------------------
| Total Income
|--------------------------------------------------------------------------
*/

$totalStmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount), 0)
    FROM income
    $where
");

$totalStmt->execute($params);

$totalIncome = (float)$totalStmt->fetchColumn();

/*
|--------------------------------------------------------------------------
| <?= __('income_records') ?>
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        i.*,
        u.member_code,
        u.first_name,
        u.middle_name,
        u.last_name

    FROM income i

    LEFT JOIN users u
        ON u.user_id = i.created_by_user_id

    $where

    ORDER BY i.income_date DESC, i.income_id DESC
");

$stmt->execute($params);

$incomes = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

require_once '../../../includes/dashboard/header.php';

require_once '../../../includes/dashboard/sidebars/admin.php';

require_once '../../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">

<!-- Header -->

<div class="d-flex justify-content-between align-items-center flex-wrap mb-3">

    <div>

        <h4 class="mb-1">

            <i class="fas fa-arrow-circle-down text-success mr-2"></i>

            <?= __('income_management') ?>

        </h4>

        <small class="text-muted">

            <?= __('manage_all_clan_income_records') ?>

        </small>

    </div>

    <div class="mt-2">

        <a
            href="create.php"
            class="btn btn-success"
        >

            <i class="fas fa-plus mr-1"></i>

            <?= __('add_income') ?>

        </a>

    </div>

</div>


<!-- Alerts -->

<?php if (!empty($_SESSION['success'])): ?>

<div class="alert alert-success">

    <?= htmlspecialchars($_SESSION['success']) ?>

</div>

<?php unset($_SESSION['success']); ?>

<?php endif; ?>


<?php if (!empty($_SESSION['error'])): ?>

<div class="alert alert-danger">

    <?= htmlspecialchars($_SESSION['error']) ?>

</div>

<?php unset($_SESSION['error']); ?>

<?php endif; ?>


<!-- Total -->

<div class="row mb-3">

    <div class="col-md-4">

        <div class="small-box bg-success">

            <div class="inner">

                <h3>
                    <?= number_format($totalIncome, 2) ?>
                </h3>

                <p><?= __('total_income') ?></p>

            </div>

            <div class="icon">

                <i class="fas fa-money-bill-wave"></i>

            </div>

        </div>

    </div>

</div>


<!-- Search -->

<div class="card clan-page-card">

<div class="card-body">

<form method="GET" class="form-inline">

    <div class="input-group w-100">

        <input
            type="text"
            name="search"
            class="form-control"
            placeholder="<?= __('search_income') ?>"
            value="<?= htmlspecialchars($search) ?>"
        >

        <div class="input-group-append">

            <button
                class="btn btn-primary"
                type="submit"
            >

                <i class="fas fa-search"></i>

                Search

            </button>

        </div>

    </div>

</form>

</div>

</div>


<!-- Table -->

<div class="card clan-page-card">

<div class="card-header">

    <h5 class="mb-0">

        <i class="fas fa-list mr-2 text-success"></i>

        <?= __('income_records') ?>

    </h5>

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-hover">

<thead>

<tr>

    <th>#</th>

    <th><?= __('title') ?></th>

    <th><?= __('category') ?></th>

    <th><?= __('amount') ?></th>

    <th><?= __('date') ?></th>

    <th>Received From</th>

    <th><?= __('reference') ?></th>

    <th>Added By</th>

    <th width="150"><?= __('actions') ?></th>

</tr>

</thead>

<tbody>

<?php if (!empty($incomes)): ?>

<?php foreach ($incomes as $index => $income): ?>

<tr>

<td>
    <?= $index + 1 ?>
</td>

<td>
    <strong>
        <?= htmlspecialchars($income['income_title']) ?>
    </strong>
</td>

<td>
    <?= htmlspecialchars($income['income_category']) ?>
</td>

<td>

<strong class="text-success">

    <?= number_format(
        (float)$income['amount'],
        2
    ) ?>

</strong>

</td>

<td>
    <?= htmlspecialchars($income['income_date']) ?>
</td>

<td>
    <?= htmlspecialchars(
        $income['received_from'] ?? 'N/A'
    ) ?>
</td>

<td>
    <?= htmlspecialchars(
        $income['reference_number'] ?? 'N/A'
    ) ?>
</td>

<td>

<?php

$creator = trim(
    ($income['first_name'] ?? '') . ' ' .
    ($income['middle_name'] ?? '') . ' ' .
    ($income['last_name'] ?? '')
);

?>

<?= htmlspecialchars(
    $creator !== '' ? $creator : 'System'
) ?>

</td>

<td>

<a
    href="edit.php?id=<?= (int)$income['income_id'] ?>"
    class="btn btn-sm btn-warning"
    title="Edit"
>

    <i class="fas fa-edit"></i>

</a>

<a
    href="delete.php?id=<?= (int)$income['income_id'] ?>"
    class="btn btn-sm btn-danger"
    title="Delete"
    onclick="return confirm('Are you sure you want to delete this income record?');"
>

    <i class="fas fa-trash"></i>

</a>

</td>

</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>

<td
    colspan="9"
    class="text-center text-muted py-4"
>

    <i class="fas fa-money-bill-wave fa-2x mb-2"></i>

    <br>

    <?= __('no_income_records_found') ?>

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

<?php

require_once '../../../includes/dashboard/footer.php';

?>