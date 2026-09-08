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

$search = trim($_GET['search'] ?? '');

$where = '';

$params = [];

if ($search !== '') {

    $where = "
        WHERE
            expense_title LIKE ?
            OR expense_category LIKE ?
            OR paid_to LIKE ?
            OR reference_number LIKE ?
            OR description LIKE ?
    ";

    $value = '%' . $search . '%';

    $params = [
        $value,
        $value,
        $value,
        $value,
        $value
    ];
}


/* Total Expenses */

$totalStmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount), 0)
    FROM expenses
    $where
");

$totalStmt->execute($params);

$totalExpenses =
    (float)$totalStmt->fetchColumn();


/* Records */

$stmt = $pdo->prepare("
    SELECT
        e.*,

        u.member_code,
        u.first_name,
        u.middle_name,
        u.last_name

    FROM expenses e

    LEFT JOIN users u
        ON u.user_id = e.created_by_user_id

    $where

    ORDER BY
        e.expense_date DESC,
        e.expense_id DESC
");

$stmt->execute($params);

$expenses =
    $stmt->fetchAll(PDO::FETCH_ASSOC);


require_once '../../../includes/dashboard/header.php';

require_once '../../../includes/dashboard/sidebars/admin.php';

require_once '../../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">


<div class="d-flex justify-content-between align-items-center flex-wrap mb-3">

<div>

<h4 class="mb-1">

<i class="fas fa-arrow-circle-up text-danger mr-2"></i>

<?= __('expense_management') ?>

</h4>

<small class="text-muted">

<?= __('manage_all_clan_expenses') ?>

</small>

</div>


<div class="mt-2">

<a
    href="create.php"
    class="btn btn-danger"
>

<i class="fas fa-plus mr-1"></i>

<?= __('add_expense') ?>

</a>

</div>

</div>


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


<div class="row mb-3">

<div class="col-md-4">

<div class="small-box bg-danger">

<div class="inner">

<h3>
<?= number_format($totalExpenses, 2) ?>
</h3>

<p><?= __('total_expenses') ?></p>

</div>

<div class="icon">

<i class="fas fa-receipt"></i>

</div>

</div>

</div>

</div>


<div class="card clan-page-card">

<div class="card-body">

<form method="GET">

<div class="input-group">

<input
    type="text"
    name="search"
    class="form-control"
    placeholder="<?= __('search_expenses') ?>"
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


<div class="card clan-page-card">

<div class="card-header">

<h5 class="mb-0">

<i class="fas fa-receipt mr-2 text-danger"></i>

<?= __('expense_records') ?>

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

<th>Paid To</th>

<th><?= __('reference') ?></th>

<th>Added By</th>

<th width="150"><?= __('actions') ?></th>

</tr>

</thead>

<tbody>

<?php if (!empty($expenses)): ?>

<?php foreach ($expenses as $index => $expense): ?>

<tr>

<td>
<?= $index + 1 ?>
</td>

<td>

<strong>

<?= htmlspecialchars(
    $expense['expense_title']
) ?>

</strong>

</td>

<td>

<?= htmlspecialchars(
    $expense['expense_category']
) ?>

</td>

<td>

<strong class="text-danger">

<?= number_format(
    (float)$expense['amount'],
    2
) ?>

</strong>

</td>

<td>

<?= htmlspecialchars(
    $expense['expense_date']
) ?>

</td>

<td>

<?= htmlspecialchars(
    $expense['paid_to'] ?? 'N/A'
) ?>

</td>

<td>

<?= htmlspecialchars(
    $expense['reference_number'] ?? 'N/A'
) ?>

</td>

<td>

<?php

$name = trim(
    ($expense['first_name'] ?? '') . ' ' .
    ($expense['middle_name'] ?? '') . ' ' .
    ($expense['last_name'] ?? '')
);

?>

<?= htmlspecialchars(
    $name !== '' ? $name : 'System'
) ?>

</td>

<td>

<a
    href="edit.php?id=<?= (int)$expense['expense_id'] ?>"
    class="btn btn-sm btn-warning"
>

<i class="fas fa-edit"></i>

</a>

<a
    href="delete.php?id=<?= (int)$expense['expense_id'] ?>"
    class="btn btn-sm btn-danger"
    onclick="return confirm('Are you sure you want to delete this expense?');"
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

<i class="fas fa-receipt fa-2x mb-2"></i>

<br>

<?= __('no_expense_records_found') ?>

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