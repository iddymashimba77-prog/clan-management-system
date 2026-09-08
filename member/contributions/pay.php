<?php


require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireClanUser();

require_once '../../config/tenant.php';
$clanId = getCurrentClanId();

// Get database name from session first
$databaseName = getTenantDatabaseName();

// If session doesn't have database name, try to get from clan_owner
if ($databaseName === null || $databaseName === '') {
    try {
        $ownerPdo = new PDO(
            'mysql:unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=clan_owner;charset=utf8mb4',
            'root',
            '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
        $stmt = $ownerPdo->prepare("SELECT database_name FROM owner_installations WHERE clan_id = ? LIMIT 1");
        $stmt->execute([$clanId]);
        $databaseName = $stmt->fetchColumn();
        
        // Set it in session for future requests
        if ($databaseName && session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['database_name'] = $databaseName;
        }
    } catch (Exception $e) {
        // Fallback to default
        $databaseName = null;
    }
}

try {
    $database = new Database($databaseName);
    $pdo = $database->connect();
} catch (Exception $e) {
    die(__('db_connection_failed') . " " . $e->getMessage());
}

$contribution_id = (int)($_GET['id'] ?? 0);

try {
    $stmt = $pdo->prepare("
SELECT
ec.*,
e.title AS event_name
FROM event_contributions ec
INNER JOIN events e
ON ec.event_id=e.event_id
WHERE ec.contribution_id=?
AND ec.clan_id=?
LIMIT 1
");

    $stmt->execute([$contribution_id, $clanId]);

    $contribution = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$contribution){
        die(__('contribution_not_found'));
    }
} catch (PDOException $e) {
    die(__('query_failed') . " " . $e->getMessage());
}

$stmt=$pdo->prepare("
SELECT *
FROM payment_methods
WHERE status='active'
AND clan_id=?
ORDER BY method_name
");
$stmt->execute([$clanId]);
$methods=$stmt->fetchAll(PDO::FETCH_ASSOC);

define('USER_ROLE','Member');

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/'.strtolower(USER_ROLE).'.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content" style="max-width: 1200px; margin: 0 auto;">

<div class="card">

<div class="card-header">

<h3><?= __('pay_contribution') ?></h3>

</div>

<div class="card-body">

<form
action="store.php"
method="POST"
enctype="multipart/form-data">

<input
type="hidden"
name="contribution_id"
value="<?= $contribution['contribution_id'] ?>">

<div class="form-group">

<label><?= __('event') ?></label>

<input
type="text"
class="form-control"
readonly
value="<?= htmlspecialchars($contribution['event_name']) ?>">

</div>

<div class="form-group">

<label><?= __('contribution') ?></label>

<input
type="text"
class="form-control"
readonly
value="<?= htmlspecialchars($contribution['title']) ?>">

</div>

<div class="form-group">

<label><?= __('required_amount') ?></label>

<input
type="text"
class="form-control"
readonly
value="TZS <?= number_format($contribution['required_amount'],2) ?>">

</div>

<div class="form-group">

<label><?= __('amount_paying_now') ?></label>

<input
type="number"
step="0.01"
min="1"
max="<?= $contribution['required_amount'] ?>"
name="amount"
class="form-control"
required>

<small class="text-muted">

<?= __('pay_gradually_hint') ?>

</small>

</div>

<div class="form-group">

<label><?= __('payment_method') ?></label>

<select
name="payment_method_id"
class="form-control"
required>

<?php foreach($methods as $method): ?>

<option value="<?= $method['payment_method_id'] ?>">
<?= htmlspecialchars($method['method_name']) ?>
<?php if(!empty($method['account_name'])): ?> - <?= htmlspecialchars($method['account_name']) ?><?php endif; ?>
<?php if(!empty($method['account_number'])): ?> - <?= htmlspecialchars($method['account_number']) ?><?php endif; ?>
</option>

<?php endforeach; ?>

</select>

</div>

<div class="form-group">

<label><?= __('reference_number') ?></label>

<input
type="text"
name="reference_number"
class="form-control"
required>

</div>

<div class="form-group">

<label><?= __('payment_proof') ?></label>

<input
type="file"
name="payment_proof"
class="form-control"
accept=".jpg,.jpeg,.png,.pdf"
required>

</div>

<a href="history.php" class="btn btn-info">

<?= __('payment_history') ?>

</a>

<button class="btn btn-success">

<?= __('submit_payment') ?>

</button>

<a
href="index.php"
class="btn btn-secondary">

<?= __('cancel') ?>

</a>

</form>

</div>

</div>

</div>

</section>

</div>

<?php
require_once '../../includes/dashboard/footer.php';