<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$paymentId = (int)($_GET['id'] ?? 0);

if ($paymentId <= 0) {

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| GET TRANSACTION
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT

        cp.*,

        c.title AS contribution_title,
        c.description AS contribution_description,

        u.member_code,
        u.first_name,
        u.middle_name,
        u.last_name,
        u.phone,
        u.email,

        pm.method_name,
        pm.provider_name,
        pm.phone_number AS payment_phone,
        pm.bank_name,
        pm.account_name,
        pm.account_number

    FROM contribution_payments cp

    INNER JOIN contributions c
        ON c.contribution_id = cp.contribution_id

    INNER JOIN users u
        ON u.user_id = cp.member_user_id

    LEFT JOIN payment_methods pm
        ON pm.payment_method_id = cp.payment_method_id

    WHERE cp.payment_id = ?

    LIMIT 1
");

$stmt->execute([$paymentId]);

$transaction = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$transaction) {

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| MEMBER NAME
|--------------------------------------------------------------------------
*/

$fullName = trim(
    ($transaction['first_name'] ?? '') . ' ' .
    ($transaction['middle_name'] ?? '') . ' ' .
    ($transaction['last_name'] ?? '')
);


/*
|--------------------------------------------------------------------------
| LAYOUT
|--------------------------------------------------------------------------
*/

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/admin.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

    <section class="content">

        <div class="container-fluid clan-page-content">


            <!-- HEADER -->

            <div class="d-flex justify-content-between align-items-center mb-3">

                <div>

                    <h3 class="mb-1">

                        <i class="fas fa-receipt text-primary mr-2"></i>

                        <?= __('transaction_details') ?>

                    </h3>

                    <small class="text-muted">

                        Payment transaction #<?= $paymentId ?>

                    </small>

                </div>


                <a
                    href="index.php"
                    class="btn btn-secondary"
                >

                    <i class="fas fa-arrow-left mr-1"></i>

                    <?= __('back_to_transactions') ?>

                </a>

            </div>


            <!-- STATUS -->

            <div class="card clan-page-card mb-3">

                <div class="card-body text-center">


                    <?php if ($transaction['payment_status'] === 'approved'): ?>

                        <span class="badge badge-success p-2">

                            <i class="fas fa-check-circle mr-1"></i>

                            APPROVED

                        </span>


                    <?php elseif ($transaction['payment_status'] === 'rejected'): ?>

                        <span class="badge badge-danger p-2">

                            <i class="fas fa-times-circle mr-1"></i>

                            REJECTED

                        </span>


                    <?php else: ?>

                        <span class="badge badge-warning p-2">

                            <i class="fas fa-clock mr-1"></i>

                            PENDING

                        </span>

                    <?php endif; ?>


                </div>

            </div>


            <div class="row">


                <!-- TRANSACTION -->

                <div class="col-md-6">

                    <div class="card clan-page-card">

                        <div class="card-header">

                            <h5 class="mb-0">

                                <i class="fas fa-money-bill-wave text-primary mr-2"></i>

                                <?= __('payment_information') ?>

                            </h5>

                        </div>


                        <div class="card-body">


                            <table class="table table-bordered">

                                <tr>

                                    <th width="40%">
                                        <?= __('transaction_id') ?>
                                    </th>

                                    <td>
                                        <?= $paymentId ?>
                                    </td>

                                </tr>


                                <tr>

                                    <th>
                                        <?= __('reference_number_label2') ?>
                                    </th>

                                    <td>

                                        <?= htmlspecialchars(
                                            $transaction['reference_number'] ?? 'N/A'
                                        ) ?>

                                    </td>

                                </tr>


                                <tr>

                                    <th>
                                        <?= __('amount_label') ?>
                                    </th>

                                    <td>

                                        <strong>

                                            <?= number_format(
                                                (float)$transaction['amount'],
                                                2
                                            ) ?>

                                        </strong>

                                    </td>

                                </tr>


                                <tr>

                                    <th>
                                        <?= __('payment_method_label2') ?>
                                    </th>

                                    <td>

                                        <?= htmlspecialchars(
                                            $transaction['method_name'] ?? 'N/A'
                                        ) ?>

                                    </td>

                                </tr>


                                <tr>

                                    <th>
                                        <?= __('provider') ?>
                                    </th>

                                    <td>

                                        <?= htmlspecialchars(
                                            $transaction['provider_name'] ?? 'N/A'
                                        ) ?>

                                    </td>

                                </tr>


                                <tr>

                                    <th>
                                        <?= __('payment_date_label') ?>
                                    </th>

                                    <td>

                                        <?= htmlspecialchars(
                                            $transaction['payment_date']
                                                ?: $transaction['submitted_at']
                                        ) ?>

                                    </td>

                                </tr>


                                <tr>

                                    <th>
                                        <?= __('created_at') ?>
                                    </th>

                                    <td>

                                        <?= htmlspecialchars(
                                            $transaction['created_at'] ?? ''
                                        ) ?>

                                    </td>

                                </tr>

                            </table>


                        </div>

                    </div>

                </div>


                <!-- MEMBER -->

                <div class="col-md-6">

                    <div class="card clan-page-card">

                        <div class="card-header">

                            <h5 class="mb-0">

                                <i class="fas fa-user text-primary mr-2"></i>

                                <?= __('member_information') ?>

                            </h5>

                        </div>


                        <div class="card-body">


                            <table class="table table-bordered">


                                <tr>

                                    <th width="40%">
                                        <?= __('member_code_label') ?>
                                    </th>

                                    <td>

                                        <?= htmlspecialchars(
                                            $transaction['member_code']
                                        ) ?>

                                    </td>

                                </tr>


                                <tr>

                                    <th>
                                        <?= __('full_name_label') ?>
                                    </th>

                                    <td>

                                        <?= htmlspecialchars($fullName) ?>

                                    </td>

                                </tr>


                                <tr>

                                    <th>
                                        <?= __('phone_label') ?>
                                    </th>

                                    <td>

                                        <?= htmlspecialchars(
                                            $transaction['phone'] ?? 'N/A'
                                        ) ?>

                                    </td>

                                </tr>


                                <tr>

                                    <th>
                                        <?= __('email_label') ?>
                                    </th>

                                    <td>

                                        <?= htmlspecialchars(
                                            $transaction['email'] ?? 'N/A'
                                        ) ?>

                                    </td>

                                </tr>


                                <tr>

                                    <th>
                                        <?= __('contribution_label2') ?>
                                    </th>

                                    <td>

                                        <?= htmlspecialchars(
                                            $transaction['contribution_title']
                                        ) ?>

                                    </td>

                                </tr>


                            </table>


                        </div>

                    </div>

                </div>


            </div>


            <!-- APPROVAL WORKFLOW -->

            <div class="card clan-page-card mt-3">

                <div class="card-header">

                    <h5 class="mb-0">

                        <i class="fas fa-tasks text-primary mr-2"></i>

                        <?= __('approval_workflow') ?>

                    </h5>

                </div>


                <div class="card-body">

                    <div class="row">


                        <div class="col-md-3 text-center">

                            <strong>
                                <?= __('secretary_label') ?>
                            </strong>

                            <br>

                            <span class="badge badge-info mt-2">

                                <?= htmlspecialchars(
                                    $transaction['secretary_status'] ?? 'Pending'
                                ) ?>

                            </span>

                        </div>


                        <div class="col-md-3 text-center">

                            <strong>
                                <?= __('treasurer_label') ?>
                            </strong>

                            <br>

                            <span class="badge badge-primary mt-2">

                                <?= htmlspecialchars(
                                    $transaction['treasurer_status'] ?? 'Pending'
                                ) ?>

                            </span>

                        </div>


                        <div class="col-md-3 text-center">

                            <strong>
                                <?= __('chairperson_label') ?>
                            </strong>

                            <br>

                            <span class="badge badge-warning mt-2">

                                <?= htmlspecialchars(
                                    $transaction['chairperson_status'] ?? 'Pending'
                                ) ?>

                            </span>

                        </div>


                        <div class="col-md-3 text-center">

                            <strong>
                                <?= __('payment_status_label') ?>
                            </strong>

                            <br>


                            <?php if ($transaction['payment_status'] === 'approved'): ?>

                                <span class="badge badge-success mt-2">

                                    Approved

                                </span>


                            <?php elseif ($transaction['payment_status'] === 'rejected'): ?>

                                <span class="badge badge-danger mt-2">

                                    Rejected

                                </span>


                            <?php else: ?>

                                <span class="badge badge-secondary mt-2">

                                    Pending

                                </span>

                            <?php endif; ?>


                        </div>


                    </div>

                </div>

            </div>


            <!-- REMARKS -->

            <?php if (!empty($transaction['remarks'])): ?>

                <div class="card clan-page-card mt-3">

                    <div class="card-header">

                        <h5 class="mb-0">

                            <i class="fas fa-comment-alt text-primary mr-2"></i>

                            <?= __('remarks') ?>

                        </h5>

                    </div>


                    <div class="card-body">

                        <?= nl2br(
                            htmlspecialchars($transaction['remarks'])
                        ) ?>

                    </div>

                </div>

            <?php endif; ?>


        </div>

    </section>

</div>


<?php

require_once '../../includes/dashboard/footer.php';

?>