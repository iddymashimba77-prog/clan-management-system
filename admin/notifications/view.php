<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$id = isset($_GET['id'])
    ? (int)$_GET['id']
    : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        n.*,

        CONCAT(
            u.first_name, ' ',
            COALESCE(u.middle_name, ''), ' ',
            u.last_name
        ) AS member_name,

        u.member_code

    FROM notifications n

    LEFT JOIN users u
        ON u.user_id = n.user_id

    WHERE n.notification_id = ?
      AND n.user_id = ?

    LIMIT 1
");

$stmt->execute([$id, $_SESSION['user_id']]);

$notification = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$notification) {
    header('Location: index.php');
    exit;
}


/*
 * Mark as read when administrator opens it.
 */

if (empty($notification['is_read'])) {

    $update = $pdo->prepare("
        UPDATE notifications
        SET is_read = 1
        WHERE notification_id = ?
          AND user_id = ?
        LIMIT 1
    ");

    $update->execute([$id, $_SESSION['user_id']]);

    $notification['is_read'] = 1;
}


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

            <?= __('notification_details') ?>

        </h5>

        <a
            href="index.php"
            class="btn btn-outline-primary btn-sm"
        >
            <i class="fas fa-arrow-left mr-1"></i>
            Back
        </a>

    </div>


    <div class="card-body">

        <div class="row">

            <div class="col-md-8">

                <h3>
                    <?php $isBirthday = strtolower($notification['notification_type'] ?? '') === 'birthday'; ?>
                    <?php if ($isBirthday): ?>
                        <i class="fas fa-birthday-cake text-pink mr-2"></i>
                    <?php endif; ?>
                    <?= htmlspecialchars(
                        $notification['title'] ?? ''
                    ) ?>
                </h3>

                <div class="mb-3">

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

                    <?php if (!empty($notification['is_read'])): ?>

                        <span class="badge badge-success">
                            Read
                        </span>

                    <?php else: ?>

                        <span class="badge badge-warning">
                            Unread
                        </span>

                    <?php endif; ?>

                </div>


                <div class="card bg-light">

                    <div class="card-body">

                        <?= nl2br(
                            htmlspecialchars(
                                $notification['message'] ?? ''
                            )
                        ) ?>

                    </div>

                </div>

            </div>


            <div class="col-md-4">

                <div class="border rounded p-3">

                    <small class="text-muted d-block">
                        Recipient
                    </small>

                    <strong>

                        <?= htmlspecialchars(
                            $notification['member_name']
                            ?: 'Unknown'
                        ) ?>

                    </strong>

                    <small class="d-block text-muted">

                        <?= htmlspecialchars(
                            $notification['member_code'] ?? ''
                        ) ?>

                    </small>


                    <hr>


                    <small class="text-muted d-block">
                        Created At
                    </small>

                    <strong>
                        <?= htmlspecialchars(
                            $notification['created_at']
                        ) ?>
                    </strong>

                </div>

            </div>

        </div>

    </div>

</div>

</div>

</section>

</div>

<?php require_once '../../includes/dashboard/footer.php'; ?>