<?php

ini_set('display_startup_errors',1);

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$roleId = (int)($_GET['id'] ?? 0);

if ($roleId <= 0) {
    header('Location: index.php');
    exit;
}


/* Role */

$stmt = $pdo->prepare("
    SELECT
        role_id,
        role_name,
        description,
        status,
        created_at
    FROM roles
    WHERE role_id = ?
    LIMIT 1
");

$stmt->execute([$roleId]);

$role = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$role) {
    header('Location: index.php');
    exit;
}


/* Users */

$stmt = $pdo->prepare("
    SELECT
        u.user_id,
        u.member_code,
        u.first_name,
        u.middle_name,
        u.last_name,
        u.email,
        u.phone
    FROM user_roles ur
    INNER JOIN users u
        ON u.user_id = ur.user_id
    WHERE ur.role_id = ?
      AND u.is_deleted = 0
    ORDER BY u.first_name, u.last_name
");

$stmt->execute([$roleId]);

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* Permissions */

$stmt = $pdo->prepare("
    SELECT
        p.permission_id,
        p.permission_name,
        p.permission_key,
        p.description,
        p.module
    FROM role_permissions rp
    INNER JOIN permissions p
        ON p.permission_id = rp.permission_id
    WHERE rp.role_id = ?
      AND p.status = 'active'
    ORDER BY
        p.module ASC,
        p.permission_name ASC
");

$stmt->execute([$roleId]);

$permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);


require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebar.php';
require_once '../../includes/dashboard/navbar.php';
?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">

    <div class="mb-3">

        <a href="index.php" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i>
            Roles
        </a>

    </div>


    <div class="row">


        <!-- ROLE INFORMATION -->

        <div class="col-md-5">

            <div class="card clan-page-card">

                <div class="card-header">

                    <h5 class="mb-0">
                        <i class="fas fa-user-shield mr-2 text-primary"></i>
                        Role Information
                    </h5>

                </div>


                <div class="card-body">

                    <h3>
                        <?= htmlspecialchars($role['role_name']) ?>
                    </h3>


                    <p class="text-muted">

                        <?= htmlspecialchars(
                            $role['description'] ?? 'No description'
                        ) ?>

                    </p>


                    <hr>


                    <p>
                        <strong>Status:</strong>

                        <?php if ($role['status'] === 'active'): ?>

                            <span class="badge badge-success">
                                Active
                            </span>

                        <?php else: ?>

                            <span class="badge badge-secondary">
                                Inactive
                            </span>

                        <?php endif; ?>

                    </p>


                    <p>
                        <strong>Users:</strong>
                        <?= count($users) ?>
                    </p>


                    <p>
                        <strong>Permissions:</strong>
                        <?= count($permissions) ?>
                    </p>


                    <div class="mt-3">

                        <a
                            href="edit.php?id=<?= $roleId ?>"
                            class="btn btn-warning"
                        >
                            <i class="fas fa-edit mr-1"></i>
                            Edit
                        </a>


                        <a
                            href="permissions.php?id=<?= $roleId ?>"
                            class="btn btn-primary"
                        >
                            <i class="fas fa-key mr-1"></i>
                            Permissions
                        </a>

                    </div>

                </div>

            </div>

        </div>


        <!-- PERMISSIONS -->

        <div class="col-md-7">

            <div class="card clan-page-card">

                <div class="card-header">

                    <h5 class="mb-0">
                        <i class="fas fa-key mr-2 text-primary"></i>
                        Assigned Permissions
                    </h5>

                </div>


                <div class="card-body">

                    <?php if (!empty($permissions)): ?>

                        <?php

                        $grouped = [];

                        foreach ($permissions as $permission) {

                            $module = $permission['module']
                                ?: 'General';

                            $grouped[$module][] = $permission;
                        }

                        ?>


                        <?php foreach ($grouped as $module => $items): ?>

                            <h6 class="font-weight-bold text-primary mt-2">
                                <?= htmlspecialchars($module) ?>
                            </h6>

                            <div class="mb-3">

                                <?php foreach ($items as $permission): ?>

                                    <span class="badge badge-light border mr-1 mb-1">

                                        <?= htmlspecialchars(
                                            $permission['permission_name']
                                        ) ?>

                                    </span>

                                <?php endforeach; ?>

                            </div>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <p class="text-muted">
                            No permissions assigned.
                        </p>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>


    <!-- USERS -->

    <div class="card clan-page-card">

        <div class="card-header">

            <h5 class="mb-0">
                <i class="fas fa-users mr-2 text-primary"></i>
                Users With This Role
            </h5>

        </div>


        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover">

                    <thead>

                        <tr>
                            <th>#</th>
                            <th>Member Code</th>
                            <th><?= __('name') ?></th>
                            <th><?= __('email') ?></th>
                            <th><?= __('phone') ?></th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach ($users as $index => $user): ?>

                        <tr>

                            <td>
                                <?= $index + 1 ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($user['member_code']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    trim(
                                        $user['first_name'] . ' ' .
                                        ($user['middle_name'] ?? '') . ' ' .
                                        $user['last_name']
                                    )
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($user['email'] ?? '-') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($user['phone'] ?? '-') ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>


                    <?php if (empty($users)): ?>

                        <tr>

                            <td colspan="5" class="text-center text-muted">
                                No users assigned to this role.
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