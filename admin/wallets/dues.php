<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$wallet_id = isset($_GET['wallet_id'])
    ? (int) $_GET['wallet_id']
    : 0;

if ($wallet_id <= 0) {
    header('Location: index.php?error=invalid_wallet');
    exit;
}

/*
|--------------------------------------------------------------------------
| WALLET
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM family_wallets
    WHERE wallet_id = ?
    LIMIT 1
");

$stmt->execute([$wallet_id]);

$wallet = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$wallet) {
    header('Location: index.php?error=wallet_not_found');
    exit;
}

/*
|--------------------------------------------------------------------------
| MONTHLY DUES
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        d.*,

        u.member_code,
        u.first_name,
        u.middle_name,
        u.last_name,
        u.phone

    FROM wallet_monthly_dues d

    INNER JOIN users u
        ON u.user_id = d.member_user_id

    WHERE d.wallet_id = ?

    ORDER BY
        d.due_month DESC,
        u.first_name ASC
");

$stmt->execute([$wallet_id]);

$dues = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| SUMMARY
|--------------------------------------------------------------------------
*/

$totalRequired = 0;
$totalPaid     = 0;
$totalBalance  = 0;

foreach ($dues as $due) {

    $totalRequired += (float) $due['required_amount'];
    $totalPaid     += (float) $due['paid_amount'];
    $totalBalance  += (float) $due['balance'];
}

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebar.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">

    <!-- HEADER -->

    <div class="card clan-page-card">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center flex-wrap">

                <div>

                    <h3 class="mb-1">

                        <i class="fas fa-calendar-check text-primary mr-2"></i>

                        Monthly Dues

                    </h3>

                    <p class="text-muted mb-0">

                        <?= htmlspecialchars($wallet['wallet_name']); ?>

                    </p>

                </div>

                <div class="mt-2">

                    <a
                        href="view.php?id=<?= $wallet_id ?>"
                        class="btn btn-primary btn-sm"
                    >

                        <i class="fas fa-wallet mr-1"></i>

                        Wallet

                    </a>

                    <a
                        href="index.php"
                        class="btn btn-secondary btn-sm"
                    >

                        <i class="fas fa-arrow-left mr-1"></i>

                        Back

                    </a>

                </div>

            </div>

        </div>

    </div>


    <!-- SUMMARY -->

    <div class="row">

        <div class="col-md-4">

            <div class="small-box bg-info">

                <div class="inner">

                    <h3>
                        <?= number_format($totalRequired, 2); ?>
                    </h3>

                    <p>Total Required</p>

                </div>

                <div class="icon">

                    <i class="fas fa-file-invoice-dollar"></i>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="small-box bg-success">

                <div class="inner">

                    <h3>
                        <?= number_format($totalPaid, 2); ?>
                    </h3>

                    <p>Total Paid</p>

                </div>

                <div class="icon">

                    <i class="fas fa-money-bill-wave"></i>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="small-box bg-warning">

                <div class="inner">

                    <h3>
                        <?= number_format($totalBalance, 2); ?>
                    </h3>

                    <p>Total Balance</p>

                </div>

                <div class="icon">

                    <i class="fas fa-balance-scale"></i>

                </div>

            </div>

        </div>

    </div>


    <!-- DUES TABLE -->

    <div class="card clan-page-card">

        <div class="card-header">

            <h3 class="card-title">

                <i class="fas fa-list mr-2"></i>

                Member Dues

            </h3>

        </div>


        <div class="card-body">

            <?php if (!empty($dues)): ?>

            <div class="table-responsive">

                <table class="table table-bordered table-striped">

                    <thead>

                        <tr>

                            <th>#</th>

                            <th><?= __('member') ?></th>

                            <th><?= __('month') ?></th>

                            <th><?= __('required') ?></th>

                            <th><?= __('paid') ?></th>

                            <th><?= __('balance') ?></th>

                            <th><?= __('status') ?></th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php foreach ($dues as $index => $due): ?>

                        <tr>

                            <td>
                                <?= $index + 1; ?>
                            </td>

                            <td>

                                <strong>
                                    <?= htmlspecialchars(
                                        trim(
                                            $due['first_name'] . ' ' .
                                            $due['middle_name'] . ' ' .
                                            $due['last_name']
                                        )
                                    ); ?>
                                </strong>

                                <br>

                                <small class="text-muted">

                                    <?= htmlspecialchars(
                                        $due['member_code']
                                    ); ?>

                                </small>

                            </td>

                            <td>

                                <?= htmlspecialchars(
                                    date(
                                        'F Y',
                                        strtotime($due['due_month'])
                                    )
                                ); ?>

                            </td>

                            <td>

                                <?= number_format(
                                    $due['required_amount'],
                                    2
                                ); ?>

                            </td>

                            <td class="text-success">

                                <?= number_format(
                                    $due['paid_amount'],
                                    2
                                ); ?>

                            </td>

                            <td class="text-danger">

                                <?= number_format(
                                    $due['balance'],
                                    2
                                ); ?>

                            </td>

                            <td>

                                <?php if ($due['status'] === 'Completed'): ?>

                                    <span class="badge badge-success">
                                        Completed
                                    </span>

                                <?php elseif ($due['status'] === 'Partial'): ?>

                                    <span class="badge badge-warning">
                                        Partial
                                    </span>

                                <?php else: ?>

                                    <span class="badge badge-secondary">
                                        Pending
                                    </span>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

            <?php else: ?>

                <div class="text-center py-5">

                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>

                    <h5><?= __('no_monthly_dues_found') ?></h5>

                    <p class="text-muted">
                        No dues have been generated for this wallet yet.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

</section>

</div>

<?php

require_once '../../includes/dashboard/footer.php';

?>