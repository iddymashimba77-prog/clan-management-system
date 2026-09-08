<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database = new Database();
$pdo = $database->connect();

$user_id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
SELECT
u.*,
r.role_name
FROM users u
LEFT JOIN roles r
ON u.role_id=r.role_id
WHERE u.user_id=?
LIMIT 1
");

$stmt->execute([$user_id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$user){
    die("User not found.");
}

$roles = $pdo->query("
SELECT role_id,role_name
FROM roles
ORDER BY role_name
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

Edit User Account

</h3>

</div>

<div class="card-body">

<form method="POST" action="update.php">

<input
type="hidden"
name="user_id"
value="<?= $user['user_id'] ?>">

<div class="form-group">

<label>Member</label>

<input
type="text"
class="form-control"
readonly
value="<?= htmlspecialchars(
$user['member_code']." - ".
trim(
$user['first_name']." ".
$user['middle_name']." ".
$user['last_name']
)
) ?>">

</div>

<div class="form-group">

<label>Username</label>

<input
type="text"
name="username"
class="form-control"
value="<?= htmlspecialchars($user['username']) ?>"
required>

</div>

<div class="form-group">

<label>Email</label>

<input
type="email"
name="email"
class="form-control"
value="<?= htmlspecialchars($user['email']) ?>">

</div>

<div class="form-group">

<label>Phone</label>

<input
type="text"
name="phone"
class="form-control"
value="<?= htmlspecialchars($user['phone']) ?>">

</div>

<div class="form-group">

<label>Role</label>

<select
name="role_id"
class="form-control">

<?php foreach($roles as $role): ?>

<option
value="<?= $role['role_id'] ?>"
<?= $role['role_id']==$user['role_id'] ? 'selected':'' ?>>

<?= htmlspecialchars($role['role_name']) ?>

</option>

<?php endforeach; ?>

</select>

</div>

<button
class="btn btn-success">

Update Account

</button>

</form>

<hr>

<h4>Reset Password</h4>

<?php if(isset($_GET['success'])): ?>

<div class="alert alert-success">

Password updated successfully.

</div>

<?php endif; ?>

<?php if(isset($_GET['error'])): ?>

<div class="alert alert-danger">

Password must be at least 6 characters.

</div>

<?php endif; ?>

<form action="reset_password.php" method="POST">

<input
type="hidden"
name="user_id"
value="<?= $user['user_id'] ?>">

<div class="form-group">

<label>New Password</label>

<input
type="password"
name="password"
class="form-control"
required>

</div>

<button
type="submit"
class="btn btn-danger">

<i class="fas fa-key"></i>

Reset Password

</button>

</form>

<a
href="index.php"
class="btn btn-secondary">

Cancel

</a>

</div>

</div>

</div>

</section>

<?php
require_once '../../includes/dashboard/footer.php';