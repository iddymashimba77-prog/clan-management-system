<?php

require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';

requireLogin();

if (!hasRole(ROLE_CHAIRPERSON)) {
    header("Location: ../login.php");
    exit;
}

$database = new Database();
$pdo = $database->connect();

$totalMembers = $pdo->query("
SELECT COUNT(*) FROM users
WHERE is_deleted=0
")->fetchColumn();

$totalMale = $pdo->query("
SELECT COUNT(*) FROM users
WHERE gender='Male'
AND is_deleted=0
")->fetchColumn();

$totalFemale = $pdo->query("
SELECT COUNT(*) FROM users
WHERE gender='Female'
AND is_deleted=0
")->fetchColumn();

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/navbar.php';

?>

<div class="card">

<h2>Welcome, <?= htmlspecialchars($_SESSION['full_name']); ?></h2>

<p>Clan Management System Administration Panel</p>

</div>

<div class="dashboard-cards">

<div class="dashboard-card">

<h5>Total Members</h5>

<h2><?= $totalMembers; ?></h2>

</div>

<div class="dashboard-card">

<h5>Male Members</h5>

<h2><?= $totalMale; ?></h2>

</div>

<div class="dashboard-card">

<h5>Female Members</h5>

<h2><?= $totalFemale; ?></h2>

</div>

<div class="dashboard-card">

<h5>System Status</h5>

<h2>Online</h2>

</div>

</div>

<div class="card quick-actions">

<h4>Quick Actions</h4>

<br>

<a href="members/create.php" class="btn btn-success">
<i class="fa fa-user-plus"></i>
Add Member
</a>

<a href="members/index.php" class="btn btn-primary">
<i class="fa fa-users"></i>
Manage Members
</a>

</div>

<?php

include 'includes/footer.php';

?>