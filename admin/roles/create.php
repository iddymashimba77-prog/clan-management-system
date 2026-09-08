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


            <div class="card clan-page-card">

                <div class="card-header">

                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle mr-2 text-primary"></i>
                        Create New Role
                    </h5>

                </div>


                <form action="store.php" method="POST">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
                    >


                    <div class="card-body">

                        <div class="form-group">

                            <label>
                                Role Name
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="text"
                                name="role_name"
                                class="form-control"
                                required
                                maxlength="100"
                                placeholder="e.g. Finance Manager"
                            >

                        </div>


                        <div class="form-group">

                            <label><?= __('description') ?></label>

                            <textarea
                                name="description"
                                class="form-control"
                                rows="4"
                                placeholder="Describe the responsibilities of this role..."
                            ></textarea>

                        </div>


                        <div class="form-group">

                            <label><?= __('status') ?></label>

                            <select name="status" class="form-control">

                                <option value="active">
                                    Active
                                </option>

                                <option value="inactive">
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
                            <?= __('create_role') ?>
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

<?php require_once '../../includes/dashboard/footer.php'; ?>