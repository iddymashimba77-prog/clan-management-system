<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->query("
    SELECT
        user_id,
        member_code,
        first_name,
        middle_name,
        last_name
    FROM users
    WHERE is_deleted = 0
      AND account_status = 'active'
    ORDER BY first_name ASC, last_name ASC
");

$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebar.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">

<div class="card clan-page-card">

    <div class="card-header">

        <h5 class="mb-0">
            <i class="fas fa-bell mr-2 text-primary"></i>
            <?= __('create_notification') ?>
        </h5>

    </div>

    <div class="card-body">

        <form
            method="POST"
            action="store.php"
        >

            <div class="form-group">

                <label>
                    Recipient
                </label>

                <select
                    name="user_id"
                    class="form-control"
                >

                    <option value="0">
                        All Members
                    </option>

                    <?php foreach ($members as $member): ?>

                        <option value="<?= (int)$member['user_id'] ?>">

                            <?= htmlspecialchars(
                                $member['member_code']
                            ) ?>

                            -
                            <?= htmlspecialchars(
                                trim(
                                    $member['first_name'] . ' ' .
                                    ($member['middle_name'] ?? '') . ' ' .
                                    $member['last_name']
                                )
                            ) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="form-group">

                <label>
                    Notification Type
                </label>

                <select
                    name="notification_type"
                    class="form-control"
                    required
                >

                    <option value="General">
                        General
                    </option>

                    <option value="Contribution">
                        Contribution
                    </option>

                    <option value="Wallet">
                        Wallet
                    </option>

                    <option value="Event">
                        Event
                    </option>

                    <option value="Family">
                        Family
                    </option>

                    <option value="System">
                        System
                    </option>

                    <option value="Important">
                        Important
                    </option>

                </select>

            </div>


            <div class="form-group">

                <label>
                    Title
                </label>

                <input
                    type="text"
                    name="title"
                    class="form-control"
                    maxlength="255"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Message
                </label>

                <textarea
                    name="message"
                    class="form-control"
                    rows="6"
                    required
                ></textarea>

            </div>


            <div class="d-flex justify-content-between">

                <a
                    href="index.php"
                    class="btn btn-secondary"
                >
                    <i class="fas fa-arrow-left mr-1"></i>
                    Back
                </a>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <i class="fas fa-paper-plane mr-1"></i>
                    <?= __('create_notification') ?>
                </button>

            </div>

        </form>

    </div>

</div>

</div>

</section>

</div>

<?php require_once '../../includes/dashboard/footer.php'; ?>