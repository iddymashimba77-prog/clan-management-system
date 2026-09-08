<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$wallet_id = (int)($_GET['id'] ?? 0);

if ($wallet_id <= 0) {
    die(__('invalid_wallet'));
}

/*
|--------------------------------------------------------------------------
| Verify Wallet + User Membership
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        fw.*,
        fwm.current_balance,
        fwm.carry_forward,
        fwm.last_paid_month

    FROM family_wallets fw

    INNER JOIN family_wallet_members fwm
        ON fw.wallet_id = fwm.wallet_id

    WHERE fw.wallet_id = ?
    AND fwm.user_id = ?
    AND fw.status = 'active'

    LIMIT 1
");

$stmt->execute([
    $wallet_id,
    $_SESSION['user_id']
]);

$wallet = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$wallet) {
    http_response_code(403);
    die(__('not_assigned_to_wallet'));
}

/*
|--------------------------------------------------------------------------
| Get User Information
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        first_name,
        middle_name,
        last_name

    FROM users

    WHERE user_id = ?

    LIMIT 1
");

$stmt->execute([
    $_SESSION['user_id']
]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

$full_name = trim(
    $user['first_name'] . ' ' .
    ($user['middle_name'] ?? '') . ' ' .
    $user['last_name']
);

define('USER_ROLE', ucfirst(strtolower($_SESSION['roles'][0] ?? 'Member')));

require_once '../../includes/dashboard/header.php';

/*
|--------------------------------------------------------------------------
| Select Correct Sidebar
|--------------------------------------------------------------------------
*/

$roles = $_SESSION['roles'] ?? ['member'];
$role = strtolower($roles[0] ?? 'member');

if (in_array('Chairperson', $roles)) {

    require_once '../../includes/dashboard/sidebars/chairperson.php';
    $back_url = '../../chairperson/my_wallets/index.php';

} elseif (in_array('Treasurer', $roles)) {

    require_once '../../includes/dashboard/sidebars/treasurer.php';
    $back_url = '../../treasurer/my_wallets/index.php';

} elseif (in_array('Secretary', $roles)) {

    require_once '../../includes/dashboard/sidebars/secretary.php';
    $back_url = '../../secretary/my_wallets/index.php';

} else {

    require_once '../../includes/dashboard/sidebars/member.php';
    $back_url = 'index.php';

}

require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid" style="max-width: 1200px; margin: 0 auto;">

<div class="row">

<div class="col-md-8">

<div class="card card-success">

<div class="card-header">

<h3 class="card-title">

<i class="fas fa-wallet"></i>

<?= __('pay_family_wallet') ?>

</h3>

</div>

<div class="card-body">

<div class="alert alert-info">

<strong>
<?= htmlspecialchars($wallet['wallet_name']) ?>
</strong>

<br>

<?= __('contributing_as') ?>

<strong>
<?= htmlspecialchars($full_name) ?>
</strong>

</div>

<form
action="<?= BASE_URL ?>member/wallets/store_payments.php"
method="POST"
enctype="multipart/form-data"
>

<input
type="hidden"
name="wallet_id"
value="<?= $wallet_id ?>"
>

<!-- Wallet -->

<div class="form-group">

<label><?= __('wallet') ?></label>

<input
type="text"
class="form-control"
readonly
value="<?= htmlspecialchars($wallet['wallet_name']) ?>"
>

</div>

<!-- Monthly Amount -->

<div class="form-group">

<label><?= __('required_monthly_contribution') ?></label>

<input
type="text"
class="form-control"
readonly
value="TZS <?= number_format(
    $wallet['monthly_amount'],
    2
) ?>"
>

</div>

<!-- Payment Month -->

<div class="form-group">

<label>
<?= __('payment_month') ?>
</label>

<input
type="month"
name="payment_month"
class="form-control"
value="<?= date('Y-m') ?>"
required
>

<small class="text-muted">

<?= __('select_payment_month') ?>

</small>

</div>

<!-- Amount -->

<div class="form-group">

<label>
<?= __('amount_paid') ?>
</label>

<div class="input-group">

<div class="input-group-prepend">

<span class="input-group-text">
TZS
</span>

</div>

<input
type="number"
name="amount"
class="form-control"
step="0.01"
min="1"
placeholder="<?= __('enter_amount_paid') ?>"
required
>

</div>

<small class="text-muted">

<?= __('pay_partially_hint') ?>

</small>

</div>

<!-- Reference -->

<div class="form-group">

<label>
<?= __('bank_transaction_reference') ?>
</label>

<input
type="text"
name="reference_number"
class="form-control"
placeholder="<?= __('enter_transaction_reference') ?>"
required
>

</div>

<!-- Proof -->

<div class="form-group">

<label>
<?= __('payment_proof') ?>
</label>

<div class="custom-file">

<input
type="file"
name="payment_proof"
class="custom-file-input"
id="payment_proof"
accept=".jpg,.jpeg,.png,.pdf"
required
>

<label
class="custom-file-label"
for="payment_proof"
>

<?= __('choose_payment_proof') ?>

</label>

</div>

<small class="text-muted">

<?= __('allowed_jpg_jpeg_png_pdf') ?>

</small>

</div>

<hr>

<h5>

<i class="fas fa-university"></i>

<?= __('bank_payment_details') ?>

</h5>

<!-- Bank -->

<div class="form-group">

<label>
<?= __('bank_name') ?>
</label>

<input
type="text"
class="form-control"
readonly
value="<?= htmlspecialchars($wallet['bank_name']) ?>"
>

</div>

<!-- Account Name -->

<div class="form-group">

<label>
<?= __('account_name') ?>
</label>

<input
type="text"
class="form-control"
readonly
value="<?= htmlspecialchars($wallet['account_name']) ?>"
>

</div>

<!-- Account Number -->

<div class="form-group">

<label>
<?= __('account_number') ?>
</label>

<div class="input-group">

<input
type="text"
class="form-control"
readonly
value="<?= htmlspecialchars($wallet['account_number']) ?>"
id="account_number"
>

<div class="input-group-append">

<button
type="button"
class="btn btn-secondary"
onclick="copyAccountNumber()"
>

<i class="fas fa-copy"></i>

<?= __('copy') ?>

</div>

</div>

</div>

<hr>

<!-- Current Balance -->

<div class="row">

<div class="col-md-6">

<div class="small-box bg-info">

<div class="inner">

<h4>
TZS <?= number_format(
    $wallet['current_balance'] ?? 0,
    2
) ?>
</h4>

<p><?= __('current_balance') ?></p>

</div>

<div class="icon">

<i class="fas fa-money-bill-wave"></i>

</div>

</div>

</div>

<div class="col-md-6">

<div class="small-box bg-success">

<div class="inner">

<h4>
TZS <?= number_format(
    $wallet['carry_forward'] ?? 0,
    2
) ?>
</h4>

<p><?= __('carry_forward') ?></p>

</div>

<div class="icon">

<i class="fas fa-forward"></i>

</div>

</div>

</div>

</div>

<!-- Buttons -->

<div class="form-group mt-4">

<button
type="submit"
class="btn btn-success"
>

<i class="fas fa-paper-plane"></i>

<?= __('submit_payment') ?>

</button>

<a
href="<?= $back_url ?>"
class="btn btn-secondary"
>

<i class="fas fa-arrow-left"></i>

<?= __('back') ?>

</a>

</div>

</form>

</div>

</div>

</div>

</div>

</div>

</section>

<script>

function copyAccountNumber() {

    const account =
        document.getElementById(
            'account_number'
        );

    navigator.clipboard.writeText(
        account.value
    );

    alert(
        '<?= __('account_number_copied') ?>'
    );
}

document
.getElementById('payment_proof')
.addEventListener(
    'change',
    function () {

        let fileName =
            this.files[0]
            ? this.files[0].name
            : '<?= __('choose_payment_proof') ?>';

        this.nextElementSibling.innerText =
            fileName;

    }
);

</script>

<?php

require_once '../../includes/dashboard/footer.php';

?>

