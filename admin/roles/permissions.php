<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$roleId = (int)($_GET['id'] ?? 0);

if ($roleId <= 0) {
    header('Location: index.php');
    exit;
}

$pdo = $database->connect();


/* Role */

$stmt = $pdo->prepare("
    SELECT role_id, role_name
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


/* Permissions */

$stmt = $pdo->query("
    SELECT
        permission_id,
        permission_name,
        permission_key,
        description,
        module
    FROM permissions
    WHERE status = 'active'
    ORDER BY
        module ASC,
        permission_name ASC
");

$allPermissions = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* Assigned permissions */

$stmt = $pdo->prepare("
    SELECT permission_id
    FROM role_permissions
    WHERE role_id = ?
");

$stmt->execute([$roleId]);

$assignedPermissions = array_map(
    'intval',
    $stmt->fetchAll(PDO::FETCH_COLUMN)
);


/* Group */

$groupedPermissions = [];

foreach ($allPermissions as $permission) {

    $module = trim($permission['module'] ?? '');

    if ($module === '') {
        $module = 'General';
    }

    $groupedPermissions[$module][] = $permission;
}


require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebar.php';
require_once '../../includes/dashboard/navbar.php';
?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">

    <div class="mb-3">

        <a
            href="view.php?id=<?= $roleId ?>"
            class="btn btn-outline-primary btn-sm"
        >
            <i class="fas fa-arrow-left mr-1"></i>
            <?= htmlspecialchars($role['role_name']) ?>
        </a>

    </div>


    <div class="card clan-page-card">

        <div class="card-header">

            <h5 class="mb-0">

                <i class="fas fa-key mr-2 text-primary"></i>

                Manage Permissions:
                <?= htmlspecialchars($role['role_name']) ?>

            </h5>

        </div>


        <form
            action="permissions_update.php"
            method="POST"
        >

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
            >

            <input
                type="hidden"
                name="role_id"
                value="<?= $roleId ?>"
            >


            <div class="card-body">


                <?php if (!empty($groupedPermissions)): ?>

                    <?php foreach ($groupedPermissions as $module => $permissions): ?>

                        <div class="mb-4">

                            <div class="d-flex justify-content-between align-items-center mb-2">

                                <h6 class="font-weight-bold text-primary mb-0">

                                    <i class="fas fa-folder mr-1"></i>

                                    <?= htmlspecialchars($module) ?>

                                </h6>


                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary select-module"
                                    data-module="<?= htmlspecialchars(
                                        preg_replace(
                                            '/[^a-zA-Z0-9]/',
                                            '_',
                                            $module
                                        )
                                    ) ?>"
                                >
                                    Select All
                                </button>

                            </div>


                            <div class="row">

                                <?php

                                $moduleKey = preg_replace(
                                    '/[^a-zA-Z0-9]/',
                                    '_',
                                    $module
                                );

                                ?>


                                <?php foreach ($permissions as $permission): ?>

                                    <?php
                                    $permissionId =
                                        (int)$permission['permission_id'];

                                    $checked =
                                        in_array(
                                            $permissionId,
                                            $assignedPermissions,
                                            true
                                        );
                                    ?>


                                    <div class="col-md-6 col-lg-4 mb-2">

                                        <div class="custom-control custom-checkbox">

                                            <input
                                                type="checkbox"
                                                class="custom-control-input permission-checkbox module-<?= htmlspecialchars($moduleKey) ?>"
                                                id="permission_<?= $permissionId ?>"
                                                name="permissions[]"
                                                value="<?= $permissionId ?>"
                                                <?= $checked ? 'checked' : '' ?>
                                            >

                                            <label
                                                class="custom-control-label"
                                                for="permission_<?= $permissionId ?>"
                                            >

                                                <strong>
                                                    <?= htmlspecialchars(
                                                        $permission['permission_name']
                                                    ) ?>
                                                </strong>

                                                <small class="d-block text-muted">

                                                    <?= htmlspecialchars(
                                                        $permission['permission_key']
                                                    ) ?>

                                                </small>

                                            </label>

                                        </div>

                                    </div>

                                <?php endforeach; ?>

                            </div>

                        </div>

                        <hr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="alert alert-warning">

                        No active permissions found.

                        Create permissions first.

                    </div>

                <?php endif; ?>

            </div>


            <div class="card-footer">

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <i class="fas fa-save mr-1"></i>
                    Save Permissions
                </button>

                <a
                    href="view.php?id=<?= $roleId ?>"
                    class="btn btn-secondary"
                >
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

</section>

</div>


<script>

document.querySelectorAll('.select-module').forEach(function(button) {

    button.addEventListener('click', function() {

        const module = this.dataset.module;

        const checkboxes = document.querySelectorAll(
            '.module-' + module
        );

        let allChecked = true;

        checkboxes.forEach(function(box) {

            if (!box.checked) {
                allChecked = false;
            }

        });

        checkboxes.forEach(function(box) {

            box.checked = !allChecked;

        });

        this.innerText = allChecked
            ? 'Select All'
            : 'Unselect All';

    });

});

</script>


<?php require_once '../../includes/dashboard/footer.php'; ?>