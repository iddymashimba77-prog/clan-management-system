<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = $database->connect();

$userId = $_SESSION['user_id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT
        u.user_id,
        r.role_name
    FROM users u
    LEFT JOIN roles r ON r.role_id = u.role_id
    WHERE u.user_id = ?
      AND u.is_deleted = 0
      AND u.account_status = 'active'
    LIMIT 1
");

$stmt->execute([$userId]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || strtolower($user['role_name']) !== 'administrator') {
    http_response_code(403);
    exit('Access denied.');
}


/*
|--------------------------------------------------------------------------
| Get settings
|--------------------------------------------------------------------------
*/

$settings = [];

try {

    $stmt = $pdo->query("
        SELECT *
        FROM clan_settings
        LIMIT 1
    ");

    $settings = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

} catch (PDOException $e) {
    $settings = [];
}

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/admin.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">

<div class="card clan-page-card">

    <div class="card-header">

        <h5 class="mb-0">
            <i class="fas fa-cog mr-2 text-primary"></i>
            System <?= __('settings') ?>
        </h5>

    </div>

    <div class="card-body">

        <form method="POST" action="update_settings.php">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
            >

            <div class="row">

                <?php foreach ($settings as $key => $value): ?>

                    <?php if ($key === 'id' || $key === 'created_at' || $key === 'updated_at') continue; ?>

                    <div class="col-md-6 mb-3">

                        <label class="font-weight-bold">
                            <?= htmlspecialchars(ucwords(str_replace('_', ' ', $key))) ?>
                        </label>

                        <?php if (strlen((string)$value) > 100): ?>

                            <textarea
                                name="<?= htmlspecialchars($key) ?>"
                                class="form-control"
                                rows="4"
                            ><?= htmlspecialchars($value ?? '') ?></textarea>

                        <?php else: ?>

                            <input
                                type="text"
                                name="<?= htmlspecialchars($key) ?>"
                                value="<?= htmlspecialchars($value ?? '') ?>"
                                class="form-control"
                            >

                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>

            </div>

            <?php if (!$settings): ?>

                <div class="alert alert-warning">
                    No system settings record was found in <code>clan_settings</code>.
                </div>

            <?php else: ?>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i>
                    Save <?= __('settings') ?>
                </button>

            <?php endif; ?>

        </form>

    </div>

</div>

</div>

</section>

</div>

<?php require_once '../../includes/dashboard/footer.php'; ?>