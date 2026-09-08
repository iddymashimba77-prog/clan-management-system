<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$pdo = $database->connect();

$stmt = $pdo->query("
    SELECT
        u.user_id,
        u.member_code,
        u.first_name,
        u.middle_name,
        u.last_name,
        u.gender,
        u.date_of_birth,
        u.marital_status,
        u.father_user_id,
        u.mother_user_id,
        u.spouse_user_id,
        f.member_code AS father_code,
        CONCAT(
            f.first_name, ' ',
            COALESCE(f.middle_name, ''), ' ',
            f.last_name
        ) AS father_name,
        m.member_code AS mother_code,
        CONCAT(
            m.first_name, ' ',
            COALESCE(m.middle_name, ''), ' ',
            m.last_name
        ) AS mother_name,
        s.member_code AS spouse_code,
        CONCAT(
            s.first_name, ' ',
            COALESCE(s.middle_name, ''), ' ',
            s.last_name
        ) AS spouse_name
    FROM users u
    LEFT JOIN users f
        ON f.user_id = u.father_user_id
    LEFT JOIN users m
        ON m.user_id = u.mother_user_id
    LEFT JOIN users s
        ON s.user_id = u.spouse_user_id
    WHERE u.is_deleted = 0
    ORDER BY u.first_name ASC, u.last_name ASC
");

$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/admin.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">
<section class="content">

<div class="container-fluid clan-page-content">

<div class="card clan-page-card">

    <div class="card-header d-flex justify-content-between align-items-center">

        <div>
            <h4 class="mb-1">
                <i class="fas fa-sitemap mr-2 text-primary"></i>
                Family Tree Management
            </h4>

            <small class="text-muted">
                Manage parents, spouses and family relationships.
            </small>
        </div>

       
         <a href="<?= BASE_URL ?>family_tree/index.php" class="btn btn-primary btn-sm">
           <i class="nav-icon fas fa-sitemap"></i>
            View <?= __('family_tree') ?>
        </a>
        

        <a href="create.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i>
            Add Relationship
        </a>
        


    </div>

    <div class="card-body">

        <?php if (!empty($members)): ?>

        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead>
                <tr>
                    <th>#</th>
                    <th><?= __('member') ?></th>
                    <th><?= __('father') ?></th>
                    <th><?= __('mother') ?></th>
                    <th><?= __('spouse') ?></th>
                    <th><?= __('gender') ?></th>
                    <th><?= __('actions') ?></th>
                </tr>
                </thead>

                <tbody>

                <?php foreach ($members as $i => $member): ?>

                <tr>

                    <td><?= $i + 1 ?></td>

                    <td>
                        <strong>
                            <?= htmlspecialchars(
                                trim(
                                    $member['first_name'].' '.
                                    ($member['middle_name'] ?? '').' '.
                                    $member['last_name']
                                )
                            ) ?>
                        </strong>

                        <small class="d-block text-muted">
                            <?= htmlspecialchars($member['member_code']) ?>
                        </small>
                    </td>

                    <td>
                        <?php if ($member['father_user_id']): ?>

                            <span class="text-primary">
                                <?= htmlspecialchars($member['father_name']) ?>
                            </span>

                            <small class="d-block text-muted">
                                <?= htmlspecialchars($member['father_code']) ?>
                            </small>

                        <?php else: ?>

                            <span class="text-muted">
                                Not recorded
                            </span>

                        <?php endif; ?>
                    </td>

                    <td>
                        <?php if ($member['mother_user_id']): ?>

                            <span class="text-danger">
                                <?= htmlspecialchars($member['mother_name']) ?>
                            </span>

                            <small class="d-block text-muted">
                                <?= htmlspecialchars($member['mother_code']) ?>
                            </small>

                        <?php else: ?>

                            <span class="text-muted">
                                Not recorded
                            </span>

                        <?php endif; ?>
                    </td>

                    <td>
                        <?php if ($member['spouse_user_id']): ?>

                            <span class="text-danger">
                                <?= htmlspecialchars($member['spouse_name']) ?>
                            </span>

                            <small class="d-block text-muted">
                                <?= htmlspecialchars($member['spouse_code']) ?>
                            </small>

                        <?php else: ?>

                            <span class="text-muted">
                                None
                            </span>

                        <?php endif; ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($member['gender']) ?>
                    </td>

                    <td>

                        <a
                            href="view.php?id=<?= (int)$member['user_id'] ?>"
                            class="btn btn-info btn-sm"
                            title="View"
                        >
                            <i class="fas fa-eye"></i>
                        </a>

                        <a
                            href="edit.php?id=<?= (int)$member['user_id'] ?>"
                            class="btn btn-warning btn-sm"
                            title="Edit"
                        >
                            <i class="fas fa-edit"></i>
                        </a>

                        <a
                            href="delete.php?id=<?= (int)$member['user_id'] ?>"
                            class="btn btn-danger btn-sm"
                            title="Remove relationship"
                            onclick="return confirm('Remove family relationships for this member?')"
                        >
                            <i class="fas fa-unlink"></i>
                        </a>

                    </td>

                </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

        <?php else: ?>

            <div class="text-center py-5">

                <i class="fas fa-sitemap fa-3x text-muted mb-3"></i>

                <h5><?= __('no_family_relationships_found') ?></h5>

                <p class="text-muted">
                    Start by connecting members.
                </p>

                <a href="create.php" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i>
                    Add Relationship
                </a>

            </div>

        <?php endif; ?>

    </div>

</div>

</div>

</section>
</div>

<?php require_once '../../includes/dashboard/footer.php'; ?>