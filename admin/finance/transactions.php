<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();


/*
|--------------------------------------------------------------------------
| FILTERS
|--------------------------------------------------------------------------
*/

$status = trim($_GET['status'] ?? '');
$search = trim($_GET['search'] ?? '');
$type = trim($_GET['type'] ?? '');

$whereContribution = [];
$paramsContribution = [];

$whereWallet = [];
$paramsWallet = [];


/*
|--------------------------------------------------------------------------
| STATUS FILTER
|--------------------------------------------------------------------------
*/

$allowedStatuses = [
    'pending',
    'approved',
    'rejected'
];

if ($status !== '' && in_array($status, $allowedStatuses, true)) {

    $whereContribution[] = "cp.payment_status = ?";
    $paramsContribution[] = $status;

    $whereWallet[] = "wp.payment_status = ?";
    $paramsWallet[] = $status;

}


/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

if ($search !== '') {

    $searchValue = '%' . $search . '%';


    /*
    |--------------------------------------------------------------------------
    | CONTRIBUTION SEARCH
    |--------------------------------------------------------------------------
    */

    $whereContribution[] = "
        (
            cp.reference_number LIKE ?
            OR u.member_code LIKE ?
            OR u.first_name LIKE ?
            OR u.middle_name LIKE ?
            OR u.last_name LIKE ?
            OR c.title LIKE ?
            OR pm.method_name LIKE ?
        )
    ";

    for ($i = 0; $i < 7; $i++) {

        $paramsContribution[] = $searchValue;

    }


    /*
    |--------------------------------------------------------------------------
    | WALLET SEARCH
    |--------------------------------------------------------------------------
    */

    $whereWallet[] = "
        (
            wp.reference_number LIKE ?
            OR u.member_code LIKE ?
            OR u.first_name LIKE ?
            OR u.middle_name LIKE ?
            OR u.last_name LIKE ?
            OR w.wallet_name LIKE ?
        )
    ";

    for ($i = 0; $i < 6; $i++) {

        $paramsWallet[] = $searchValue;

    }

}


/*
|--------------------------------------------------------------------------
| WHERE
|--------------------------------------------------------------------------
*/

$whereContributionSql = '';

if (!empty($whereContribution)) {

    $whereContributionSql =
        'WHERE ' . implode(' AND ', $whereContribution);

}


$whereWalletSql = '';

if (!empty($whereWallet)) {

    $whereWalletSql =
        'WHERE ' . implode(' AND ', $whereWallet);

}


/*
|--------------------------------------------------------------------------
| TRANSACTIONS ARRAY
|--------------------------------------------------------------------------
*/

$transactions = [];


/*
|--------------------------------------------------------------------------
| CONTRIBUTION PAYMENTS
|--------------------------------------------------------------------------
*/

if ($type === '' || $type === 'contribution') {

    $sql = "

        SELECT

            cp.payment_id AS transaction_id,

            cp.amount,

            cp.payment_date AS transaction_date,

            cp.payment_status AS status,

            'Contribution' AS transaction_type,

            c.title AS source,

            CONCAT(
                u.first_name,
                ' ',
                COALESCE(u.middle_name, ''),
                ' ',
                COALESCE(u.last_name, '')
            ) AS member_name,

            u.member_code,

            cp.reference_number,

            pm.method_name AS payment_method

        FROM contribution_payments cp

        INNER JOIN contributions c
            ON c.contribution_id = cp.contribution_id

        INNER JOIN users u
            ON u.user_id = cp.member_user_id

        LEFT JOIN payment_methods pm
            ON pm.payment_method_id = cp.payment_method_id

        $whereContributionSql

        ORDER BY cp.payment_id DESC

    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute($paramsContribution);

    $contributionTransactions =
        $stmt->fetchAll(PDO::FETCH_ASSOC);

    $transactions = array_merge(
        $transactions,
        $contributionTransactions
    );

}


/*
|--------------------------------------------------------------------------
| WALLET PAYMENTS
|--------------------------------------------------------------------------
*/

if ($type === '' || $type === 'wallet') {

    $sql = "

        SELECT

            wp.payment_id AS transaction_id,

            wp.amount,

            wp.payment_date AS transaction_date,

            wp.payment_status AS status,

            'Wallet' AS transaction_type,

            w.wallet_name AS source,

            CONCAT(
                u.first_name,
                ' ',
                COALESCE(u.middle_name, ''),
                ' ',
                COALESCE(u.last_name, '')
            ) AS member_name,

            u.member_code,

            wp.reference_number,

            NULL AS payment_method

        FROM family_wallet_payments wp

        INNER JOIN family_wallets w
            ON w.wallet_id = wp.wallet_id

        INNER JOIN users u
            ON u.user_id = wp.member_user_id

        $whereWalletSql

        ORDER BY wp.payment_id DESC

    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute($paramsWallet);

    $walletTransactions =
        $stmt->fetchAll(PDO::FETCH_ASSOC);

    $transactions = array_merge(
        $transactions,
        $walletTransactions
    );

}


/*
|--------------------------------------------------------------------------
| SORT ALL TRANSACTIONS
|--------------------------------------------------------------------------
*/

usort(
    $transactions,
    function ($a, $b) {

        return strtotime(
            $b['transaction_date'] ?? '1970-01-01'
        )
        <=>
        strtotime(
            $a['transaction_date'] ?? '1970-01-01'
        );

    }
);


/*
|--------------------------------------------------------------------------
| SUMMARY
|--------------------------------------------------------------------------
*/

$totalTransactions = count($transactions);

$totalAmount = 0;
$approvedAmount = 0;
$pendingAmount = 0;
$rejectedAmount = 0;

foreach ($transactions as $transaction) {

    $amount = (float)$transaction['amount'];

    $totalAmount += $amount;

    if ($transaction['status'] === 'approved') {

        $approvedAmount += $amount;

    } elseif ($transaction['status'] === 'pending') {

        $pendingAmount += $amount;

    } elseif ($transaction['status'] === 'rejected') {

        $rejectedAmount += $amount;

    }

}


/*
|--------------------------------------------------------------------------
| DASHBOARD
|--------------------------------------------------------------------------
*/

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/admin.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">


    <div class="card clan-page-card mb-3">

        <div class="card-body">

            <h3 class="mb-1">

                <i class="fas fa-exchange-alt text-primary mr-2"></i>

                <?= __('financial_transactions') ?>

            </h3>

            <p class="text-muted mb-0">

                <?= __('view_and_monitor_all_contribution_and_wallet_transactions') ?>

            </p>

        </div>

    </div>


    <!-- SUMMARY -->

    <div class="row">


        <div class="col-lg-3 col-md-6">

            <div class="small-box bg-info">

                <div class="inner">

                    <h3>
                        <?= $totalTransactions ?>
                    </h3>

                    <p>Total Transactions</p>

                </div>

                <div class="icon">

                    <i class="fas fa-exchange-alt"></i>

                </div>

            </div>

        </div>


        <div class="col-lg-3 col-md-6">

            <div class="small-box bg-primary">

                <div class="inner">

                    <h3>
                        <?= number_format($totalAmount, 2) ?>
                    </h3>

                    <p>Total Amount</p>

                </div>

                <div class="icon">

                    <i class="fas fa-money-bill-wave"></i>

                </div>

            </div>

        </div>


        <div class="col-lg-3 col-md-6">

            <div class="small-box bg-success">

                <div class="inner">

                    <h3>
                        <?= number_format($approvedAmount, 2) ?>
                    </h3>

                    <p>Approved Amount</p>

                </div>

                <div class="icon">

                    <i class="fas fa-check-circle"></i>

                </div>

            </div>

        </div>


        <div class="col-lg-3 col-md-6">

            <div class="small-box bg-warning">

                <div class="inner">

                    <h3>
                        <?= number_format($pendingAmount, 2) ?>
                    </h3>

                    <p>Pending Amount</p>

                </div>

                <div class="icon">

                    <i class="fas fa-clock"></i>

                </div>

            </div>

        </div>

    </div>


    <!-- FILTER -->

    <div class="card clan-page-card mb-3">

        <div class="card-header">

            <h5 class="mb-0">

                <i class="fas fa-filter mr-2 text-primary"></i>

                <?= __('filter_transactions') ?>

            </h5>

        </div>


        <div class="card-body">

            <form method="GET">

                <div class="row">


                    <div class="col-md-4 mb-2">

                        <label><?= __('search') ?></label>

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Member name, member code, source or reference..."
                            value="<?= htmlspecialchars($search) ?>"
                        >

                    </div>


                    <div class="col-md-3 mb-2">

                        <label>Transaction Type</label>

                        <select
                            name="type"
                            class="form-control"
                        >

                            <option value="">
                                <?= __('all_transactions') ?>
                            </option>

                            <option
                                value="contribution"
                                <?= $type === 'contribution' ? 'selected' : '' ?>
                            >
                                Contribution
                            </option>

                            <option
                                value="wallet"
                                <?= $type === 'wallet' ? 'selected' : '' ?>
                            >
                                Wallet
                            </option>

                        </select>

                    </div>


                    <div class="col-md-2 mb-2">

                        <label><?= __('status') ?></label>

                        <select
                            name="status"
                            class="form-control"
                        >

                            <option value="">
                                All
                            </option>

                            <option
                                value="pending"
                                <?= $status === 'pending' ? 'selected' : '' ?>
                            >
                                Pending
                            </option>

                            <option
                                value="approved"
                                <?= $status === 'approved' ? 'selected' : '' ?>
                            >
                                Approved
                            </option>

                            <option
                                value="rejected"
                                <?= $status === 'rejected' ? 'selected' : '' ?>
                            >
                                Rejected
                            </option>

                        </select>

                    </div>


                    <div class="col-md-3 mb-2 d-flex align-items-end">

                        <button
                            type="submit"
                            class="btn btn-primary mr-2"
                        >

                            <i class="fas fa-search mr-1"></i>

                            Search

                        </button>


                        <a
                            href="transactions.php"
                            class="btn btn-secondary"
                        >

                            <i class="fas fa-sync mr-1"></i>

                            <?= __('reset') ?>

                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- TABLE -->

    <div class="card clan-page-card">

        <div class="card-header">

            <h5 class="mb-0">

                <i class="fas fa-list mr-2 text-primary"></i>

                <?= __('financial_transaction_history') ?>

            </h5>

        </div>


        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover table-striped">

                    <thead>

                        <tr>

                            <th>#</th>

                            <th><?= __('reference') ?></th>

                            <th><?= __('type') ?></th>

                            <th><?= __('source') ?></th>

                            <th><?= __('member') ?></th>

                            <th><?= __('amount') ?></th>

                            <th><?= __('status') ?></th>

                            <th><?= __('date') ?></th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (!empty($transactions)): ?>


                        <?php foreach ($transactions as $index => $transaction): ?>


                            <tr>


                                <td>
                                    <?= $index + 1 ?>
                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $transaction['reference_number']
                                            ?? 'N/A'
                                    ) ?>

                                </td>


                                <td>

                                    <?php if (
                                        $transaction['transaction_type']
                                        === 'Wallet'
                                    ): ?>

                                        <span class="badge badge-info">

                                            <i class="fas fa-wallet mr-1"></i>

                                            Wallet

                                        </span>

                                    <?php else: ?>

                                        <span class="badge badge-primary">

                                            <i class="fas fa-hand-holding-usd mr-1"></i>

                                            Contribution

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $transaction['source']
                                    ) ?>

                                </td>


                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $transaction['member_name']
                                        ) ?>

                                    </strong>

                                    <br>

                                    <small class="text-muted">

                                        <?= htmlspecialchars(
                                            $transaction['member_code']
                                        ) ?>

                                    </small>

                                </td>


                                <td>

                                    <strong>

                                        <?= number_format(
                                            (float)$transaction['amount'],
                                            2
                                        ) ?>

                                    </strong>

                                </td>


                                <td>

                                    <?php if (
                                        $transaction['status']
                                        === 'approved'
                                    ): ?>

                                        <span class="badge badge-success">
                                            Approved
                                        </span>

                                    <?php elseif (
                                        $transaction['status']
                                        === 'rejected'
                                    ): ?>

                                        <span class="badge badge-danger">
                                            Rejected
                                        </span>

                                    <?php else: ?>

                                        <span class="badge badge-warning">
                                            Pending
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $transaction['transaction_date']
                                            ?? ''
                                    ) ?>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="8"
                                class="text-center text-muted py-5"
                            >

                                <i class="fas fa-exchange-alt fa-2x mb-2"></i>

                                <br>

                                <?= __('no_transactions_found') ?>

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

require_once '../../includes/dashboard/footer.php';

?>