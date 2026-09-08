<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireAdministrator();

$database = new Database();
$pdo = $database->connect();

$clanId = getCurrentClanId();

$user_id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $user_id = (int)$_POST['user_id'];
    $role_id = (int)$_POST['role_id'];

    // Administrator asibadilishwe
    $stmt = $pdo->prepare("
        SELECT role_name
        FROM roles
        WHERE role_id=(
            SELECT role_id
            FROM users
            WHERE user_id=?
              AND clan_id=?
        )
    ");
    $stmt->execute([$user_id, $clanId]);
    $currentRole = $stmt->fetchColumn();

    if($currentRole == 'Administrator'){
        die("Administrator role cannot be changed.");
    }

    // Chairperson mmoja tu
    if($role_id == 2){

        $stmt = $pdo->prepare("
        UPDATE users
        SET role_id=5
        WHERE role_id=2
          AND clan_id=?
        ");
        $stmt->execute([$clanId]);

    }

    // Secretary mmoja tu
    if($role_id == 3){

        $stmt = $pdo->prepare("
        UPDATE users
        SET role_id=5
        WHERE role_id=3
          AND clan_id=?
        ");
        $stmt->execute([$clanId]);

    }

    // Treasurer mmoja tu
    if($role_id == 4){

        $stmt = $pdo->prepare("
        UPDATE users
        SET role_id=5
        WHERE role_id=4
          AND clan_id=?
        ");
        $stmt->execute([$clanId]);

    }

    $stmt=$pdo->prepare("
    UPDATE users
    SET role_id=?
    WHERE user_id=?
      AND clan_id=?
    ");

    $stmt->execute([
        $role_id,
        $user_id,
        $clanId
    ]);

    header("Location:index.php");
    exit;
}

$stmt=$pdo->prepare("
SELECT *
FROM users
WHERE user_id=?
  AND clan_id=?
");
$stmt->execute([$user_id, $clanId]);
$user=$stmt->fetch(PDO::FETCH_ASSOC);

$roles=$pdo->query("
SELECT *
FROM roles
WHERE role_name<>'Administrator'
ORDER BY role_id
")->fetchAll(PDO::FETCH_ASSOC);

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebar.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid" style="max-width: 1200px; margin: 0 auto;">

<div class="card">

<div class="card-header">

<h3 class="card-title">
Assign Leadership
</h3>

</div>

<div class="card-body">

<form method="POST">

<input
type="hidden"
name="user_id"
value="<?= $user['user_id'] ?>">

<div class="form-group">

<label><?= __('member') ?></label>

<input
class="form-control"
readonly
value="<?= htmlspecialchars(
$user['member_code'].' - '.$user['first_name'].' '.$user['last_name']
) ?>">

</div>

<div class="form-group">

<label><?= __('role') ?></label>

<select
name="role_id"
class="form-control">

<?php foreach($roles as $role): ?>

<option
value="<?= $role['role_id'] ?>"
<?= $role['role_id']==$user['role_id']?'selected':'' ?>>

<?= htmlspecialchars($role['role_name']) ?>

</option>

<?php endforeach; ?>

</select>

</div>

<button
class="btn btn-success">

<i class="fas fa-save"></i>

Save

</button>

<a
href="index.php"
class="btn btn-secondary">

Cancel

</a>

</form>

</div>

</div>

</div>

</section>

<?php
require_once '../../includes/dashboard/footer.php';