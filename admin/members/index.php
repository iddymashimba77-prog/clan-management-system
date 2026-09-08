<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireAdministrator();

$database = new Database();
$pdo = $database->connect();

$clanId = getCurrentClanId();

/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

$totalMembers = $pdo->prepare("
    SELECT COUNT(*)
    FROM users
    WHERE is_deleted = 0
      AND clan_id = ?
");
$totalMembers->execute([$clanId]);
$totalMembers = $totalMembers->fetchColumn();

$activeMembers = $pdo->prepare("
    SELECT COUNT(*)
    FROM users
    WHERE is_deleted = 0
      AND account_status = 'active'
      AND clan_id = ?
");
$activeMembers->execute([$clanId]);
$activeMembers = $activeMembers->fetchColumn();

$inactiveMembers = $pdo->prepare("
    SELECT COUNT(*)
    FROM users
    WHERE is_deleted = 0
      AND account_status = 'inactive'
      AND clan_id = ?
");
$inactiveMembers->execute([$clanId]);
$inactiveMembers = $inactiveMembers->fetchColumn();

$maleMembers = $pdo->prepare("
    SELECT COUNT(*)
    FROM users
    WHERE is_deleted = 0
      AND gender = 'Male'
      AND clan_id = ?
");
$maleMembers->execute([$clanId]);
$maleMembers = $maleMembers->fetchColumn();

$femaleMembers = $pdo->prepare("
    SELECT COUNT(*)
    FROM users
    WHERE is_deleted = 0
      AND gender = 'Female'
      AND clan_id = ?
");
$femaleMembers->execute([$clanId]);
$femaleMembers = $femaleMembers->fetchColumn();

$deceasedMembers = $pdo->prepare("
    SELECT COUNT(*)
    FROM users
    WHERE is_deleted = 0
      AND is_deceased = 1
      AND clan_id = ?
");
$deceasedMembers->execute([$clanId]);
$deceasedMembers = $deceasedMembers->fetchColumn();


/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? '');

$sql = "
    SELECT
        u.user_id,
        u.member_code,
        u.first_name,
        u.middle_name,
        u.last_name,
        u.gender,
        u.phone,
        u.email,
        u.account_status,
        u.is_deceased,
        u.created_at,
        r.role_name
    FROM users u
    LEFT JOIN roles r
        ON r.role_id = u.role_id
    WHERE u.is_deleted = 0
      AND u.clan_id = ?
";

$params = [$clanId];

if ($search !== '') {

    $sql .= "
        AND (
            u.member_code LIKE ?
            OR u.first_name LIKE ?
            OR u.middle_name LIKE ?
            OR u.last_name LIKE ?
            OR u.phone LIKE ?
            OR u.email LIKE ?
            OR u.username LIKE ?
        )
    ";

    $term = "%{$search}%";

    array_push($params, $term, $term, $term, $term, $term, $term, $term);
}

$sql .= " ORDER BY u.user_id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$members = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Dashboard layout
|--------------------------------------------------------------------------
*/

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebar.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">

<!-- HEADER -->

<div class="card clan-page-card">

<div class="card-body">

<div class="d-flex justify-content-between align-items-center flex-wrap">

<div>

<h3 class="mb-1">

<i class="fas fa-users text-primary mr-2"></i>

Member Management

</h3>

<p class="text-muted mb-0">

Manage all clan members and their accounts.

</p>

</div>

<div class="mt-2 mt-md-0">

<a href="create.php" class="btn btn-primary">

<i class="fas fa-user-plus mr-1"></i>

Add Member

</a>

</div>

</div>

</div>

</div>


<!-- STATISTICS -->

<div class="row">

<div class="col-lg-3 col-md-6">

<div class="small-box bg-primary">

<div class="inner">

<h3><?= number_format($totalMembers); ?></h3>

<p>Total Members</p>

</div>

<div class="icon">

<i class="fas fa-users"></i>

</div>

</div>

</div>


<div class="col-lg-3 col-md-6">

<div class="small-box bg-success">

<div class="inner">

<h3><?= number_format($activeMembers); ?></h3>

<p>Active Members</p>

</div>

<div class="icon">

<i class="fas fa-user-check"></i>

</div>

</div>

</div>


<div class="col-lg-3 col-md-6">

<div class="small-box bg-warning">

<div class="inner">

<h3><?= number_format($inactiveMembers); ?></h3>

<p>Inactive Members</p>

</div>

<div class="icon">

<i class="fas fa-user-clock"></i>

</div>

</div>

</div>


<div class="col-lg-3 col-md-6">

<div class="small-box bg-danger">

<div class="inner">

<h3><?= number_format($deceasedMembers); ?></h3>

<p>Deceased Members</p>

</div>

<div class="icon">

<i class="fas fa-star"></i>

</div>

</div>

</div>

</div>


<!-- GENDER SUMMARY -->

<div class="row">

<div class="col-md-6">

<div class="card clan-page-card">

<div class="card-body">

<h5>

<i class="fas fa-mars text-primary mr-2"></i>

Male Members

</h5>

<h2><?= number_format($maleMembers); ?></h2>

</div>

</div>

</div>


<div class="col-md-6">

<div class="card clan-page-card">

<div class="card-body">

<h5>

<i class="fas fa-venus text-danger mr-2"></i>

Female Members

</h5>

<h2><?= number_format($femaleMembers); ?></h2>

</div>

</div>

</div>

</div>


<!-- SEARCH -->

<div class="card clan-page-card">

<div class="card-body">

<form method="GET">

<div class="input-group">

<input
type="search"
name="search"
class="form-control"
value="<?= htmlspecialchars($search); ?>"
placeholder="Search member name, code, phone, email..."
autocomplete="off"
>

<div class="input-group-append">

<button
type="submit"
class="btn btn-primary"
>

<i class="fas fa-search mr-1"></i>

Search

</button>

<?php if ($search !== ''): ?>

<a
href="index.php"
class="btn btn-outline-secondary"
>

Clear

</a>

<?php endif; ?>

</div>

</div>

</form>

</div>

</div>


<!-- MEMBERS TABLE -->

<div class="card clan-page-card">

<div class="card-header">

<h3 class="card-title">

<i class="fas fa-list mr-2"></i>

All Members

</h3>

</div>

<div class="card-body table-responsive">

<table
class="table table-bordered table-hover"
>

<thead>

<tr>

<th>#</th>
<th>Member Code</th>
<th>Full Name</th>
<th><?= __('gender') ?></th>
<th><?= __('phone') ?></th>
<th><?= __('role') ?></th>
<th><?= __('status') ?></th>
<th><?= __('created') ?></th>
<th width="170"><?= __('actions') ?></th>

</tr>

</thead>

<tbody>

<?php if (!empty($members)): ?>

<?php foreach ($members as $index => $member): ?>

<tr>

<td><?= $index + 1; ?></td>

<td>

<strong>

<?= htmlspecialchars($member['member_code']); ?>

</strong>

</td>

<td>

<?= htmlspecialchars(

trim(

$member['first_name'] . ' ' .

$member['middle_name'] . ' ' .

$member['last_name']

)

); ?>

</td>

<td>

<?php if ($member['gender'] === 'Male'): ?>

<span class="badge badge-primary">

Male

</span>

<?php else: ?>

<span class="badge badge-danger">

Female

</span>

<?php endif; ?>

</td>

<td>

<?= htmlspecialchars($member['phone'] ?? ''); ?>

</td>

<td>

<span class="badge badge-info">

<?= htmlspecialchars(
$member['role_name'] ?? 'Member'
); ?>

</span>

</td>

<td>

<?php if ($member['account_status'] === 'active'): ?>

<span class="badge badge-success">

Active

</span>

<?php else: ?>

<span class="badge badge-warning">

Inactive

</span>

<?php endif; ?>

</td>

<td>

<?= date(
'd M Y',
strtotime($member['created_at'])
); ?>

</td>

<td>

<a
href="view.php?id=<?= (int)$member['user_id']; ?>"
class="btn btn-info btn-sm"
title="View"
>

<i class="fas fa-eye"></i>

</a>

<a
href="edit.php?id=<?= (int)$member['user_id']; ?>"
class="btn btn-primary btn-sm"
title="Edit"
>

<i class="fas fa-edit"></i>

</a>

<a
href="toggle_status.php?id=<?= (int)$member['user_id']; ?>"
class="btn btn-warning btn-sm"
title="Change Status"
>

<i class="fas fa-power-off"></i>

</a>

<a
href="delete.php?id=<?= (int)$member['user_id']; ?>"
class="btn btn-danger btn-sm"
title="Delete"
onclick="return confirm('Are you sure you want to delete this member?');"
>

<i class="fas fa-trash"></i>

</a>

</td>

</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>

<td colspan="9" class="text-center py-4">

<i class="fas fa-users-slash fa-2x text-muted mb-2"></i>

<br>

No members found.

</td>

</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</div>

</div>

</section>

</div>

<?php

require_once '../../includes/dashboard/footer.php';

?>