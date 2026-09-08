<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireSecretary();

$database=new Database();
$pdo=$database->connect();

$id=(int)($_GET['id']??0);

$stmt=$pdo->prepare("
UPDATE notifications
SET is_read=1
WHERE notification_id=?
AND user_id=?
");

$stmt->execute([$id,$_SESSION['user_id']]);

$stmt=$pdo->prepare("
SELECT *
FROM notifications
WHERE notification_id=?
AND user_id=?
");

$stmt->execute([$id,$_SESSION['user_id']]);

$n=$stmt->fetch(PDO::FETCH_ASSOC);

define('USER_ROLE','Secretary');

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/secretary.php';
require_once '../../includes/dashboard/navbar.php';
?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid" style="max-width: 1200px; margin: 0 auto;">

<div class="card">

<div class="card-header">

<?php $isBirthday = strtolower($n['notification_type'] ?? '') === 'birthday'; ?>

<h3>
<?php if ($isBirthday): ?>
<i class="fas fa-birthday-cake text-pink mr-2"></i>
<?php endif; ?>
<?= htmlspecialchars($n['title']) ?>
</h3>

</div>

<div class="card-body">

<p><?= nl2br(htmlspecialchars($n['message'])) ?></p>

<hr>

<small>

<?= date('d M Y H:i',strtotime($n['created_at'])) ?>

</small>

<br><br>

<a href="index.php" class="btn btn-secondary">

<?= __('back') ?>

</a>

</div>

</div>

</div>

</section>

<?php require_once '../../includes/dashboard/footer.php'; ?>