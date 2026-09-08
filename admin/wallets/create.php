<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$membersStmt = $pdo->query("
    SELECT
        user_id,
        member_code,
        first_name,
        middle_name,
        last_name
    FROM users
    WHERE is_deleted = 0
      AND account_status = 'active'
    ORDER BY first_name, last_name
");

$members = $membersStmt->fetchAll(PDO::FETCH_ASSOC);

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

<i class="fas fa-wallet mr-2"></i>

<?= __('create_family_wallet') ?>

</h3>

</div>

<form action="store.php" method="POST">

<div class="card-body">

<div class="row">

<div class="col-md-6">

<div class="form-group">

<label><?= __('wallet_name') ?></label>

<input
    type="text"
    name="wallet_name"
    class="form-control"
    required
>

</div>

</div>

<div class="col-md-6">

<div class="form-group">

<label><?= __('monthly_amount_label') ?></label>

<input
    type="number"
    step="0.01"
    min="0"
    name="monthly_amount"
    class="form-control"
    required
>

</div>

</div>

</div>


<div class="form-group">

<label><?= __('description_label') ?></label>

<textarea
    name="description"
    class="form-control"
    rows="3"
></textarea>

</div>


<div class="form-group">

<label><?= __('purpose') ?></label>

<textarea
    name="purpose"
    class="form-control"
    rows="3"
></textarea>

</div>


<div class="row">

<div class="col-md-6">

<div class="form-group">

<label><?= __('target_amount_label') ?></label>

<input
    type="number"
    step="0.01"
    min="0"
    name="target_amount"
    class="form-control"
    required
>

</div>

</div>


<div class="col-md-3">

<div class="form-group">

<label><?= __('start_date_label') ?></label>

<input
    type="date"
    name="start_date"
    class="form-control"
    required
>

</div>

</div>


<div class="col-md-3">

<div class="form-group">

<label><?= __('end_date_label') ?></label>

<input
    type="date"
    name="end_date"
    class="form-control"
    required
>

</div>

</div>

</div>


<div class="row">

<div class="col-md-4">

<div class="form-group">

<label><?= __('bank_name_label') ?></label>

<input
    type="text"
    name="bank_name"
    class="form-control"
    required
>

</div>

</div>


<div class="col-md-4">

<div class="form-group">

<label><?= __('account_name_input') ?></label>

<input
    type="text"
    name="account_name"
    class="form-control"
    required
>

</div>

</div>


<div class="col-md-4">

<div class="form-group">

<label><?= __('account_number_input') ?></label>

<input
    type="text"
    name="account_number"
    class="form-control"
    required
>

</div>

</div>

</div>


<div class="form-group">

<label><?= __('visibility') ?></label>

<select name="visibility" class="form-control" id="visibility">

<option value="all">
    <?= __('all_members') ?>
</option>

<option value="selected">
    <?= __('selected_members') ?>
</option>

</select>

</div>


<div
    id="memberSelection"
    style="display:none;"
>

<label><?= __('select_members') ?></label>

<div class="border rounded p-3">

<?php foreach ($members as $member): ?>

<div class="form-check mb-2">

<input
    type="checkbox"
    class="form-check-input"
    name="members[]"
    value="<?= (int)$member['user_id']; ?>"
    id="member<?= (int)$member['user_id']; ?>"
>

<label
    class="form-check-label"
    for="member<?= (int)$member['user_id']; ?>"
>

<?= htmlspecialchars(
    $member['member_code']
    . ' - '
    . $member['first_name']
    . ' '
    . $member['last_name']
); ?>

</label>

</div>

<?php endforeach; ?>

</div>

</div>

</div>


<div class="card-footer">

<button
    type="submit"
    class="btn btn-success"
>

<i class="fas fa-save mr-1"></i>

<?= __('create_wallet') ?>

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


<script>

document.addEventListener('DOMContentLoaded', function () {

    const visibility =
        document.getElementById('visibility');

    const memberSelection =
        document.getElementById('memberSelection');

    visibility.addEventListener('change', function () {

        if (this.value === 'selected') {

            memberSelection.style.display = 'block';

        } else {

            memberSelection.style.display = 'none';

        }

    });

});

</script>

<?php require_once '../../includes/dashboard/footer.php'; ?>