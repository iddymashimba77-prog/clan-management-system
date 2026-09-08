<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$pdo = $database->connect();

$stmt = $pdo->query("
    SELECT
        g.generation_id,
        g.generation_name,
        g.description,
        g.created_at,
        CONCAT(
            u.first_name, ' ',
            COALESCE(u.middle_name, ''), ' ',
            u.last_name
        ) AS created_by_name
    FROM generations g
    LEFT JOIN users u
        ON u.user_id = g.created_by_user_id
    ORDER BY g.generation_id ASC
");

$generations = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/admin.php';
require_once '../../includes/dashboard/navbar.php';
?>

<div class="content-wrapper">
<section class="content">
<div class="container-fluid clan-page-content">

<div class="card clan-page-card">

    <div class="card-header d-flex justify-content-between align-items-center">

        <h5 class="mb-0">
            <i class="fas fa-layer-group mr-2 text-primary"></i>
            Family <?= __('generations') ?>
        </h5>

        <a href="index.php" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i>
            Family Tree
        </a>

    </div>

    <div class="card-body">

        <form
            method="POST"
            action="generation_store.php"
            class="mb-4"
        >

            <div class="row">

                <div class="col-md-5">
                    <label>Generation Name</label>

                    <input
                        type="text"
                        name="generation_name"
                        class="form-control"
                        placeholder="e.g. First Generation"
                        required
                    >
                </div>

                <div class="col-md-5">
                    <label><?= __('description') ?></label>

                    <input
                        type="text"
                        name="description"
                        class="form-control"
                        placeholder="Optional description"
                    >
                </div>

                <div class="col-md-2 d-flex align-items-end">

                    <button
                        type="submit"
                        class="btn btn-primary btn-block"
                    >
                        <i class="fas fa-plus mr-1"></i>
                        Add
                    </button>

                </div>

            </div>

        </form>


        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead>

                    <tr>
                        <th>#</th>
                        <th><?= __('generation') ?></th>
                        <th><?= __('description') ?></th>
                        <th>Created By</th>
                        <th>Created At</th>
                    </tr>

                </thead>

                <tbody>

                <?php if (!empty($generations)): ?>

                    <?php foreach ($generations as $i => $generation): ?>

                        <tr>

                            <td><?= $i + 1 ?></td>

                            <td>
                                <strong>
                                    <?= htmlspecialchars(
                                        $generation['generation_name']
                                    ) ?>
                                </strong>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $generation['description']
                                    ?: '—'
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $generation['created_by_name']
                                    ?: 'System'
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $generation['created_at']
                                ) ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            No generations have been created.
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

<?php require_once '../../includes/dashboard/footer.php'; ?>