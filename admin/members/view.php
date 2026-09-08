<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireMember();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$database = new Database();
$pdo = $database->connect();

$clanId = getCurrentClanId();

$stmt = $pdo->prepare("
    SELECT
        u.*,

        r.role_name,

        father.member_code AS father_code,
        father.first_name AS father_first_name,
        father.middle_name AS father_middle_name,
        father.last_name AS father_last_name,

        mother.member_code AS mother_code,
        mother.first_name AS mother_first_name,
        mother.middle_name AS mother_middle_name,
        mother.last_name AS mother_last_name,

        spouse.member_code AS spouse_code,
        spouse.first_name AS spouse_first_name,
        spouse.middle_name AS spouse_middle_name,
        spouse.last_name AS spouse_last_name,

        g.generation_name

    FROM users u

    LEFT JOIN roles r
        ON r.role_id = u.role_id

    LEFT JOIN users father
        ON father.user_id = u.father_user_id

    LEFT JOIN users mother
        ON mother.user_id = u.mother_user_id

    LEFT JOIN users spouse
        ON spouse.user_id = u.spouse_user_id

    LEFT JOIN member_generations mg
        ON mg.user_id = u.user_id

    LEFT JOIN generations g
        ON g.generation_id = mg.generation_id

    WHERE u.user_id = ?
      AND u.clan_id = ?
    LIMIT 1
");

$stmt->execute([$id, $clanId]);

$member = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$member) {
    header('Location: index.php?error=' . urlencode('Member not found.'));
    exit;
}

$fullName = trim(
    ($member['first_name'] ?? '') . ' ' .
    ($member['middle_name'] ?? '') . ' ' .
    ($member['last_name'] ?? '')
);

$profileImage = !empty($member['profile_picture'])
    ? '../../uploads/profile_pictures/' . $member['profile_picture']
    : '../../assets/images/default-avatar.png';

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

<i class="fas fa-user mr-2 text-primary"></i>

Member Details

</h3>

<div class="card-tools">

<a
href="edit.php?id=<?= $id ?>"
class="btn btn-primary btn-sm"
>

<i class="fas fa-edit mr-1"></i>

Edit

</a>

<a
href="index.php"
class="btn btn-secondary btn-sm"
>

<i class="fas fa-arrow-left mr-1"></i>

Back

</a>

</div>

</div>

<div class="card-body">

<div class="row">

<!-- PROFILE -->

<div class="col-lg-4">

<div class="card clan-page-card h-100">

<div class="card-body text-center">

<img
src="<?= htmlspecialchars($profileImage) ?>"
class="img-fluid rounded-circle mb-3"
style="width:150px;height:150px;object-fit:cover;"
onerror="this.onerror=null;this.src='../../assets/images/default-avatar.png';"
>

<h4>

<?= htmlspecialchars($fullName) ?>

</h4>

<p class="text-muted mb-2">

<?= htmlspecialchars($member['member_code']) ?>

</p>

<?php if ($member['account_status'] === 'active'): ?>

<span class="badge badge-success">

Active

</span>

<?php else: ?>

<span class="badge badge-danger">

Inactive

</span>

<?php endif; ?>

<?php if (!empty($member['is_deceased'])): ?>

<div class="mt-2">

<span class="badge badge-dark">

<i class="fas fa-star mr-1"></i>

Deceased

</span>

</div>

<?php endif; ?>

</div>

</div>

</div>


<!-- INFORMATION -->

<div class="col-lg-8">

<div class="card clan-page-card">

<div class="card-header">

<h5 class="mb-0">

<i class="fas fa-id-card mr-2 text-primary"></i>

Personal Information

</h5>

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered">

<tr>
<th width="35%">Member Code</th>
<td><?= htmlspecialchars($member['member_code']) ?></td>
</tr>

<tr>
<th>First Name</th>
<td><?= htmlspecialchars($member['first_name']) ?></td>
</tr>

<tr>
<th>Middle Name</th>
<td><?= htmlspecialchars($member['middle_name'] ?? '-') ?></td>
</tr>

<tr>
<th>Last Name</th>
<td><?= htmlspecialchars($member['last_name']) ?></td>
</tr>

<tr>
<th><?= __('gender') ?></th>
<td><?= htmlspecialchars($member['gender']) ?></td>
</tr>

<tr>
<th>Date of Birth</th>
<td><?= htmlspecialchars($member['date_of_birth'] ?? '-') ?></td>
</tr>

<tr>
<th><?= __('phone') ?></th>
<td><?= htmlspecialchars($member['phone'] ?? '-') ?></td>
</tr>

<tr>
<th><?= __('email') ?></th>
<td><?= htmlspecialchars($member['email'] ?? '-') ?></td>
</tr>

<tr>
<th>National ID</th>
<td><?= htmlspecialchars($member['national_id'] ?? '-') ?></td>
</tr>

<tr>
<th>Birth Place</th>
<td><?= htmlspecialchars($member['birth_place'] ?? '-') ?></td>
</tr>

<tr>
<th>Current Residence</th>
<td><?= htmlspecialchars($member['current_residence'] ?? '-') ?></td>
</tr>

<tr>
<th><?= __('occupation') ?></th>
<td><?= htmlspecialchars($member['occupation'] ?? '-') ?></td>
</tr>

<tr>
<th>Marital Status</th>
<td><?= htmlspecialchars($member['marital_status'] ?? '-') ?></td>
</tr>

<tr>
<th><?= __('role') ?></th>
<td><?= htmlspecialchars($member['role_name'] ?? '-') ?></td>
</tr>

<tr>
<th><?= __('generation') ?></th>
<td><?= htmlspecialchars($member['generation_name'] ?? '-') ?></td>
</tr>

<tr>
<th>Family Member Type</th>
<td><?= htmlspecialchars($member['family_member_type'] ?? '-') ?></td>
</tr>

</table>

</div>

</div>

</div>

</div>


<!-- FAMILY RELATIONSHIPS -->

<div class="col-12 mt-3">

<div class="card clan-page-card">

<div class="card-header">

<h5 class="mb-0">

<i class="fas fa-users mr-2 text-primary"></i>

Family Relationships

</h5>

</div>

<div class="card-body">

<div class="row">

<div class="col-md-4">

<strong>Father</strong>

<div class="mt-2">

<?php if (!empty($member['father_user_id'])): ?>

<a
href="view.php?id=<?= (int)$member['father_user_id'] ?>"
>

<?= htmlspecialchars(
trim(
$member['father_first_name'] . ' ' .
$member['father_last_name']
)
) ?>

</a>

<div class="text-muted small">

<?= htmlspecialchars($member['father_code']) ?>

</div>

<?php else: ?>

<span class="text-muted">

Unknown

</span>

<?php endif; ?>

</div>

</div>


<div class="col-md-4">

<strong>Mother</strong>

<div class="mt-2">

<?php if (!empty($member['mother_user_id'])): ?>

<a
href="view.php?id=<?= (int)$member['mother_user_id'] ?>"
>

<?= htmlspecialchars(
trim(
$member['mother_first_name'] . ' ' .
$member['mother_last_name']
)
) ?>

</a>

<div class="text-muted small">

<?= htmlspecialchars($member['mother_code']) ?>

</div>

<?php else: ?>

<span class="text-muted">

Unknown

</span>

<?php endif; ?>

</div>

</div>


<div class="col-md-4">

<strong>Spouse</strong>

<div class="mt-2">

<?php if (!empty($member['spouse_user_id'])): ?>

<a
href="view.php?id=<?= (int)$member['spouse_user_id'] ?>"
>

<?= htmlspecialchars(
trim(
$member['spouse_first_name'] . ' ' .
$member['spouse_last_name']
)
) ?>

</a>

<div class="text-muted small">

<?= htmlspecialchars($member['spouse_code']) ?>

</div>

<?php else: ?>

<span class="text-muted">

Not Married

</span>

<?php endif; ?>

</div>

</div>

</div>

</div>

</div>

</div>


<!-- BIOGRAPHY -->

<div class="col-12 mt-3">

<div class="card clan-page-card">

<div class="card-header">

<h5 class="mb-0">

<i class="fas fa-book-open mr-2 text-primary"></i>

Biography

</h5>

</div>

<div class="card-body">

<?= !empty($member['biography'])
    ? nl2br(htmlspecialchars($member['biography']))
    : '<span class="text-muted">No biography available.</span>'
?>

</div>

</div>

</div>

</div>

</div>

<div class="card-footer">

<a
href="edit.php?id=<?= $id ?>"
class="btn btn-primary"
>

<i class="fas fa-edit mr-1"></i>

Edit Member

</a>

<a
href="index.php"
class="btn btn-secondary"
>

Back

</a>

</div>

</div>

</div>

</section>

</div>

<?php

require_once '../../includes/dashboard/footer.php';

?>