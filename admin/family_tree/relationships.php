<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$pdo = $database->connect();

$stmt = $pdo->query("
    SELECT
        fr.relationship_id,
        fr.member_user_id,
        fr.father_user_id,
        fr.mother_user_id,
        fr.child_type,
        fr.created_at,

        CONCAT(
            child.first_name, ' ',
            COALESCE(child.middle_name, ''), ' ',
            child.last_name
        ) AS child_name,
        child.member_code AS child_code,

        CONCAT(
            father.first_name, ' ',
            COALESCE(father.middle_name, ''), ' ',
            father.last_name
        ) AS father_name,

        CONCAT(
            mother.first_name, ' ',
            COALESCE(mother.middle_name, ''), ' ',
            mother.last_name
        ) AS mother_name

    FROM family_relationships fr

    INNER JOIN users child
        ON child.user_id = fr.member_user_id

    LEFT JOIN users father
        ON father.user_id = fr.father_user_id

    LEFT JOIN users mother
        ON mother.user_id = fr.mother_user_id

    ORDER BY fr.relationship_id DESC
");

$relationships = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            <i class="fas fa-sitemap mr-2 text-primary"></i>
            Family <?= __('relationships') ?>
        </h5>

        <a href="index.php" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i>
            Family Tree
        </a>

    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead>
                    <tr>
                        <th>#</th>
                        <th><?= __('child') ?></th>
                        <th><?= __('father') ?></th>
                        <th><?= __('mother') ?></th>
                        <th>Child Type</th>
                        <th><?= __('created') ?></th>
                    </tr>
                </thead>

                <tbody>

                <?php if (!empty($relationships)): ?>

                    <?php foreach ($relationships as $i => $row): ?>

                        <tr>

                            <td><?= $i + 1 ?></td>

                            <td>
                                <strong>
                                    <?= htmlspecialchars($row['child_name']) ?>
                                </strong>

                                <small class="d-block text-muted">
                                    <?= htmlspecialchars($row['child_code']) ?>
                                </small>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $row['father_name'] ?: 'Not recorded'
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $row['mother_name'] ?: 'Not recorded'
                                ) ?>
                            </td>

                            <td>
                                <span class="badge badge-info">
                                    <?= htmlspecialchars($row['child_type']) ?>
                                </span>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['created_at']) ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            No family relationships found.
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