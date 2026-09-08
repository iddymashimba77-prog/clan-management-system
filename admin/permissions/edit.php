<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$permissionId = (int)(
    $_GET['id'] ?? 0
);


if ($permissionId <= 0) {

    $_SESSION['error'] =
        'Invalid permission ID.';

    header('Location: index.php');

    exit;
}


$database = new Database();

$pdo = $database->connect();


/*
|--------------------------------------------------------------------------
| GET PERMISSION
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        permission_id,
        permission_name,
        permission_key,
        description,
        module,
        status
    FROM permissions
    WHERE permission_id = ?
    LIMIT 1
");

$stmt->execute([
    $permissionId
]);

$permission = $stmt->fetch(
    PDO::FETCH_ASSOC
);


if (!$permission) {

    $_SESSION['error'] =
        'Permission not found.';

    header('Location: index.php');

    exit;
}


/*
|--------------------------------------------------------------------------
| PAGE
|--------------------------------------------------------------------------
*/

require_once '../../includes/dashboard/header.php';

require_once '../../includes/dashboard/sidebars/admin.php';

require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">


<!-- PAGE HEADER -->

<div class="d-flex justify-content-between align-items-center mb-3">

    <div>

        <h4 class="mb-1">

            <i class="fas fa-edit text-warning mr-2"></i>

            Edit Permission

        </h4>

        <small class="text-muted">

            Update system permission information.

        </small>

    </div>


    <div>

        <a
            href="index.php"
            class="btn btn-secondary"
        >

            <i class="fas fa-arrow-left mr-1"></i>

            Back to Permissions

        </a>

    </div>

</div>


<!-- ERROR -->

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


<!-- FORM -->

<div class="card clan-page-card">

    <div class="card-header">

        <h5 class="mb-0">

            <i class="fas fa-key mr-2 text-primary"></i>

            Permission Information

        </h5>

    </div>


    <form
        action="store.php"
        method="POST"
    >

        <input
            type="hidden"
            name="permission_id"
            value="<?= (int)$permission['permission_id'] ?>"
        >


        <div class="card-body">

            <div class="row">


                <!-- NAME -->

                <div class="col-md-6">

                    <div class="form-group">

                        <label for="permission_name">

                            Permission Name

                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="text"
                            name="permission_name"
                            id="permission_name"
                            class="form-control"
                            value="<?= htmlspecialchars(
                                $permission['permission_name']
                            ) ?>"
                            required
                        >

                    </div>

                </div>


                <!-- KEY -->

                <div class="col-md-6">

                    <div class="form-group">

                        <label for="permission_key">

                            Permission Key

                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="text"
                            name="permission_key"
                            id="permission_key"
                            class="form-control"
                            value="<?= htmlspecialchars(
                                $permission['permission_key']
                            ) ?>"
                            required
                        >

                        <small class="form-text text-muted">

                            Lowercase letters, numbers and underscores only.

                        </small>

                    </div>

                </div>


                <!-- MODULE -->

                <div class="col-md-6">

                    <div class="form-group">

                        <label for="module">

                            Module

                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="text"
                            name="module"
                            id="module"
                            class="form-control"
                            value="<?= htmlspecialchars(
                                $permission['module'] ?? ''
                            ) ?>"
                            required
                        >

                    </div>

                </div>


                <!-- STATUS -->

                <div class="col-md-6">

                    <div class="form-group">

                        <label for="status">

                            Status

                        </label>

                        <select
                            name="status"
                            id="status"
                            class="form-control"
                        >

                            <option
                                value="active"
                                <?= $permission['status'] === 'active'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Active
                            </option>


                            <option
                                value="inactive"
                                <?= $permission['status'] === 'inactive'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Inactive
                            </option>

                        </select>

                    </div>

                </div>


                <!-- DESCRIPTION -->

                <div class="col-md-12">

                    <div class="form-group">

                        <label for="description">

                            Description

                        </label>

                        <textarea
                            name="description"
                            id="description"
                            class="form-control"
                            rows="4"
                        ><?= htmlspecialchars(
                            $permission['description'] ?? ''
                        ) ?></textarea>

                    </div>

                </div>


            </div>

        </div>


        <div class="card-footer">

            <button
                type="submit"
                class="btn btn-primary"
            >

                <i class="fas fa-save mr-1"></i>

                Update Permission

            </button>


            <a
                href="index.php"
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


<?php

require_once '../../includes/dashboard/footer.php';

?>