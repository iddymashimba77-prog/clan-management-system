<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireAdministrator();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$database = new Database();
$pdo = $database->connect();

$clanId = getCurrentClanId();


/*
|--------------------------------------------------------------------------
| Member
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM users
    WHERE user_id = ?
      AND clan_id = ?
    LIMIT 1
");

$stmt->execute([$id, $clanId]);

$member = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$member) {
    header('Location: index.php?error=' . urlencode('Member not found.'));
    exit;
}


/*
|--------------------------------------------------------------------------
| Roles
|--------------------------------------------------------------------------
*/

$roles = $pdo->query("
    SELECT role_id, role_name
    FROM roles
    ORDER BY role_id
")->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Generations
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT generation_id, generation_name
    FROM generations
    WHERE clan_id = ?
    ORDER BY generation_id
");
$stmt->execute([$clanId]);
$generations = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Current generation
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT generation_id
    FROM member_generations
    WHERE user_id = ?
      AND clan_id = ?
    LIMIT 1
");

$stmt->execute([$id, $clanId]);

$currentGeneration = $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Other members
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        user_id,
        member_code,
        first_name,
        middle_name,
        last_name,
        gender
    FROM users
    WHERE is_deleted = 0
      AND user_id != ?
      AND clan_id = ?
    ORDER BY first_name, last_name
");

$stmt->execute([$id, $clanId]);

$members = $stmt->fetchAll(PDO::FETCH_ASSOC);


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

<i class="fas fa-user-edit mr-2 text-primary"></i>

Edit Member

</h3>

</div>

<form
action="update.php"
method="POST"
enctype="multipart/form-data"
>

<input
type="hidden"
name="user_id"
value="<?= (int)$member['user_id'] ?>"
>

<div class="card-body">

<div class="row">


<!-- PERSONAL -->

<div class="col-12">

<h5 class="text-primary mb-3">

<i class="fas fa-user mr-2"></i>

Personal Information

</h5>

</div>


<div class="col-md-4">

<div class="form-group">

<label>Member Code</label>

<input
type="text"
class="form-control"
value="<?= htmlspecialchars($member['member_code']) ?>"
readonly
>

</div>

</div>


<div class="col-md-4">

<div class="form-group">

<label>First Name *</label>

<input
type="text"
name="first_name"
class="form-control"
value="<?= htmlspecialchars($member['first_name']) ?>"
required
>

</div>

</div>


<div class="col-md-4">

<div class="form-group">

<label>Middle Name</label>

<input
type="text"
name="middle_name"
class="form-control"
value="<?= htmlspecialchars($member['middle_name'] ?? '') ?>"
>

</div>

</div>


<div class="col-md-4">

<div class="form-group">

<label>Last Name *</label>

<input
type="text"
name="last_name"
class="form-control"
value="<?= htmlspecialchars($member['last_name']) ?>"
required
>

</div>

</div>


<div class="col-md-4">

<div class="form-group">

<label>Gender *</label>

<select
name="gender"
class="form-control"
required
>

<option value="Male"
<?= $member['gender'] === 'Male' ? 'selected' : '' ?>
>
Male
</option>

<option value="Female"
<?= $member['gender'] === 'Female' ? 'selected' : '' ?>
>
Female
</option>

</select>

</div>

</div>


<div class="col-md-4">

<div class="form-group">

<label>Date of Birth</label>

<input
type="date"
name="date_of_birth"
class="form-control"
value="<?= htmlspecialchars($member['date_of_birth'] ?? '') ?>"
>

</div>

</div>


<div class="col-md-4">

<div class="form-group">

<label>National ID</label>

<input
type="text"
name="national_id"
class="form-control"
value="<?= htmlspecialchars($member['national_id'] ?? '') ?>"
>

</div>

</div>


<div class="col-md-4">

<div class="form-group">

<label><?= __('phone') ?></label>

<input
type="text"
name="phone"
class="form-control"
value="<?= htmlspecialchars($member['phone'] ?? '') ?>"
>

</div>

</div>


<div class="col-md-4">

<div class="form-group">

<label><?= __('email') ?></label>

<input
type="email"
name="email"
class="form-control"
value="<?= htmlspecialchars($member['email'] ?? '') ?>"
>

</div>

</div>


<div class="col-md-4">

<div class="form-group">

<label><?= __('username') ?></label>

<input
type="text"
name="username"
class="form-control"
value="<?= htmlspecialchars($member['username'] ?? '') ?>"
>

</div>

</div>


<!-- LOCATION -->

<div class="col-12 mt-3">

<h5 class="text-primary mb-3">

<i class="fas fa-map-marker-alt mr-2"></i>

Location & Background

</h5>

</div>


<div class="col-md-6">

<div class="form-group">

<label>Birth Place</label>

<input
type="text"
name="birth_place"
class="form-control"
value="<?= htmlspecialchars($member['birth_place'] ?? '') ?>"
>

</div>

</div>


<div class="col-md-6">

<div class="form-group">

<label>Current Residence</label>

<input
type="text"
name="current_residence"
class="form-control"
value="<?= htmlspecialchars($member['current_residence'] ?? '') ?>"
>

</div>

</div>


<div class="col-md-6">

<div class="form-group">

<label><?= __('occupation') ?></label>

<input
type="text"
name="occupation"
class="form-control"
value="<?= htmlspecialchars($member['occupation'] ?? '') ?>"
>

</div>

</div>


<div class="col-md-6">

<div class="form-group">

<label>Marital Status</label>

<select
name="marital_status"
class="form-control"
>

<?php foreach (
    ['Single','Married','Divorced','Widowed']
    as $status
): ?>

<option
value="<?= $status ?>"
<?= $member['marital_status'] === $status ? 'selected' : '' ?>
>

<?= $status ?>

</option>

<?php endforeach; ?>

</select>

</div>

</div>


<!-- FAMILY -->

<div class="col-12 mt-3">

<h5 class="text-primary mb-3">

<i class="fas fa-users mr-2"></i>

Family Relationships

</h5>

</div>


<div class="col-md-4">

<div class="form-group">

<label><?= __('father') ?></label>

<select
name="father_user_id"
class="form-control"
>

<option value="">Select Father</option>

<?php foreach ($members as $m): ?>

<?php if (($m['gender'] ?? '') !== 'Male') continue; ?>

<option
value="<?= (int)$m['user_id'] ?>"
<?= (int)$member['father_user_id'] === (int)$m['user_id']
    ? 'selected'
    : '' ?>
>

<?= htmlspecialchars(
$m['member_code'].' - '.
$m['first_name'].' '.
$m['last_name']
) ?>

</option>

<?php endforeach; ?>

</select>

</div>

</div>


<div class="col-md-4">

<div class="form-group">

<label><?= __('mother') ?></label>

<select
name="mother_user_id"
class="form-control"
>

<option value="">Select Mother</option>

<?php foreach ($members as $m): ?>

<?php if (($m['gender'] ?? '') !== 'Female') continue; ?>

<option
value="<?= (int)$m['user_id'] ?>"
<?= (int)$member['mother_user_id'] === (int)$m['user_id']
    ? 'selected'
    : '' ?>
>

<?= htmlspecialchars(
$m['member_code'].' - '.
$m['first_name'].' '.
$m['last_name']
) ?>

</option>

<?php endforeach; ?>

</select>

</div>

</div>


<div class="col-md-4">

<div class="form-group">

<label><?= __('spouse') ?></label>

<select
name="spouse_user_id"
class="form-control"
>

<option value="">Select Spouse</option>

<?php
$spouseGender = ($member['gender'] ?? '') === 'Male'
    ? 'Female'
    : 'Male';
?>

<?php foreach ($members as $m): ?>

<?php if (($m['gender'] ?? '') !== $spouseGender) continue; ?>

<option
value="<?= (int)$m['user_id'] ?>"
<?= (int)$member['spouse_user_id'] === (int)$m['user_id']
    ? 'selected'
    : '' ?>
>

<?= htmlspecialchars(
$m['member_code'].' - '.
$m['first_name'].' '.
$m['last_name']
) ?>

</option>

<?php endforeach; ?>

</select>

</div>

</div>


<div class="col-md-4">

<div class="form-group">

<label>Family Member Type</label>

<select
name="family_member_type"
class="form-control"
>

<option
value="blood"
<?= $member['family_member_type'] === 'blood'
    ? 'selected'
    : '' ?>
>
Blood
</option>

<option
value="marriage"
<?= $member['family_member_type'] === 'marriage'
    ? 'selected'
    : '' ?>
>
Marriage
</option>

</select>

</div>

</div>


<div class="col-md-4">

<div class="form-group">

<label><?= __('generation') ?></label>

<select
name="generation_id"
class="form-control"
>

<option value="">No Generation</option>

<?php foreach ($generations as $generation): ?>

<option
value="<?= (int)$generation['generation_id'] ?>"
<?= (int)$currentGeneration ===
    (int)$generation['generation_id']
    ? 'selected'
    : '' ?>
>

<?= htmlspecialchars(
$generation['generation_name']
) ?>

</option>

<?php endforeach; ?>

</select>

</div>

</div>


<div class="col-md-4">

<div class="form-group">

<label><?= __('role') ?></label>

<select
name="role_id"
class="form-control"
>

<?php foreach ($roles as $role): ?>

<option
value="<?= (int)$role['role_id'] ?>"
<?= (int)$member['role_id'] ===
    (int)$role['role_id']
    ? 'selected'
    : '' ?>
>

<?= htmlspecialchars($role['role_name']) ?>

</option>

<?php endforeach; ?>

</select>

</div>

</div>


<!-- STATUS -->

<div class="col-md-6">

<div class="form-group">

<label>Account Status</label>

<select
name="account_status"
class="form-control"
>

<option
value="active"
<?= $member['account_status'] === 'active'
    ? 'selected'
    : '' ?>
>
Active
</option>

<option
value="inactive"
<?= $member['account_status'] === 'inactive'
    ? 'selected'
    : '' ?>
>
Inactive
</option>

</select>

</div>

</div>


<div class="col-md-6">

<div class="form-group">

<label>Deceased Status</label>

<select
name="is_deceased"
class="form-control"
>

<option
value="0"
<?= empty($member['is_deceased'])
    ? 'selected'
    : '' ?>
>
Alive
</option>

<option
value="1"
<?= !empty($member['is_deceased'])
    ? 'selected'
    : '' ?>
>
Deceased
</option>

</select>

</div>

</div>


<div class="col-md-4">

<div class="form-group">

<label>Death Date</label>

<input
type="date"
name="death_date"
class="form-control"
value="<?= htmlspecialchars($member['death_date'] ?? '') ?>"
>

</div>

</div>


<div class="col-md-4">

<div class="form-group">

<label>Death Place</label>

<input
type="text"
name="death_place"
class="form-control"
value="<?= htmlspecialchars($member['death_place'] ?? '') ?>"
>

</div>

</div>


<!-- IMAGES -->

<div class="col-md-12 mt-3">

<h5 class="text-primary mb-3">

<i class="fas fa-images mr-2"></i>

Photos

</h5>

</div>


<div class="col-md-6">

<div class="form-group">

<label>Profile Picture</label>

<input
type="file"
name="profile_picture"
class="form-control-file"
accept="image/*"
>

<?php if (!empty($member['profile_picture'])): ?>

<div class="mt-2">

<img
src="../../uploads/profile_pictures/<?= htmlspecialchars($member['profile_picture']) ?>"
style="width:100px;height:100px;object-fit:cover;border-radius:50%;"
>

</div>

<?php endif; ?>

</div>

</div>


<div class="col-md-6">

<div class="form-group">

<label>Cover Photo</label>

<input
type="file"
name="cover_photo"
class="form-control-file"
accept="image/*"
>

<?php if (!empty($member['cover_photo'])): ?>

<div class="mt-2">

<img
src="../../uploads/cover_photos/<?= htmlspecialchars($member['cover_photo']) ?>"
style="width:180px;height:80px;object-fit:cover;"
>

</div>

<?php endif; ?>

</div>

</div>


<!-- BIO -->

<div class="col-md-12">

<div class="form-group">

<label><?= __('biography') ?></label>

<textarea
name="biography"
rows="6"
class="form-control"
><?= htmlspecialchars($member['biography'] ?? '') ?></textarea>

</div>

</div>

</div>

</div>

<div class="card-footer">

<a
href="view.php?id=<?= $id ?>"
class="btn btn-secondary"
>

Cancel

</a>

<button
type="submit"
class="btn btn-primary float-right"
>

<i class="fas fa-save mr-1"></i>

Update Member

</button>

</div>

</form>

</div>

</div>

</section>

</div>

<?php

require_once '../../includes/dashboard/footer.php';

?>