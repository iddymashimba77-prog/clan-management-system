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

$database = new Database();
$pdo = $database->connect();

$message = null;
$messageType = 'success';


/*
|--------------------------------------------------------------------------
| Create backup
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['create_backup'])
) {

    try {

        $backup = createDatabaseBackup($pdo);

        $message =
            'Backup created successfully: ' .
            $backup['filename'];

        $messageType = 'success';

    } catch (Throwable $e) {

        $message =
            $e->getMessage();

        $messageType = 'danger';
    }
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

            <i class="fas fa-database mr-2 text-primary"></i>

            Database <?= __('backup') ?>

        </h3>

    </div>


    <div class="card-body">


        <?php if ($message): ?>

            <div class="alert alert-<?= htmlspecialchars($messageType) ?>">

                <?= htmlspecialchars($message) ?>

            </div>

        <?php endif; ?>


        <div class="mb-4">

            <form method="POST">

                <button
                    type="submit"
                    name="create_backup"
                    value="1"
                    class="btn btn-primary"
                >

                    <i class="fas fa-database mr-1"></i>

                    Create New <?= __('backup') ?>

                </button>

                <a
                    href="restore.php"
                    class="btn btn-outline-warning"
                >

                    <i class="fas fa-upload mr-1"></i>

                    Restore Database

                </a>

            </form>

        </div>


        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead>

                    <tr>

                        <th>#</th>

                        <th>Backup File</th>

                        <th><?= __('size') ?></th>

                        <th><?= __('created') ?></th>

                        <th width="180"><?= __('actions') ?></th>

                    </tr>

                </thead>


                <tbody>

                <?php if ($backups): ?>

                    <?php foreach ($backups as $index => $backup): ?>

                        <tr>

                            <td>
                                <?= $index + 1 ?>
                            </td>

                            <td>

                                <i class="fas fa-file-code text-primary mr-1"></i>

                                <?= htmlspecialchars(
                                    $backup['filename']
                                ) ?>

                            </td>

                            <td>

                                <?= number_format(
                                    $backup['size'] / 1024,
                                    2
                                ) ?>

                                KB

                            </td>

                            <td>

                                <?= htmlspecialchars(
                                    $backup['created_at']
                                ) ?>

                            </td>

                            <td>

                                <a
                                    href="download_backup.php?file=<?= urlencode($backup['filename']) ?>"
                                    class="btn btn-sm btn-success"
                                    title="Download"
                                >

                                    <i class="fas fa-download"></i>

                                </a>


                                <a
                                    href="delete_backup.php?file=<?= urlencode($backup['filename']) ?>"
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('Delete this backup?');"
                                    title="Delete"
                                >

                                    <i class="fas fa-trash"></i>

                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td
                            colspan="5"
                            class="text-center text-muted py-4"
                        >

                            <i class="fas fa-database fa-2x mb-2"></i>

                            <br>

                            No backups available.

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