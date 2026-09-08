<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$database = new Database();
$pdo = $database->connect();


/* =========================================================
   FETCH CONTRIBUTION TYPES
========================================================= */

$stmt = $pdo->query("
    SELECT
        ct.contribution_type_id,
        ct.type_name,
        ct.description,
        ct.status,
        ct.created_at,

        COUNT(c.contribution_id) AS total_contributions

    FROM contribution_types ct

    LEFT JOIN contributions c
        ON c.contribution_type_id = ct.contribution_type_id

    GROUP BY
        ct.contribution_type_id,
        ct.type_name,
        ct.description,
        ct.status,
        ct.created_at

    ORDER BY ct.type_name ASC
");

$types = $stmt->fetchAll(PDO::FETCH_ASSOC);


require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/admin.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">

    <div class="card clan-page-card mb-3">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center flex-wrap">

                <div>

                    <h3 class="mb-1">

                        <i class="fas fa-layer-group text-primary mr-2"></i>

                        <?= __('contribution_types') ?>

                    </h3>

                    <p class="text-muted mb-0">

                        Manage categories used to classify contributions.

                    </p>

                </div>

                <div class="mt-2 mt-md-0">

                    <a
                        href="create.php"
                        class="btn btn-primary"
                    >

                        <i class="fas fa-plus mr-1"></i>

                        Add Contribution Type

                    </a>

                </div>

            </div>

        </div>

    </div>


    <?php if (!empty($_SESSION['success'])): ?>

        <div class="alert alert-success alert-dismissible fade show">

            <i class="fas fa-check-circle mr-2"></i>

            <?= htmlspecialchars($_SESSION['success']) ?>

            <button
                type="button"
                class="close"
                data-dismiss="alert"
            >

                <span>&times;</span>

            </button>

        </div>

        <?php unset($_SESSION['success']); ?>

    <?php endif; ?>


    <?php if (!empty($_SESSION['error'])): ?>

        <div class="alert alert-danger alert-dismissible fade show">

            <i class="fas fa-exclamation-circle mr-2"></i>

            <?= htmlspecialchars($_SESSION['error']) ?>

            <button
                type="button"
                class="close"
                data-dismiss="alert"
            >

                <span>&times;</span>

            </button>

        </div>

        <?php unset($_SESSION['error']); ?>

    <?php endif; ?>


    <div class="card clan-page-card">

        <div class="card-header">

            <h5 class="mb-0">

                <i class="fas fa-list mr-2 text-primary"></i>

                Available <?= __('contribution_types') ?>

            </h5>

        </div>


        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover">

                    <thead>

                        <tr>

                            <th>#</th>

                            <th>Type Name</th>

                            <th><?= __('description') ?></th>

                            <th><?= __('contributions') ?></th>

                            <th><?= __('status') ?></th>

                            <th><?= __('created') ?></th>

                            <th width="180"><?= __('actions') ?></th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php if (!empty($types)): ?>

                        <?php foreach ($types as $index => $type): ?>

                            <tr>

                                <td>
                                    <?= $index + 1 ?>
                                </td>


                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $type['type_name']
                                        ) ?>

                                    </strong>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $type['description'] ?? 'No description'
                                    ) ?>

                                </td>


                                <td>

                                    <span class="badge badge-info">

                                        <?= (int)$type['total_contributions'] ?>

                                    </span>

                                </td>


                                <td>

                                    <?php if ($type['status'] === 'active'): ?>

                                        <span class="badge badge-success">

                                            Active

                                        </span>

                                    <?php else: ?>

                                        <span class="badge badge-secondary">

                                            Inactive

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $type['created_at']
                                    ) ?>

                                </td>


                                <td>

                                    <a
                                        href="edit.php?id=<?= (int)$type['contribution_type_id'] ?>"
                                        class="btn btn-sm btn-warning"
                                        title="Edit"
                                    >

                                        <i class="fas fa-edit"></i>

                                    </a>


                                    <a
                                        href="delete.php?id=<?= (int)$type['contribution_type_id'] ?>"
                                        class="btn btn-sm btn-danger"
                                        title="Delete"
                                        onclick="return confirm('Delete this contribution type? Existing contributions will remain safe.');"
                                    >

                                        <i class="fas fa-trash"></i>

                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td
                                colspan="7"
                                class="text-center text-muted py-5"
                            >

                                <?= __('no_contribution_types_found') ?>

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