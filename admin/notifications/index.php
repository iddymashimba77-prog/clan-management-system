<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->prepare("
    SELECT
        n.notification_id,
        n.user_id,
        n.title,
        n.notification_type,
        n.message,
        n.is_read,
        n.created_at
    FROM notifications n
    WHERE n.user_id = ?
    ORDER BY n.created_at DESC
");

$stmt->execute([$_SESSION['user_id']]);

$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/admin.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">

<div class="card clan-page-card">

    <div class="card-header d-flex justify-content-between align-items-center">

        <h5 class="mb-0">
            <i class="fas fa-bell mr-2 text-primary"></i>
            <?= __('notifications') ?>
        </h5>

        <a
            href="create.php"
            class="btn btn-primary btn-sm"
        >
            <i class="fas fa-plus mr-1"></i>
            Create Notification
        </a>

    </div>

    <div class="card-body">

        <?php if (isset($_GET['success'])): ?>

            <div class="alert alert-success">
                Notification operation completed successfully.
            </div>

        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>

            <div class="alert alert-danger">
                Notification operation failed.
            </div>

        <?php endif; ?>


        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead>

                <tr>
                    <th>#</th>
                    <th><?= __('title') ?></th>
                    <th><?= __('type') ?></th>
                    <th><?= __('status') ?></th>
                    <th><?= __('created') ?></th>
                    <th width="180"><?= __('actions') ?></th>
                </tr>

                </thead>

                <tbody>

                <?php if (!empty($notifications)): ?>

                    <?php foreach ($notifications as $i => $notification): ?>

                        <tr>

                            <td><?= $i + 1 ?></td>

                            <td>
                                <strong>
                                    <?php $isBirthday = strtolower($notification['notification_type'] ?? '') === 'birthday'; ?>
                                    <?php if ($isBirthday): ?>
                                        <i class="fas fa-birthday-cake text-pink mr-1"></i>
                                    <?php endif; ?>
                                    <?= htmlspecialchars(
                                        $notification['title'] ?? ''
                                    ) ?>
                                </strong>
                            </td>

                            <td>
                                <?php $isBirthday = strtolower($notification['notification_type'] ?? '') === 'birthday'; ?>
                                <?php if ($isBirthday): ?>
                                    <span class="badge" style="background:#ec4899; color:#fff;">
                                        <i class="fas fa-birthday-cake mr-1"></i>
                                        <?= htmlspecialchars(
                                            $notification['notification_type']
                                            ?: 'General'
                                        ) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-info">
                                        <?= htmlspecialchars(
                                            $notification['notification_type']
                                            ?: 'General'
                                        ) ?>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>

                                <?php if (!empty($notification['is_read'])): ?>

                                    <span class="badge badge-success">
                                        Read
                                    </span>

                                <?php else: ?>

                                    <span class="badge badge-warning">
                                        Unread
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $notification['created_at']
                                ) ?>
                            </td>

                            <td>

                                <a
                                    href="view.php?id=<?= (int)$notification['notification_id'] ?>"
                                    class="btn btn-info btn-sm"
                                    title="View"
                                >
                                    <i class="fas fa-eye"></i>
                                </a>

                                <a
                                    href="delete.php?id=<?= (int)$notification['notification_id'] ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Delete this notification?');"
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
                            colspan="6"
                            class="text-center text-muted py-5"
                        >

                            <i class="fas fa-bell-slash fa-3x mb-3"></i>

                            <p class="mb-0">
                                <?= __('no_notifications_found') ?>
                            </p>

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