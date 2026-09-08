<?php

require_once '../../../config/database.php';
require_once '../../../config/constants.php';
require_once '../../../config/auth.php';

requireLogin();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {

    $_SESSION['error'] =
        'Invalid expense ID.';

    header('Location: index.php');

    exit;
}

$database = new Database();

$pdo = $database->connect();

$stmt = $pdo->prepare("
    SELECT *
    FROM expenses
    WHERE expense_id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$expense =
    $stmt->fetch(PDO::FETCH_ASSOC);

if (!$expense) {

    $_SESSION['error'] =
        'Expense record not found.';

    header('Location: index.php');

    exit;
}

if (empty($_SESSION['csrf_token'])) {

    $_SESSION['csrf_token'] =
        bin2hex(random_bytes(32));
}

require_once '../../../includes/dashboard/header.php';

require_once '../../../includes/dashboard/sidebars/admin.php';

require_once '../../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">

<div class="card clan-page-card">

<div class="card-header">

<h5 class="mb-0">

<i class="fas fa-edit text-warning mr-2"></i>

<?= __('edit_expense') ?>

</h5>

</div>


<form
    action="update.php"
    method="POST"
>

<input
    type="hidden"
    name="csrf_token"
    value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
>

<input
    type="hidden"
    name="expense_id"
    value="<?= (int)$expense['expense_id'] ?>"
>


<div class="card-body">

<div class="row">


<div class="col-md-6">

<div class="form-group">

<label>
<?= __('expense_title') ?>
</label>

<input
    type="text"
    name="expense_title"
    class="form-control"
    value="<?= htmlspecialchars($expense['expense_title']) ?>"
    required
>

</div>

</div>


<div class="col-md-6">

<div class="form-group">

<label>
<?= __('expense_category') ?>
</label>

<select
    name="expense_category"
    class="form-control"
    required
>

<?php

$categories = [
    'Office',
    'Transport',
    'Utilities',
    'Events',
    'Maintenance',
    'Salaries',
    'Other'
];

?>

<option value="">
<?= __('select_category') ?>
</option>

<?php foreach ($categories as $category): ?>

<option
    value="<?= htmlspecialchars($category) ?>"
    <?= $expense['expense_category'] === $category ? 'selected' : '' ?>
>

<?= htmlspecialchars($category) ?>

</option>

<?php endforeach; ?>

</select>

</div>

</div>


<div class="col-md-6">

<div class="form-group">

<label>
<?= __('amount') ?>
</label>

<input
    type="number"
    name="amount"
    class="form-control"
    min="0.01"
    step="0.01"
    value="<?= htmlspecialchars($expense['amount']) ?>"
    required
>

</div>

</div>


<div class="col-md-6">

<div class="form-group">

<label>
<?= __('expense_date') ?>
</label>

<input
    type="date"
    name="expense_date"
    class="form-control"
    value="<?= htmlspecialchars($expense['expense_date']) ?>"
    required
>

</div>

</div>


<div class="col-md-6">

<div class="form-group">

<label>
<?= __('paid_to') ?>
</label>

<input
    type="text"
    name="paid_to"
    class="form-control"
    value="<?= htmlspecialchars($expense['paid_to'] ?? '') ?>"
>

</div>

</div>


<div class="col-md-6">

<div class="form-group">

<label>
<?= __('reference_number') ?>
</label>

<input
    type="text"
    name="reference_number"
    class="form-control"
    value="<?= htmlspecialchars($expense['reference_number'] ?? '') ?>"
>

</div>

</div>


<div class="col-md-6">

<div class="form-group">

<label>
<?= __('payment_method') ?>
</label>

<select
    name="payment_method"
    class="form-control"
>

<?php

$methods = [
    'Cash',
    'Bank',
    'Mobile Money',
    'Other'
];

?>

<option value="">
<?= __('select_method') ?>
</option>

<?php foreach ($methods as $method): ?>

<option
    value="<?= htmlspecialchars($method) ?>"
    <?= ($expense['payment_method'] ?? '') === $method ? 'selected' : '' ?>
>

<?= htmlspecialchars($method) ?>

</option>

<?php endforeach; ?>

</select>

</div>

</div>


<div class="col-md-12">

<div class="form-group">

<label>
<?= __('description') ?>
</label>

<textarea
    name="description"
    class="form-control"
    rows="4"
><?= htmlspecialchars($expense['description'] ?? '') ?></textarea>

</div>

</div>


</div>

</div>


<div class="card-footer">

<button
    type="submit"
    class="btn btn-primary"
>

<i class="fas fa-save mr-1"></i>

<?= __('update_expense') ?>

</button>

<a
    href="index.php"
    class="btn btn-secondary"
>

<?= __('cancel') ?>

</a>

</div>

</form>

</div>

</div>

</section>

</div>

<?php

require_once '../../../includes/dashboard/footer.php';

?>