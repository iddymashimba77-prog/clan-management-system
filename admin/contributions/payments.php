<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$contribution_id = (int)($_GET['contribution_id'] ?? 0);

$where = '';
$params = [];

if ($contribution_id > 0) {
    $where = 'WHERE cp.contribution_id = ?';
    $params[] = $contribution_id;
}


/* =========================================================
   FETCH PAYMENTS
========================================================= */

$stmt = $pdo->prepare("
    SELECT
        cp.*,

        c.title AS contribution_title,

        u.member_code,
        u.first_name,
        u.middle_name,
        u.last_name,

        pm.method_name

    FROM contribution_payments cp

    INNER JOIN contributions c
        ON c.contribution_id = cp.contribution_id

    INNER JOIN users u
        ON u.user_id = cp.member_user_id

    LEFT JOIN payment_methods pm
        ON pm.payment_method_id = cp.payment_method_id

    $where

    ORDER BY cp.payment_id DESC
");

$stmt->execute($params);

$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================================================
   DASHBOARD LAYOUT
========================================================= */

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/admin.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

    <section class="content">

        <div class="container-fluid clan-page-content">

            <!-- PAGE HEADER -->

            <div class="card clan-page-card mb-3">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center flex-wrap">

                        <div>

                            <h3 class="mb-1">

                                <i class="fas fa-money-bill-wave text-primary mr-2"></i>

                                <?= __('contribution_payments') ?>

                            </h3>

                            <p class="text-muted mb-0">

                                View and manage contribution payments.

                            </p>

                        </div>


                        <div class="mt-2 mt-md-0">

                            <a
                                href="index.php"
                                class="btn btn-secondary"
                            >

                                <i class="fas fa-arrow-left mr-1"></i>

                                Contributions

                            </a>

                        </div>

                    </div>

                </div>

            </div>


            <!-- SUCCESS MESSAGE -->

            <?php if (!empty($_GET['success'])): ?>

                <div class="alert alert-success alert-dismissible fade show">

                    <i class="fas fa-check-circle mr-2"></i>

                    <?= htmlspecialchars($_GET['success']) ?>

                    <button
                        type="button"
                        class="close"
                        data-dismiss="alert"
                    >
                        <span>&times;</span>
                    </button>

                </div>

            <?php endif; ?>


            <!-- ERROR MESSAGE -->

            <?php if (!empty($_GET['error'])): ?>

                <div class="alert alert-danger alert-dismissible fade show">

                    <i class="fas fa-exclamation-circle mr-2"></i>

                    <?= htmlspecialchars($_GET['error']) ?>

                    <button
                        type="button"
                        class="close"
                        data-dismiss="alert"
                    >
                        <span>&times;</span>
                    </button>

                </div>

            <?php endif; ?>


            <!-- PAYMENTS TABLE -->

            <div class="card clan-page-card">

                <div class="card-header">

                    <h5 class="mb-0">

                        <i class="fas fa-list mr-2 text-primary"></i>

                        Payment Records

                    </h5>

                </div>


                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover table-striped">

                            <thead>

                                <tr>

                                    <th>#</th>

                                    <th><?= __('contribution') ?></th>

                                    <th><?= __('member') ?></th>

                                    <th><?= __('amount') ?></th>

                                    <th>Payment Method</th>

                                    <th><?= __('reference') ?></th>

                                    <th><?= __('status') ?></th>

                                    <th>Payment Date</th>

                                    <th width="150"><?= __('actions') ?></th>

                                </tr>

                            </thead>


                            <tbody>

                            <?php if (!empty($payments)): ?>

                                <?php foreach ($payments as $payment): ?>

                                    <?php

                                    $fullName = trim(
                                        ($payment['first_name'] ?? '') . ' ' .
                                        ($payment['middle_name'] ?? '') . ' ' .
                                        ($payment['last_name'] ?? '')
                                    );

                                    ?>


                                    <tr>

                                        <!-- ID -->

                                        <td>

                                            <?= (int)$payment['payment_id'] ?>

                                        </td>


                                        <!-- CONTRIBUTION -->

                                        <td>

                                            <strong>

                                                <?= htmlspecialchars(
                                                    $payment['contribution_title'] ?? 'N/A'
                                                ) ?>

                                            </strong>

                                        </td>


                                        <!-- MEMBER -->

                                        <td>

                                            <strong>

                                                <?= htmlspecialchars(
                                                    $fullName ?: 'Unknown Member'
                                                ) ?>

                                            </strong>

                                            <br>

                                            <small class="text-muted">

                                                <?= htmlspecialchars(
                                                    $payment['member_code'] ?? ''
                                                ) ?>

                                            </small>

                                        </td>


                                        <!-- AMOUNT -->

                                        <td>

                                            <strong>

                                                <?= number_format(
                                                    (float)$payment['amount'],
                                                    2
                                                ) ?>

                                            </strong>

                                        </td>


                                        <!-- PAYMENT METHOD -->

                                        <td>

                                            <?= htmlspecialchars(
                                                $payment['method_name'] ?? 'N/A'
                                            ) ?>

                                        </td>


                                        <!-- REFERENCE -->

                                        <td>

                                            <?= htmlspecialchars(
                                                $payment['reference_number'] ?? 'N/A'
                                            ) ?>

                                        </td>


                                        <!-- STATUS -->

                                        <td>

                                            <?php

                                            $status =
                                                $payment['payment_status']
                                                ?? 'pending';

                                            ?>


                                            <?php if ($status === 'approved'): ?>

                                                <span class="badge badge-success">

                                                    Approved

                                                </span>

                                            <?php elseif ($status === 'rejected'): ?>

                                                <span class="badge badge-danger">

                                                    Rejected

                                                </span>

                                            <?php else: ?>

                                                <span class="badge badge-warning">

                                                    Pending

                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <!-- DATE -->

                                        <td>

                                            <?= htmlspecialchars(
                                                $payment['payment_date']
                                                ?? $payment['created_at']
                                                ?? ''
                                            ) ?>

                                        </td>


                                        <!-- ACTIONS -->

                                        <td>

                                            <a
                                                href="payment_view.php?id=<?= (int)$payment['payment_id'] ?>"
                                                class="btn btn-sm btn-info"
                                                title="View Payment"
                                            >

                                                <i class="fas fa-eye"></i>

                                            </a>


                                            <?php if ($status === 'pending'): ?>

                                                <a
                                                    href="approve_payment.php?id=<?= (int)$payment['payment_id'] ?>"
                                                    class="btn btn-sm btn-success"
                                                    title="Approve"
                                                    onclick="return confirm('Approve this payment?');"
                                                >

                                                    <i class="fas fa-check"></i>

                                                </a>


                                                <a
                                                    href="reject_payment.php?id=<?= (int)$payment['payment_id'] ?>"
                                                    class="btn btn-sm btn-danger"
                                                    title="Reject"
                                                    onclick="return confirm('Reject this payment?');"
                                                >

                                                    <i class="fas fa-times"></i>

                                                </a>

                                            <?php endif; ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>


                            <?php else: ?>

                                <tr>

                                    <td
                                        colspan="9"
                                        class="text-center text-muted py-5"
                                    >

                                        <i class="fas fa-money-bill-wave fa-3x mb-3"></i>

                                        <br>

                                        <strong>No payment records found.</strong>

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