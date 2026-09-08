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


/*
|--------------------------------------------------------------------------
| GET PERMISSIONS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        p.permission_id,
        p.permission_name,
        p.permission_key,
        p.description,
        p.module,
        p.status,
        p.created_at,

        (
            SELECT COUNT(*)
            FROM role_permissions rp
            WHERE rp.permission_id = p.permission_id
        ) AS total_roles

    FROM permissions p

    ORDER BY
        p.module ASC,
        p.permission_name ASC
");

$permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);


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


<!-- =====================================================
     PAGE HEADER
===================================================== -->

<div class="d-flex justify-content-between align-items-center mb-3">

    <div>

        <h4 class="mb-1">

            <i class="fas fa-key text-primary mr-2"></i>

            <?= __('permissions_management') ?>

        </h4>

        <small class="text-muted">

            Manage system permissions and access capabilities.

        </small>

    </div>


    <div>

        <a
            href="create.php"
            class="btn btn-primary"
        >

            <i class="fas fa-plus mr-1"></i>

            Create Permission

        </a>

    </div>

</div>


<!-- =====================================================
     SUCCESS MESSAGE
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
     ERROR MESSAGE
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
     PERMISSIONS TABLE
===================================================== -->

<div class="card clan-page-card">

    <div class="card-header">

        <h5 class="mb-0">

            <i class="fas fa-shield-alt mr-2 text-primary"></i>

            System Permissions

        </h5>

    </div>


    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead>

                    <tr>

                        <th width="50">#</th>

                        <th><?= __('permission') ?></th>

                        <th>Permission Key</th>

                        <th><?= __('description') ?></th>

                        <th><?= __('module') ?></th>

                        <th width="100"><?= __('roles') ?></th>

                        <th width="100"><?= __('status') ?></th>

                        <th width="170"><?= __('created') ?></th>

                        <th width="150"><?= __('actions') ?></th>

                    </tr>

                </thead>


                <tbody>


                <?php if (!empty($permissions)): ?>


                    <?php foreach ($permissions as $index => $permission): ?>

                        <tr>


                            <!-- NUMBER -->

                            <td>

                                <?= $index + 1 ?>

                            </td>


                            <!-- NAME -->

                            <td>

                                <strong>

                                    <?= htmlspecialchars(
                                        $permission['permission_name']
                                    ) ?>

                                </strong>

                            </td>


                            <!-- KEY -->

                            <td>

                                <code>

                                    <?= htmlspecialchars(
                                        $permission['permission_key']
                                    ) ?>

                                </code>

                            </td>


                            <!-- DESCRIPTION -->

                            <td>

                                <?php

                                $description =
                                    trim(
                                        $permission['description'] ?? ''
                                    );

                                ?>

                                <?= htmlspecialchars(
                                    $description !== ''
                                        ? $description
                                        : 'No description'
                                ) ?>

                            </td>


                            <!-- MODULE -->

                            <td>

                                <span class="badge badge-info">

                                    <?= htmlspecialchars(
                                        $permission['module'] ?? 'General'
                                    ) ?>

                                </span>

                            </td>


                            <!-- ROLES -->

                            <td>

                                <span class="badge badge-primary">

                                    <?= (int)$permission['total_roles'] ?>

                                </span>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <?php if (
                                    ($permission['status'] ?? 'active')
                                    === 'active'
                                ): ?>

                                    <span class="badge badge-success">

                                        Active

                                    </span>

                                <?php else: ?>

                                    <span class="badge badge-secondary">

                                        Inactive

                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- CREATED -->

                            <td>

                                <?= htmlspecialchars(
                                    $permission['created_at'] ?? ''
                                ) ?>

                            </td>


                            <!-- ACTIONS -->

                            <td>

                                <a
                                    href="edit.php?id=<?= (int)$permission['permission_id'] ?>"
                                    class="btn btn-sm btn-warning"
                                    title="Edit Permission"
                                >

                                    <i class="fas fa-edit"></i>

                                </a>


                                <a
                                    href="delete.php?id=<?= (int)$permission['permission_id'] ?>"
                                    class="btn btn-sm btn-danger"
                                    title="Delete Permission"
                                    onclick="
                                        return confirm(
                                            'Are you sure you want to delete this permission?'
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
                            colspan="9"
                            class="text-center text-muted py-5"
                        >

                            <i class="fas fa-key fa-2x mb-2"></i>

                            <br>

                            <?= __('no_permissions_found') ?>

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