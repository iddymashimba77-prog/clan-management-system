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

    $_SESSION['error'] = 'Invalid income ID.';

    header('Location: index.php');

    exit;
}

$database = new Database();

$pdo = $database->connect();

$stmt = $pdo->prepare("
    SELECT *
    FROM income
    WHERE income_id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$income = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$income) {

    $_SESSION['error'] = 'Income record not found.';

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

<?= __('edit_income') ?>

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
    name="income_id"
    value="<?= (int)$income['income_id'] ?>"
>

<div class="card-body">

<div class="row">

<div class="col-md-6">

<div class="form-group">

<label><?= __('income_title') ?></label>

<input
    type="text"
    name="income_title"
    class="form-control"
    value="<?= htmlspecialchars($income['income_title']) ?>"
    required
>

</div>

</div>


<div class="col-md-6">

<div class="form-group">

<label><?= __('income_category') ?></label>

<select
    name="income_category"
    class="form-control"
    required
>

<?php

$categories = [
    'Contributions',
    'Membership Fees',
    'Donations',
    'Fundraising',
    'Other'
];

?>

<option value="">
<?= __('select_category') ?>
</option>

<?php foreach ($categories as $category): ?>

<option
    value="<?= htmlspecialchars($category) ?>"
    <?= $income['income_category'] === $category ? 'selected' : '' ?>
>

<?= htmlspecialchars($category) ?>

</option>

<?php endforeach; ?>

</select>

</div>

</div>


<div class="col-md-6">

<div class="form-group">

<label><?= __('amount') ?></label>

<input
    type="number"
    name="amount"
    class="form-control"
    min="0.01"
    step="0.01"
    value="<?= htmlspecialchars($income['amount']) ?>"
    required
>

</div>

</div>


<div class="col-md-6">

<div class="form-group">

<label><?= __('income_date') ?></label>

<input
    type="date"
    name="income_date"
    class="form-control"
    value="<?= htmlspecialchars($income['income_date']) ?>"
    required
>

</div>

</div>


<div class="col-md-6">

<div class="form-group">

<label><?= __('received_from') ?></label>

<input
    type="text"
    name="received_from"
    class="form-control"
    value="<?= htmlspecialchars($income['received_from'] ?? '') ?>"
>

</div>

</div>


<div class="col-md-6">

<div class="form-group">

<label><?= __('reference_number') ?></label>

<input
    type="text"
    name="reference_number"
    class="form-control"
    value="<?= htmlspecialchars($income['reference_number'] ?? '') ?>"
>

</div>

</div>


<div class="col-md-6">

<div class="form-group">

<label><?= __('payment_method') ?></label>

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

<option value=""><?= __('select_method') ?></option>

<?php foreach ($methods as $method): ?>

<option
    value="<?= htmlspecialchars($method) ?>"
    <?= ($income['payment_method'] ?? '') === $method ? 'selected' : '' ?>
>

<?= htmlspecialchars($method) ?>

</option>

<?php endforeach; ?>

</select>

</div>

</div>


<div class="col-md-12">

<div class="form-group">

<label><?= __('description') ?></label>

<textarea
    name="description"
    class="form-control"
    rows="4"
><?= htmlspecialchars($income['description'] ?? '') ?></textarea>

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

<?= __('update_income') ?>

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