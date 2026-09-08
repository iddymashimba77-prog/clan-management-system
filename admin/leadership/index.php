<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireAdministrator();

$database = new Database();
$pdo = $database->connect();

$clanId = getCurrentClanId();

$stmt = $pdo->prepare("
SELECT
u.user_id,
u.member_code,
CONCAT(
u.first_name,' ',
IFNULL(CONCAT(u.middle_name,' '),''),
u.last_name
) AS full_name,
r.role_name
FROM users u
LEFT JOIN roles r
ON u.role_id=r.role_id
WHERE u.is_deleted=0
AND u.clan_id = ?
ORDER BY
CASE
WHEN r.role_name='Administrator' THEN 1
WHEN r.role_name='Chairperson' THEN 2
WHEN r.role_name='Secretary' THEN 3
WHEN r.role_name='Treasurer' THEN 4
ELSE 5
END,
u.first_name
");
$stmt->execute([$clanId]);

$leaders = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

Leadership Management

</h3>

<a href="assign.php" class="btn btn-success float-right">

<i class="fas fa-user-plus"></i>

Assign <?= __('leadership') ?>

</a>

</div>

<div class="card-body">

<table class="table table-bordered table-striped">

<thead>

<tr>

<th>#</th>

<th>Member Code</th>

<th>Full Name</th>

<th>Current Role</th>

<th width="180"><?= __('action') ?></th>

</tr>

</thead>

<tbody>

<?php $i=1; foreach($leaders as $leader): ?>

<tr>

<td><?= $i++ ?></td>

<td><?= htmlspecialchars($leader['member_code']) ?></td>

<td><?= htmlspecialchars($leader['full_name']) ?></td>

<td>

<?php

switch($leader['role_name']){

case 'Administrator':
echo '<span class="badge badge-danger">Administrator</span>';
break;

case 'Chairperson':
echo '<span class="badge badge-success">Chairperson</span>';
break;

case 'Secretary':
echo '<span class="badge badge-info">Secretary</span>';
break;

case 'Treasurer':
echo '<span class="badge badge-warning">Treasurer</span>';
break;

default:
echo '<span class="badge badge-secondary">Member</span>';
}

?>

</td>

<td>

<a
href="assign.php?id=<?= $leader['user_id'] ?>"
class="btn btn-primary btn-sm">

<i class="fas fa-user-edit"></i>

Assign

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

<?php
require_once '../../includes/dashboard/footer.php';