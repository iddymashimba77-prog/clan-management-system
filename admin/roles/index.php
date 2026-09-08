<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = $database->connect();


/* =========================================================
   GET ROLES
   Based on the actual roles table structure:
   role_id, role_name, description, created_at
========================================================= */

$stmt = $pdo->query("
    SELECT
        r.role_id,
        r.role_name,
        r.description,
        r.created_at,

        (
            SELECT COUNT(*)
            FROM users u
            WHERE u.role_id = r.role_id
              AND u.is_deleted = 0
        ) AS total_users,

        (
            SELECT COUNT(*)
            FROM role_permissions rp
            WHERE rp.role_id = r.role_id
        ) AS total_permissions

    FROM roles r

    ORDER BY r.role_id ASC
");

$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================================================
   DASHBOARD
========================================================= */

require_once '../../includes/dashboard/header.php';

require_once '../../includes/dashboard/sidebars/admin.php';

require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">


<!-- =====================================================
     PAGE HEADER
===================================================== -->

<div class="d-flex justify-content-between align-items-center mb-3">

    <div>

        <h4 class="mb-1">

            <i class="fas fa-user-shield text-primary mr-2"></i>

            <?= __('roles_management') ?>

        </h4>

        <small class="text-muted">

            Manage system roles and their permissions.

        </small>

    </div>


    <div>

        <a
            href="create.php"
            class="btn btn-primary"
        >

            <i class="fas fa-plus mr-1"></i>

            Create Role

        </a>

    </div>

</div>


<!-- =====================================================
     SESSION SUCCESS
===================================================== -->

<?php if (!empty($_SESSION['success'])): ?>

    <div class="alert alert-success alert-dismissible fade show">

        <?= htmlspecialchars($_SESSION['success']) ?>

        <button
            type="button"
            class="close"
            data-dismiss="alert"
        >
            &times;
        </button>

    </div>

    <?php unset($_SESSION['success']); ?>

<?php endif; ?>


<!-- =====================================================
     SESSION ERROR
===================================================== -->

<?php if (!empty($_SESSION['error'])): ?>

    <div class="alert alert-danger alert-dismissible fade show">

        <?= htmlspecialchars($_SESSION['error']) ?>

        <button
            type="button"
            class="close"
            data-dismiss="alert"
        >
            &times;
        </button>

    </div>

    <?php unset($_SESSION['error']); ?>

<?php endif; ?>


<!-- =====================================================
     ROLES TABLE
===================================================== -->

<div class="card clan-page-card">

    <div class="card-header">

        <h5 class="mb-0">

            <i class="fas fa-shield-alt mr-2 text-primary"></i>

            System Roles

        </h5>

    </div>


    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead>

                    <tr>

                        <th width="50">#</th>

                        <th><?= __('role') ?></th>

                        <th><?= __('description') ?></th>

                        <th width="100">
                            Users
                        </th>

                        <th width="120">
                            Permissions
                        </th>

                        <th width="170">
                            Created
                        </th>

                        <th width="230">
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php if (!empty($roles)): ?>


                    <?php foreach ($roles as $index => $role): ?>


                        <tr>


                            <!-- NUMBER -->

                            <td>

                                <?= $index + 1 ?>

                            </td>


                            <!-- ROLE -->

                            <td>

                                <strong>

                                    <?= htmlspecialchars(
                                        $role['role_name']
                                    ) ?>

                                </strong>

                            </td>


                            <!-- DESCRIPTION -->

                            <td>

                                <?php

                                $description =
                                    trim(
                                        $role['description'] ?? ''
                                    );

                                ?>

                                <?= htmlspecialchars(
                                    $description !== ''
                                        ? $description
                                        : 'No description'
                                ) ?>

                            </td>


                            <!-- USERS -->

                            <td>

                                <span class="badge badge-info">

                                    <?= (int)$role['total_users'] ?>

                                </span>

                            </td>


                            <!-- PERMISSIONS -->

                            <td>

                                <span class="badge badge-primary">

                                    <?= (int)$role['total_permissions'] ?>

                                </span>

                            </td>


                            <!-- CREATED -->

                            <td>

                                <?= htmlspecialchars(
                                    $role['created_at'] ?? ''
                                ) ?>

                            </td>


                            <!-- ACTIONS -->

                            <td>


                                <!-- VIEW -->

                                <a
                                    href="view.php?id=<?= (int)$role['role_id'] ?>"
                                    class="btn btn-sm btn-info"
                                    title="View Role"
                                >

                                    <i class="fas fa-eye"></i>

                                </a>


                                <!-- EDIT -->

                                <a
                                    href="edit.php?id=<?= (int)$role['role_id'] ?>"
                                    class="btn btn-sm btn-warning"
                                    title="Edit Role"
                                >

                                    <i class="fas fa-edit"></i>

                                </a>


                                <!-- PERMISSIONS -->

                                <a
                                    href="permissions.php?id=<?= (int)$role['role_id'] ?>"
                                    class="btn btn-sm btn-primary"
                                    title="Manage Permissions"
                                >

                                    <i class="fas fa-key"></i>

                                </a>


                                <!-- DELETE -->

                                <a
                                    href="delete.php?id=<?= (int)$role['role_id'] ?>"
                                    class="btn btn-sm btn-danger"
                                    title="Delete Role"
                                    onclick="
                                        return confirm(
                                            'Are you sure you want to delete this role?'
                                        );
                                    "
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

                            <i class="fas fa-user-shield fa-2x mb-2"></i>

                            <br>

                            <?= __('no_roles_found') ?>

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