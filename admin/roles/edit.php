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

$stmt = $pdo->prepare("
    SELECT
        role_id,
        role_name,
        description,
        status
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
            Back
        </a>

    </div>


    <div class="card clan-page-card">

        <div class="card-header">

            <h5 class="mb-0">
                <i class="fas fa-edit mr-2 text-primary"></i>
                <?= __('edit_role') ?>
            </h5>

        </div>


        <form action="update.php" method="POST">

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

                <div class="form-group">

                    <label>
                        Role Name
                    </label>

                    <input
                        type="text"
                        name="role_name"
                        class="form-control"
                        value="<?= htmlspecialchars($role['role_name']) ?>"
                        required
                        maxlength="100"
                    >

                </div>


                <div class="form-group">

                    <label><?= __('description') ?></label>

                    <textarea
                        name="description"
                        class="form-control"
                        rows="4"
                    ><?= htmlspecialchars($role['description'] ?? '') ?></textarea>

                </div>


                <div class="form-group">

                    <label><?= __('status') ?></label>

                    <select name="status" class="form-control">

                        <option
                            value="active"
                            <?= $role['status'] === 'active' ? 'selected' : '' ?>
                        >
                            Active
                        </option>

                        <option
                            value="inactive"
                            <?= $role['status'] === 'inactive' ? 'selected' : '' ?>
                        >
                            Inactive
                        </option>

                    </select>

                </div>

            </div>


            <div class="card-footer">

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <i class="fas fa-save mr-1"></i>
                    Save Changes
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

<?php require_once '../../includes/dashboard/footer.php'; ?>