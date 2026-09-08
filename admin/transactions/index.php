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
$walletId = (int)($_GET['wallet_id'] ?? 0);

$where = [];
$params = [];


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

    $where[] = "wp.payment_status = ?";
    $params[] = $status;

}


/*
|--------------------------------------------------------------------------
| WALLET FILTER
|--------------------------------------------------------------------------
*/

if ($walletId > 0) {

    $where[] = "wp.wallet_id = ?";
    $params[] = $walletId;

}


/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

if ($search !== '') {

    $where[] = "
        (
            wp.reference_number LIKE ?
            OR u.member_code LIKE ?
            OR u.first_name LIKE ?
            OR u.middle_name LIKE ?
            OR u.last_name LIKE ?
            OR w.wallet_name LIKE ?
        )
    ";

    $searchValue = '%' . $search . '%';

    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;

}


/*
|--------------------------------------------------------------------------
| WHERE
|--------------------------------------------------------------------------
*/

$whereSql = '';

if (!empty($where)) {

    $whereSql = 'WHERE ' . implode(' AND ', $where);

}


/*
|--------------------------------------------------------------------------
| WALLETS
|--------------------------------------------------------------------------
*/

$walletStmt = $pdo->query("
    SELECT
        wallet_id,
        wallet_name
    FROM family_wallets
    ORDER BY wallet_name ASC
");

$wallets = $walletStmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| WALLET TRANSACTIONS
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT

        wp.payment_id,

        wp.wallet_id,

        wp.member_user_id,

        wp.amount,

        wp.payment_month,

        wp.reference_number,

        wp.payment_proof,

        wp.payment_status,

        wp.secretary_status,

        wp.treasurer_status,

        wp.chairperson_status,

        wp.approved_by_treasurer,

        wp.approved_by_chairperson,

        wp.treasurer_date,

        wp.chairperson_date,

        wp.payment_date,

        w.wallet_name,

        u.member_code,

        u.first_name,

        u.middle_name,

        u.last_name

    FROM family_wallet_payments wp

    INNER JOIN family_wallets w
        ON w.wallet_id = wp.wallet_id

    INNER JOIN users u
        ON u.user_id = wp.member_user_id

    $whereSql

    ORDER BY wp.payment_id DESC
";


$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);


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

    if ($transaction['payment_status'] === 'approved') {

        $approvedAmount += $amount;

    } elseif ($transaction['payment_status'] === 'pending') {

        $pendingAmount += $amount;

    } elseif ($transaction['payment_status'] === 'rejected') {

        $rejectedAmount += $amount;

    }

}


/*
|--------------------------------------------------------------------------
| DASHBOARD LAYOUT
|--------------------------------------------------------------------------
*/

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/admin.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">


    <!-- =========================================================
         PAGE HEADER
    ========================================================== -->

    <div class="card clan-page-card mb-3">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center flex-wrap">

                <div>

                    <h3 class="mb-1">

                        <i class="fas fa-wallet text-primary mr-2"></i>

                        <?= __('wallet_transactions') ?>

                    </h3>

                    <p class="text-muted mb-0">

                        <?= __('view_and_monitor_all_family_wallet_payment_transactions') ?>

                    </p>

                </div>

            </div>

        </div>

    </div>


    <!-- =========================================================
         SUMMARY
    ========================================================== -->

    <div class="row">


        <!-- TOTAL TRANSACTIONS -->

        <div class="col-lg-3 col-md-6">

            <div class="small-box bg-info">

                <div class="inner">

                    <h3>
                        <?= $totalTransactions ?>
                    </h3>

                    <p>
                        <?= __('total_transactions') ?>
                    </p>

                </div>

                <div class="icon">

                    <i class="fas fa-exchange-alt"></i>

                </div>

            </div>

        </div>


        <!-- TOTAL AMOUNT -->

        <div class="col-lg-3 col-md-6">

            <div class="small-box bg-primary">

                <div class="inner">

                    <h3>
                        <?= number_format($totalAmount, 2) ?>
                    </h3>

                    <p>
                        <?= __('total_amount') ?>
                    </p>

                </div>

                <div class="icon">

                    <i class="fas fa-money-bill-wave"></i>

                </div>

            </div>

        </div>


        <!-- APPROVED -->

        <div class="col-lg-3 col-md-6">

            <div class="small-box bg-success">

                <div class="inner">

                    <h3>
                        <?= number_format($approvedAmount, 2) ?>
                    </h3>

                    <p>
                        <?= __('approved_amount') ?>
                    </p>

                </div>

                <div class="icon">

                    <i class="fas fa-check-circle"></i>

                </div>

            </div>

        </div>


        <!-- PENDING -->

        <div class="col-lg-3 col-md-6">

            <div class="small-box bg-warning">

                <div class="inner">

                    <h3>
                        <?= number_format($pendingAmount, 2) ?>
                    </h3>

                    <p>
                        <?= __('pending_amount') ?>
                    </p>

                </div>

                <div class="icon">

                    <i class="fas fa-clock"></i>

                </div>

            </div>

        </div>

    </div>


    <!-- =========================================================
         FILTER
    ========================================================== -->

    <div class="card clan-page-card mb-3">

        <div class="card-header">

            <h5 class="mb-0">

                <i class="fas fa-filter mr-2 text-primary"></i>

                Filter <?= __('wallet_transactions') ?>

            </h5>

        </div>


        <div class="card-body">

            <form method="GET">

                <div class="row">


                    <!-- SEARCH -->

                    <div class="col-md-4 mb-2">

                        <label>
                            Search
                        </label>

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Member name, member code, wallet or reference..."
                            value="<?= htmlspecialchars($search) ?>"
                        >

                    </div>


                    <!-- WALLET -->

                    <div class="col-md-3 mb-2">

                        <label>
                            Wallet
                        </label>

                        <select
                            name="wallet_id"
                            class="form-control"
                        >

                            <option value="">
                                <?= __('all_wallets') ?>
                            </option>

                            <?php foreach ($wallets as $wallet): ?>

                                <option
                                    value="<?= (int)$wallet['wallet_id'] ?>"
                                    <?= $walletId === (int)$wallet['wallet_id'] ? 'selected' : '' ?>
                                >

                                    <?= htmlspecialchars($wallet['wallet_name']) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- STATUS -->

                    <div class="col-md-2 mb-2">

                        <label>
                            Status
                        </label>

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


                    <!-- BUTTONS -->

                    <div class="col-md-3 mb-2 d-flex align-items-end">

                        <button
                            type="submit"
                            class="btn btn-primary mr-2"
                        >

                            <i class="fas fa-search mr-1"></i>

                            Search

                        </button>


                        <a
                            href="index.php"
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


    <!-- =========================================================
         TRANSACTION TABLE
    ========================================================== -->

    <div class="card clan-page-card">

        <div class="card-header">

            <h5 class="mb-0">

                <i class="fas fa-list mr-2 text-primary"></i>

                <?= __('wallet_transaction_history') ?>

            </h5>

        </div>


        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover table-striped">

                    <thead>

                        <tr>

                            <th>#</th>

                            <th><?= __('reference') ?></th>

                            <th><?= __('member') ?></th>

                            <th><?= __('wallet') ?></th>

                            <th><?= __('amount') ?></th>

                            <th>Payment Month</th>

                            <th><?= __('status') ?></th>

                            <th>Payment Date</th>

                            <th width="80">
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (!empty($transactions)): ?>


                        <?php foreach ($transactions as $index => $transaction): ?>


                            <?php

                            $fullName = trim(
                                ($transaction['first_name'] ?? '') . ' ' .
                                ($transaction['middle_name'] ?? '') . ' ' .
                                ($transaction['last_name'] ?? '')
                            );

                            ?>


                            <tr>


                                <td>
                                    <?= $index + 1 ?>
                                </td>


                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $transaction['reference_number']
                                                ?? 'N/A'
                                        ) ?>

                                    </strong>

                                </td>


                                <td>

                                    <strong>

                                        <?= htmlspecialchars($fullName) ?>

                                    </strong>

                                    <br>

                                    <small class="text-muted">

                                        <?= htmlspecialchars(
                                            $transaction['member_code'] ?? ''
                                        ) ?>

                                    </small>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $transaction['wallet_name']
                                    ) ?>

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

                                    <?= htmlspecialchars(
                                        $transaction['payment_month']
                                    ) ?>

                                </td>


                                <td>


                                    <?php if (
                                        $transaction['payment_status']
                                        === 'approved'
                                    ): ?>

                                        <span class="badge badge-success">

                                            <i class="fas fa-check mr-1"></i>

                                            Approved

                                        </span>


                                    <?php elseif (
                                        $transaction['payment_status']
                                        === 'rejected'
                                    ): ?>

                                        <span class="badge badge-danger">

                                            <i class="fas fa-times mr-1"></i>

                                            Rejected

                                        </span>


                                    <?php else: ?>

                                        <span class="badge badge-warning">

                                            <i class="fas fa-clock mr-1"></i>

                                            Pending

                                        </span>

                                    <?php endif; ?>


                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $transaction['payment_date']
                                    ) ?>

                                </td>


                                <td>

                                    <a
                                        href="view.php?id=<?= (int)$transaction['payment_id'] ?>"
                                        class="btn btn-sm btn-info"
                                        title="View Transaction"
                                    >

                                        <i class="fas fa-eye"></i>

                                    </a>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="9"
                                class="text-center text-muted py-5"
                            >

                                <i class="fas fa-wallet fa-2x mb-2"></i>

                                <br>

                                <?= __('no_wallet_transactions_found') ?>

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