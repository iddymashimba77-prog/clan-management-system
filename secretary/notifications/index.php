<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();
requireSecretary();

$database = new Database();
$pdo = $database->connect();

$stmt=$pdo->prepare("
SELECT *
FROM notifications
WHERE user_id=?
ORDER BY created_at DESC
");

$stmt->execute([$_SESSION['user_id']]);

$notifications=$stmt->fetchAll(PDO::FETCH_ASSOC);

define('USER_ROLE','Secretary');

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/secretary.php';
require_once '../../includes/dashboard/navbar.php';
?>

<section class="content">

<div class="container-fluid">

<div class="card">

<div class="card-header">

<h3 class="card-title">

Notifications

</h3>

</div>

<div class="card-body">

<table class="table table-bordered table-striped">

<thead>

<tr>

<th>Title</th>
<th>Message</th>
<th>Date</th>
<th>Status</th>
<th>Action</th>

</tr>

</thead>

<tbody>

<?php foreach($notifications as $n): ?>

<?php $isBirthday = strtolower($n['notification_type'] ?? '') === 'birthday'; ?>

<tr>

<td>
<?php if ($isBirthday): ?>
<i class="fas fa-birthday-cake text-pink mr-1"></i>
<?php endif; ?>
<?= htmlspecialchars($n['title']) ?>
</td>

<td><?= htmlspecialchars($n['message']) ?></td>

<td><?= date('d M Y H:i',strtotime($n['created_at'])) ?></td>

<td>

<?php if($n['is_read']==0): ?>

<span class="badge badge-danger">

Unread

</span>

<?php else: ?>

<span class="badge badge-success">

Read

</span>

<?php endif; ?>

</td>

<td>

<a
href="view.php?id=<?= $n['notification_id'] ?>"
class="btn btn-primary btn-sm">

Open

</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

</div>

</section>

<?php require_once '../../includes/dashboard/footer.php'; ?>