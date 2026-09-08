<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();


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

$stmt->execute([$_SESSION['user_id']]);

define('USER_ROLE', ucfirst(strtolower($_SESSION['roles'][0] ?? 'Member')));

require_once '../../includes/dashboard/header.php';

/*
|--------------------------------------------------------------------------
| Select Correct Sidebar
|--------------------------------------------------------------------------
*/

$roles = $_SESSION['roles'] ?? ['member'];

if (in_array('Chairperson', $roles)) {

    require_once '../../includes/dashboard/sidebars/chairperson.php';

} elseif (in_array('Treasurer', $roles)) {

    require_once '../../includes/dashboard/sidebars/treasurer.php';

} elseif (in_array('Secretary', $roles)) {

    require_once '../../includes/dashboard/sidebars/secretary.php';

} else {

    require_once '../../includes/dashboard/sidebars/member.php';

}

require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid" style="max-width: 1200px; margin: 0 auto;">

<div class="row">

<?php if (!$wallets): ?>

<div class="col-12">

<div class="alert alert-info">

<?= __('you_have_not_been_assigned_to_any_wallet_yet') ?>

</div>

</div>

<?php endif; ?>

<?php foreach ($wallets as $wallet): ?>

<div class="col-md-6 col-lg-4">

<div class="card card-primary">

<div class="card-header">

<h3 class="card-title">

<?= htmlspecialchars($wallet['wallet_name']) ?>

</h3>

</div>

<div class="card-body">

<p>

<strong><?= __('goal') ?></strong><br>

<?= htmlspecialchars($wallet['purpose']) ?>

</p>

<p>

<strong><?= __('monthly_contribution') ?></strong><br>

TZS <?= number_format($wallet['monthly_amount'],2) ?>

</p>

<p>

<strong><?= __('new_balance') ?></strong><br>

TZS <?= number_format($wallet['current_balance'],2) ?>

</p>

<p>

<strong><?= __('carry_forward') ?></strong><br>

TZS <?= number_format($wallet['carry_forward'],2) ?>

</p>

<p>

<strong><?= __('target') ?></strong><br>

TZS <?= number_format($wallet['target_amount'],2) ?>

</p>

<a
href="pay.php?id=<?= $wallet['wallet_id'] ?>"
class="btn btn-success btn-block">

<i class="fas fa-money-bill-wave"></i>

<?= __('pay_wallet') ?>

</a>

</div>

</div>

</div>

<?php endforeach; ?>

</div>

</div>

</section>

<?php

require_once '../../includes/dashboard/footer.php';

?>

