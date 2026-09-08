<?php

ini_set('display_startup_errors',1);

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';
require_once '../../config/backup.php';

requireLogin();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$backups = listDatabaseBackups();

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/admin.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">


<div class="card clan-page-card">

    <div class="card-header">

        <h3 class="card-title">

            <i class="fas fa-upload mr-2 text-warning"></i>

            Restore Database

        </h3>

    </div>


    <div class="card-body">


        <div class="alert alert-warning">

            <strong>
                Warning:
            </strong>

            Restoring a database will overwrite existing database
            data. Make sure you have a recent backup before continuing.

        </div>


        <?php if (!empty($_SESSION['error'])): ?>

            <div class="alert alert-danger">

                <?= htmlspecialchars(
                    $_SESSION['error']
                ) ?>

            </div>

            <?php unset($_SESSION['error']); ?>

        <?php endif; ?>


        <?php if (!empty($_SESSION['success'])): ?>

            <div class="alert alert-success">

                <?= htmlspecialchars(
                    $_SESSION['success']
                ) ?>

            </div>

            <?php unset($_SESSION['success']); ?>

        <?php endif; ?>


        <form
            action="restore_process.php"
            method="POST"
            enctype="multipart/form-data"
        >

            <div class="form-group">

                <label>
                    Select existing backup
                </label>

                <select
                    name="backup_file"
                    class="form-control"
                >

                    <option value="">
                        -- Select Backup --
                    </option>


                    <?php foreach ($backups as $backup): ?>

                        <option
                            value="<?= htmlspecialchars(
                                $backup['filename']
                            ) ?>"
                        >

                            <?= htmlspecialchars(
                                $backup['filename']
                            ) ?>

                            -
                            <?= number_format(
                                $backup['size'] / 1024,
                                2
                            ) ?>
                            KB

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="text-center my-3">

                <strong>
                    OR
                </strong>

            </div>


            <div class="form-group">

                <label>
                    Upload SQL backup
                </label>

                <input
                    type="file"
                    name="sql_file"
                    class="form-control-file"
                    accept=".sql"
                >

                <small class="form-text text-muted">

                    Only .sql database backup files are allowed.

                </small>

            </div>


            <div class="form-check mb-3">

                <input
                    type="checkbox"
                    class="form-check-input"
                    id="confirm_restore"
                    name="confirm_restore"
                    value="1"
                    required
                >

                <label
                    class="form-check-label"
                    for="confirm_restore"
                >

                    I understand that restoring the database may overwrite
                    current data.

                </label>

            </div>


            <button
                type="submit"
                class="btn btn-warning"
                onclick="return confirm('Are you sure you want to restore the database?');"
            >

                <i class="fas fa-upload mr-1"></i>

                Restore Database

            </button>


            <a
                href="backup.php"
                class="btn btn-secondary"
            >

                <i class="fas fa-arrow-left mr-1"></i>

                Back to Backups

            </a>

        </form>

    </div>

</div>


</div>

</section>

</div>


<?php require_once '../../includes/dashboard/footer.php'; ?>