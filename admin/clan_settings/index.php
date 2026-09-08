<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/security.php';
require_once __DIR__ . '/../../config/auth.php';

requireAdministrator();


$user_id = $_SESSION['user_id'] ?? null;

$database = new Database();
$pdo = $database->connect();

$clanId = getCurrentClanId();

$stmt = $pdo->prepare("
    SELECT 
        u.user_id,
        u.first_name,
        u.last_name,
        r.role_name
    FROM users u
    LEFT JOIN roles r
        ON r.role_id = u.role_id
    WHERE u.user_id = ?
      AND u.is_deleted = 0
      AND u.clan_id = ?
    LIMIT 1
");

$stmt->execute([$user_id, $clanId]);

$admin = $stmt->fetch(PDO::FETCH_ASSOC);



/*
|--------------------------------------------------------------------------
| SETTINGS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM clan_settings
    WHERE clan_id = ?
    LIMIT 1
");
$stmt->execute([$clanId]);

$settings = $stmt->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| FLASH MESSAGES
|--------------------------------------------------------------------------
*/

$success = $_SESSION['security_success'] ?? '';
$error   = $_SESSION['security_error'] ?? '';

unset($_SESSION['security_success'], $_SESSION['security_error']);


require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebar.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">

<div class="card clan-page-card">

<div class="card-header">

<h3 class="card-title">
    <i class="fas fa-cog mr-2"></i>
    <?= __('clan_settings') ?>
</h3>

</div>

<div class="card-body">


<?php if ($success): ?>

<div class="alert alert-success">
    <i class="fas fa-check-circle mr-2"></i>
    <?= htmlspecialchars($success) ?>
</div>

<?php endif; ?>


<?php if ($error): ?>

<div class="alert alert-danger">
    <i class="fas fa-exclamation-circle mr-2"></i>
    <?= htmlspecialchars($error) ?>
</div>

<?php endif; ?>


<!-- SETTINGS FORM -->

<form action="update.php" method="POST">

<?= csrf_field() ?>

<div class="form-group">

<label>Clan Name</label>

<input
    type="text"
    name="clan_name"
    class="form-control"
    value="<?= htmlspecialchars($settings['clan_name'] ?? '') ?>"
    required
>

</div>


<div class="form-group">

<label><?= __('description') ?></label>

<textarea
    name="description"
    class="form-control"
    rows="4"
><?= htmlspecialchars($settings['description'] ?? '') ?></textarea>

</div>


<hr>

<h5>
    <i class="fas fa-lock mr-2"></i>
    Administrator Confirmation
</h5>

<div class="form-group">

<label>Current Password</label>

<input
    type="password"
    name="admin_password"
    class="form-control"
    required
>

<small class="text-muted">
    Password confirmation is required when changing clan settings.
</small>

</div>


<button type="submit" class="btn btn-primary">

<i class="fas fa-save mr-1"></i>

Save Settings

</button>

</form>


<hr class="my-4">


<!-- BACKUP -->

<div class="card card-outline card-success">

<div class="card-header">

<h3 class="card-title">

<i class="fas fa-database mr-2"></i>

Database Backup

</h3>

</div>

<div class="card-body">

<p class="text-muted">

Create a complete backup of the clan management database.

</p>

<form action="backup.php" method="POST">

<?= csrf_field() ?>

<div class="form-group">

<label>Administrator Password</label>

<input
    type="password"
    name="admin_password"
    class="form-control"
    required
>

</div>

<button
    type="submit"
    class="btn btn-success"
>

<i class="fas fa-download mr-1"></i>

Create Backup

</button>

</form>

</div>

</div>


<hr class="my-4">


<!-- RESTORE -->

<div class="card card-outline card-danger">

<div class="card-header">

<h3 class="card-title text-danger">

<i class="fas fa-exclamation-triangle mr-2"></i>

Restore Database

</h3>

</div>

<div class="card-body">

<div class="alert alert-warning">

<strong>WARNING!</strong>

Restoring a database will replace current database data.

Make sure you have a recent backup before continuing.

</div>


<form
    action="restore.php"
    method="POST"
    enctype="multipart/form-data"
>

<?= csrf_field() ?>


<div class="form-group">

<label>Backup File</label>

<input
    type="file"
    name="backup_file"
    class="form-control"
    accept=".sql,.gz"
    required
>

<small class="text-muted">

Allowed files: .sql and .sql.gz

</small>

</div>


<div class="form-group">

<label>Administrator Password</label>

<input
    type="password"
    name="admin_password"
    class="form-control"
    required
>

</div>


<div class="form-group">

<label>
    Type <strong>RESTORE</strong> to confirm
</label>

<input
    type="text"
    name="restore_confirmation"
    class="form-control"
    autocomplete="off"
    required
>

</div>


<button
    type="submit"
    class="btn btn-danger"
    onclick="return confirm(
        'WARNING: This will replace the current database. Continue?'
    );"
>

<i class="fas fa-database mr-1"></i>

Restore Database

</button>

</form>

</div>

</div>

</div>

</div>

</section>

</div>

<?php

require_once '../../includes/dashboard/footer.php';

?>