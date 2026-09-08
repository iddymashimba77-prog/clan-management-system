<?php

require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/language.php';
require_once '../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$clanId = getCurrentClanId();

$user_id = (int)$_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Get User Profile
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        u.user_id,
        u.first_name,
        u.middle_name,
        u.last_name,
        u.gender,
        u.date_of_birth,
        u.phone,
        u.email,
        u.username,
        u.profile_picture,
        u.profile_picture_self,
        u.birth_place,
        u.current_residence,
        u.marital_status,
        u.biography,
        u.is_deceased,
        u.account_status,
        r.role_name

    FROM users u

    LEFT JOIN roles r
        ON u.role_id = r.role_id

    WHERE u.user_id = ?

      AND u.clan_id = ?

    LIMIT 1
");

$stmt->execute([
    $user_id,
    $clanId
]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die(__('user_profile_not_found'));
}


/*
|--------------------------------------------------------------------------
| Full Name
|--------------------------------------------------------------------------
*/

$full_name = trim(
    $user['first_name'] . ' ' .
    ($user['middle_name'] ?? '') . ' ' .
    $user['last_name']
);


/*
|--------------------------------------------------------------------------
| Role
|--------------------------------------------------------------------------
*/

$role = $user['role_name'] ?? 'Member';

$userRoles = getUserRoles($user_id);

if (!empty($userRoles)) {

    $role = implode(', ', $userRoles);
}


/*
|--------------------------------------------------------------------------
| Profile Image
|--------------------------------------------------------------------------
*/

$profileImage = '';

$selfPicture = $user['profile_picture_self'] ?? null;

if (!empty($selfPicture)) {

    $profileImage =
        BASE_URL .
        'uploads/profiles/' .
        htmlspecialchars($selfPicture);

} elseif (!empty($user['profile_picture'])) {

    $officialFolder =
        file_exists(
            $_SERVER['DOCUMENT_ROOT'] .
            '/Clan_Management_System/uploads/profile_pictures/' .
            $user['profile_picture']
        )
            ? 'profile_pictures'
            : 'profiles';

    $profileImage =
        BASE_URL .
        'uploads/' . $officialFolder . '/' .
        htmlspecialchars($user['profile_picture']);

}


/*
|--------------------------------------------------------------------------
| Success / Error Messages
|--------------------------------------------------------------------------
*/

$success = $_GET['success'] ?? '';

$error = $_GET['error'] ?? '';


/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

define(
    'USER_ROLE',
    $role
);

require_once '../includes/dashboard/header.php';


/*
|--------------------------------------------------------------------------
| Correct Sidebar
|--------------------------------------------------------------------------
*/

$roleLower =
    strtolower(trim($role));

if (isAdministrator()) {

    require_once '../includes/dashboard/sidebars/admin.php';

} elseif ($roleLower === 'chairperson') {

    require_once '../includes/dashboard/sidebars/chairperson.php';

} elseif ($roleLower === 'treasurer') {

    require_once '../includes/dashboard/sidebars/treasurer.php';

} elseif ($roleLower === 'secretary') {

    require_once '../includes/dashboard/sidebars/secretary.php';

} else {

    require_once '../includes/dashboard/sidebars/member.php';

}

require_once '../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content profile-page">

<div class="container-fluid">


<?php if ($success): ?>

<div class="alert alert-success">

<i class="fas fa-check-circle"></i>

<?= htmlspecialchars($success) ?>

</div>

<?php endif; ?>


<?php if ($error): ?>

<div class="alert alert-danger">

<i class="fas fa-exclamation-circle"></i>

<?= htmlspecialchars($error) ?>

</div>

<?php endif; ?>


<!-- PROFILE HEADER -->

<div class="card card-primary">

<div class="card-body box-profile">

<div class="text-center">

<?php if ($profileImage): ?>

<img
class="profile-user-img img-fluid img-circle"
src="<?= $profileImage ?>"
alt="<?= __('profile_picture') ?>"
style="
width:120px;
height:120px;
object-fit:cover;
"
>

<?php else: ?>

<div
class="profile-user-img img-fluid img-circle d-flex align-items-center justify-content-center"
style="
width:120px;
height:120px;
margin:auto;
background:#e9ecef;
font-size:50px;
color:#6c757d;
"
>

<i class="fas fa-user"></i>

</div>

<?php endif; ?>

</div>


<h3 class="profile-username text-center mt-3">

<?= htmlspecialchars($full_name) ?>

</h3>


<p class="text-muted text-center">

<span class="badge badge-primary">

<?= __(strtolower($role)) ?>

</span>

</p>


<p class="text-muted text-center">

<?= htmlspecialchars(
    $user['email'] ?? ''
) ?>

</p>


<ul class="list-group list-group-unbordered mb-3">

<li class="list-group-item">

<b><?= __('username') ?></b>

<span class="float-right">

<?= htmlspecialchars(
    $user['username']
) ?>

</span>

</li>


<li class="list-group-item">

<b><?= __('phone') ?></b>

<span class="float-right">

<?= htmlspecialchars(
    $user['phone'] ?? '-'
) ?>

</span>

</li>


<li class="list-group-item">

<b><?= __('marital_status') ?></b>

<span class="float-right">

<?= htmlspecialchars(
    $user['marital_status'] ?? '-'
) ?>

</span>

</li>

</ul>

</div>

</div>


<div class="row">


<!-- EDIT PROFILE -->

<div class="col-md-7">

<div class="card card-primary">

<div class="card-header">

<h3 class="card-title">

<i class="fas fa-user-edit"></i>

<?= __('edit_profile') ?>

</h3>

</div>


<form
action="update.php"
method="POST"
enctype="multipart/form-data"
>


<div class="card-body">


<input
type="hidden"
name="user_id"
value="<?= $user_id ?>"
>


<div class="form-group">

<label><?= __('first_name') ?></label>

<input
type="text"
name="first_name"
class="form-control"
value="<?= htmlspecialchars(
    $user['first_name']
) ?>"
required
>

</div>


<div class="form-group">

<label><?= __('middle_name') ?></label>

<input
type="text"
name="middle_name"
class="form-control"
value="<?= htmlspecialchars(
    $user['middle_name'] ?? ''
) ?>"
>

</div>


<div class="form-group">

<label><?= __('last_name') ?></label>

<input
type="text"
name="last_name"
class="form-control"
value="<?= htmlspecialchars(
    $user['last_name']
) ?>"
required
>

</div>


<div class="form-group">

<label><?= __('username') ?></label>

<input
type="text"
name="username"
class="form-control"
value="<?= htmlspecialchars(
    $user['username']
) ?>"
required
>

</div>


<div class="form-group">

<label><?= __('email') ?></label>

<input
type="email"
name="email"
class="form-control"
value="<?= htmlspecialchars(
    $user['email'] ?? ''
) ?>"
>

</div>


<div class="form-group">

<label><?= __('phone') ?></label>

<input
type="text"
name="phone"
class="form-control"
value="<?= htmlspecialchars(
    $user['phone'] ?? ''
) ?>"
>

</div>


<div class="form-group">

<label><?= __('current_residence') ?></label>

<input
type="text"
name="current_residence"
class="form-control"
value="<?= htmlspecialchars(
    $user['current_residence'] ?? ''
) ?>"
>

</div>


<div class="form-group">

<label><?= __('biography') ?></label>

<textarea
name="biography"
class="form-control"
rows="4"
><?= htmlspecialchars(
    $user['biography'] ?? ''
) ?></textarea>

</div>


<div class="form-group">

<label><?= __('profile_picture') ?></label>

<div class="custom-file">

<input
type="file"
name="profile_picture"
class="custom-file-input"
id="profile_picture"
accept=".jpg,.jpeg,.png,.webp"
>

<label
class="custom-file-label"
for="profile_picture"
>

<?= __('choose_profile_picture') ?>

</label>

</div>

<small class="text-muted">

<?= __('profile_picture_allowed_formats') ?>

</small>

</div>


</div>


<div class="card-footer">

<button
type="submit"
class="btn btn-primary"
>

<i class="fas fa-save"></i>

<?= __('save_changes') ?>

</button>

</div>

</form>

</div>

</div>


<!-- SECURITY -->

<div class="col-md-5">

<div class="card card-warning">

<div class="card-header">

<h3 class="card-title">

<i class="fas fa-lock"></i>

<?= __('security') ?>

</h3>

</div>


<div class="card-body">

<p>

<?= __('account_security_advice') ?>

</p>


<a
href="change_password.php"
class="btn btn-warning btn-block"
>

<i class="fas fa-key"></i>

<?= __('change_password') ?>

</a>

</div>

</div>


<!-- ACCOUNT INFORMATION -->

<div class="card card-secondary">

<div class="card-header">

<h3 class="card-title">

<?= __('account_information') ?>

</h3>

</div>


<div class="card-body">

<strong>
<?= __('role') ?>
</strong>

<p class="text-muted">

<?= htmlspecialchars($role) ?>

</p>


<strong>
<?= __('account_status') ?>
</strong>

<p>

<?php if ($user['account_status'] === 'active'): ?>

<span class="badge badge-success">

<?= __('active') ?>

</span>

<?php else: ?>

<span class="badge badge-danger">

<?= __(strtolower($user['account_status'])) ?>

</span>

<?php endif; ?>

</p>

</div>

</div>

</div>

</div>

</div>

</section>


<script>

document
.getElementById('profile_picture')
.addEventListener(
    'change',
    function () {

        const fileName =
            this.files.length
            ? this.files[0].name
            : '<?= __('choose_profile_picture') ?>';

        this.nextElementSibling.innerText =
            fileName;

    }
);

</script>

</div>

</div>

<?php

require_once '../includes/dashboard/footer.php';

?>