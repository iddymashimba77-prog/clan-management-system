<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

                <i class="fas fa-key text-primary mr-2"></i>

                Create Permission

            </h4>

            <small class="text-muted">

                Create a new system permission.

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


    <!-- ERROR MESSAGE -->

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

                <i class="fas fa-plus-circle mr-2 text-primary"></i>

                Permission Information

            </h5>

        </div>


        <form
            action="store.php"
            method="POST"
        >

            <div class="card-body">

                <div class="row">


                    <!-- PERMISSION NAME -->

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
                                placeholder="Example: View Members"
                                required
                            >

                        </div>

                    </div>


                    <!-- PERMISSION KEY -->

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
                                placeholder="Example: view_members"
                                required
                            >

                            <small class="form-text text-muted">

                                Use lowercase letters, numbers and underscores.

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
                                placeholder="Example: Members"
                                required
                            >

                            <small class="form-text text-muted">

                                Example: Members, Contributions, Payments, Backup.

                            </small>

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

                                <option value="active">
                                    Active
                                </option>

                                <option value="inactive">
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
                                placeholder="Describe what this permission allows the user to do..."
                            ></textarea>

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

                    Save Permission

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