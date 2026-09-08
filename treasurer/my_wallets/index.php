<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireTreasurer();

$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->prepare("
    SELECT
        fw.*,
        fwm.current_balance,
        fwm.carry_forward,
        fwm.last_paid_month

    FROM family_wallets fw

    INNER JOIN family_wallet_members fwm
        ON fw.wallet_id = fwm.wallet_id

    WHERE fwm.user_id = ?
    AND fw.status = 'active'

    ORDER BY fw.created_at DESC
");

$stmt->execute([
    $_SESSION['user_id']
]);

$wallets = $stmt->fetchAll(PDO::FETCH_ASSOC);

define('USER_ROLE', 'Treasurer');

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebar.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid" style="max-width: 1200px; margin: 0 auto;">

<div class="row">

<?php foreach ($wallets as $wallet): ?>

<div class="col-md-6 col-lg-4">

<div class="card card-primary">

<div class="card-header">

<h3 class="card-title">
<?= htmlspecialchars($wallet['wallet_name']) ?>
</h3>

</div>

<div class="card-body">

<?php if (!empty($wallet['description'])): ?>

<p>
<?= htmlspecialchars($wallet['description']) ?>
</p>

<?php endif; ?>

<p>
<strong><?= __('monthly_contribution') ?></strong><br>
TZS <?= number_format(
    $wallet['monthly_amount'],
    2
) ?>
</p>

<p>
<strong><?= __('target_amount') ?></strong><br>
TZS <?= number_format(
    $wallet['target_amount'],
    2
) ?>
</p>

<hr>

<p>
<strong><?= __('new_balance') ?></strong><br>
TZS <?= number_format(
    $wallet['current_balance'] ?? 0,
    2
) ?>
</p>

<p>
<strong><?= __('carry_forward') ?></strong><br>
TZS <?= number_format(
    $wallet['carry_forward'] ?? 0,
    2
) ?>
</p>

<p>
<strong><?= __('start') ?></strong>
<?= htmlspecialchars($wallet['start_date']) ?>
</p>

<p>
<strong><?= __('end') ?></strong>
<?= htmlspecialchars($wallet['end_date']) ?>
</p>

<a
href="<?= BASE_URL ?>member/wallets/pay.php?id=<?= $wallet['wallet_id'] ?>"
class="btn btn-success btn-block"
>

<i class="fas fa-money-bill-wave"></i>

<?= __('pay_wallet') ?>

</a>

</div>

</div>

</div>

<?php endforeach; ?>

<?php if (empty($wallets)): ?>

<div class="col-12">

<div class="alert alert-info">

<i class="fas fa-info-circle"></i>

<?= __('you_have_not_been_assigned_to_any_family_wallet') ?>

</div>

</div>

<?php endif; ?>

</div>

</div>

</section>

</div>

<?php

require_once '../../includes/dashboard/footer.php';

?>