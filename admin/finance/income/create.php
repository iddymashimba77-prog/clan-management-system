<?php

require_once '../../../config/database.php';
require_once '../../../config/constants.php';
require_once '../../../config/auth.php';

requireLogin();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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

<i class="fas fa-plus-circle text-success mr-2"></i>

<?= __('add_income') ?>

</h5>

</div>

<form
    action="store.php"
    method="POST"
>

<input
    type="hidden"
    name="csrf_token"
    value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
>

<div class="card-body">

<div class="row">

<div class="col-md-6">

<div class="form-group">

<label>
<?= __('income_title') ?>
</label>

<input
    type="text"
    name="income_title"
    class="form-control"
    required
    maxlength="255"
    placeholder="e.g. Membership Contribution"
>

</div>

</div>


<div class="col-md-6">

<div class="form-group">

<label>
<?= __('income_category') ?>
</label>

<select
    name="income_category"
    class="form-control"
    required
>

<option value="">
<?= __('select_category') ?>
</option>

<option value="Contributions">
Contributions
</option>

<option value="Membership Fees">
Membership Fees
</option>

<option value="Donations">
Donations
</option>

<option value="Fundraising">
Fundraising
</option>

<option value="Other">
Other
</option>

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
    required
>

</div>

</div>


<div class="col-md-6">

<div class="form-group">

<label>
<?= __('income_date') ?>
</label>

<input
    type="date"
    name="income_date"
    class="form-control"
    value="<?= date('Y-m-d') ?>"
    required
>

</div>

</div>


<div class="col-md-6">

<div class="form-group">

<label>
<?= __('received_from') ?>
</label>

<input
    type="text"
    name="received_from"
    class="form-control"
    maxlength="255"
    placeholder="Name / Organization"
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
    maxlength="255"
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

<option value="">
<?= __('select_method') ?>
</option>

<option value="Cash">
Cash
</option>

<option value="Bank">
Bank
</option>

<option value="Mobile Money">
Mobile Money
</option>

<option value="Other">
Other
</option>

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
></textarea>

</div>

</div>

</div>

</div>

<div class="card-footer">

<button
    type="submit"
    class="btn btn-success"
>

<i class="fas fa-save mr-1"></i>

<?= __('save_income') ?>

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