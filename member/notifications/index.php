<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireMember();

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

define('USER_ROLE','Member');

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/member.php';
require_once '../../includes/dashboard/navbar.php';
?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid" style="max-width: 1200px; margin: 0 auto;">

<div class="card">

<div class="card-header">

<h3 class="card-title">

<?= __('notifications') ?>

</h3>

</div>

<div class="card-body">

<table class="table table-bordered table-striped">

<thead>

<tr>

<th><?= __('title') ?></th>
<th><?= __('type') ?></th>
<th><?= __('message') ?></th>
<th><?= __('date') ?></th>
<th><?= __('status') ?></th>
<th><?= __('action') ?></th>

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

<td>
<?php if ($isBirthday): ?>
<span class="badge" style="background:#ec4899; color:#fff;">
<i class="fas fa-birthday-cake mr-1"></i>
<?= htmlspecialchars($n['notification_type'] ?: 'General') ?>
</span>
<?php else: ?>
<span class="badge badge-info">
<?= htmlspecialchars($n['notification_type'] ?: 'General') ?>
</span>
<?php endif; ?>
</td>

<td><?= htmlspecialchars($n['message']) ?></td>

<td><?= date('d M Y H:i',strtotime($n['created_at'])) ?></td>

<td>

<?php if($n['is_read']==0): ?>

<span class="badge badge-danger">

<?= __('unread') ?>

</span>

<?php else: ?>

<span class="badge badge-success">

<?= __('read') ?>

</span>

<?php endif; ?>

</td>

<td>

<a
href="view.php?id=<?= $n['notification_id'] ?>"
class="btn btn-primary btn-sm">

<?= __('open') ?>

</a>

</td>

<td>

<a
href="delete.php?id=<?= $n['notification_id'] ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('<?= __('are_you_sure_you_want_to_delete_this_notification') ?>');">

<?= __('delete') ?>

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