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
| Existing members
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
      AND clan_id = ?
    ORDER BY first_name, last_name
");
$stmt->execute([$clanId]);
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

<i class="fas fa-user-plus mr-2 text-primary"></i>

Add New Member

</h3>

</div>

<form
action="store.php"
method="POST"
enctype="multipart/form-data"
>

<div class="card-body">

<div class="row">

<!-- PERSONAL INFORMATION -->

<div class="col-md-12">

<h5 class="text-primary mb-3">

<i class="fas fa-user mr-2"></i>

Personal Information

</h5>

</div>


<div class="col-md-4">

<div class="form-group">

<label>First Name *</label>

<input
type="text"
name="first_name"
class="form-control"
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

<option value="">Select Gender</option>

<option value="Male"><?= __('male') ?></option>

<option value="Female"><?= __('female') ?></option>

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
>

</div>

</div>


<!-- LOCATION -->

<div class="col-md-12 mt-3">

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

<option value="Single"><?= __('single') ?></option>

<option value="Married"><?= __('married') ?></option>

<option value="Divorced"><?= __('divorced') ?></option>

<option value="Widowed"><?= __('widowed') ?></option>

</select>

</div>

</div>


<!-- FAMILY -->

<div class="col-md-12 mt-3">

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

<option value="<?= (int)$m['user_id']; ?>">

<?= htmlspecialchars(
$m['member_code'].' - '.
$m['first_name'].' '.
$m['last_name']
); ?>

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

<option value="<?= (int)$m['user_id']; ?>">

<?= htmlspecialchars(
$m['member_code'].' - '.
$m['first_name'].' '.
$m['last_name']
); ?>

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

<?php foreach ($members as $m): ?>

<option value="<?= (int)$m['user_id']; ?>">

<?= htmlspecialchars(
$m['member_code'].' - '.
$m['first_name'].' '.
$m['last_name']
); ?>

</option>

<?php endforeach; ?>

</select>

</div>

</div>


<div class="col-md-6">

<div class="form-group">

<label>Family Member Type</label>

<select
name="family_member_type"
class="form-control"
>

<option value="blood"><?= __('blood') ?></option>

<option value="marriage"><?= __('marriage') ?></option>

</select>

</div>

</div>


<div class="col-md-6">

<div class="form-group">

<label><?= __('generation') ?></label>

<select
name="generation_id"
class="form-control"
>

<option value="">Select Generation</option>

<?php foreach ($generations as $generation): ?>

<option value="<?= (int)$generation['generation_id']; ?>">

<?= htmlspecialchars(
$generation['generation_name']
); ?>

</option>

<?php endforeach; ?>

</select>

</div>

</div>


<!-- ACCOUNT -->

<div class="col-md-12 mt-3">

<h5 class="text-primary mb-3">

<i class="fas fa-user-shield mr-2"></i>

Account & Access

</h5>

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
value="<?= (int)$role['role_id']; ?>"
<?= strtolower($role['role_name']) === 'member'
? 'selected'
: ''; ?>
>

<?= htmlspecialchars(
$role['role_name']
); ?>

</option>

<?php endforeach; ?>

</select>

</div>

</div>


<div class="col-md-4">

<div class="form-group">

<label>Account Status</label>

<select
name="account_status"
class="form-control"
>

<option value="active"><?= __('active') ?></option>

<option value="inactive"><?= __('inactive') ?></option>

</select>

</div>

</div>


<div class="col-md-4">

<div class="form-group">

<label>Password *</label>

<input
type="password"
name="password"
class="form-control"
required
>

</div>

</div>


<!-- MEDIA -->

<div class="col-md-6">

<div class="form-group">

<label>Profile Picture</label>

<input
type="file"
name="profile_picture"
class="form-control-file"
accept="image/*"
>

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

</div>

</div>


<!-- BIOGRAPHY -->

<div class="col-md-12">

<div class="form-group">

<label><?= __('biography') ?></label>

<textarea
name="biography"
rows="5"
class="form-control"
></textarea>

</div>

</div>


<!-- DECEASED -->

<div class="col-md-12">

<div class="custom-control custom-checkbox">

<input
type="checkbox"
name="is_deceased"
value="1"
class="custom-control-input"
id="isDeceased"
>

<label
class="custom-control-label"
for="isDeceased"
>

Mark member as deceased

</label>

</div>

</div>

</div>

</div>


<div class="card-footer">

<a
href="index.php"
class="btn btn-secondary"
>

<i class="fas fa-arrow-left mr-1"></i>

Cancel

</a>

<button
type="submit"
class="btn btn-primary float-right"
>

<i class="fas fa-save mr-1"></i>

Save Member

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