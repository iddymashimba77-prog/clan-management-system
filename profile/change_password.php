<?php

require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/language.php';
require_once '../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$clanId = getCurrentClanId();

$user_id =
    (int)$_SESSION['user_id'];

$error = '';

$success = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $current_password =
        $_POST['current_password'] ?? '';

    $new_password =
        $_POST['new_password'] ?? '';

    $confirm_password =
        $_POST['confirm_password'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (
        $current_password === '' ||
        $new_password === '' ||
        $confirm_password === ''
    ) {

        $error =
            __('change_password_error_all_fields');

    } elseif (
        $new_password !== $confirm_password
    ) {

        $error =
            __('change_password_error_mismatch');

    } elseif (
        strlen($new_password) < 8
    ) {

        $error =
            __('change_password_error_min_length');

    } else {


        /*
        |--------------------------------------------------------------------------
        | Get Current Password
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            SELECT password_hash

            FROM users

            WHERE user_id = ?

              AND clan_id = ?

            LIMIT 1
        ");

        $stmt->execute([
            $user_id,
            $clanId
        ]);

        $passwordHash =
            $stmt->fetchColumn();


        /*
        |--------------------------------------------------------------------------
        | Verify Current Password
        |--------------------------------------------------------------------------
        */

        if (
            !$passwordHash ||
            !password_verify(
                $current_password,
                $passwordHash
            )
        ) {

            $error =
                __('change_password_error_current_incorrect');

        } else {


            /*
            |--------------------------------------------------------------------------
            | Prevent Same Password
            |--------------------------------------------------------------------------
            */

            if (
                password_verify(
                    $new_password,
                    $passwordHash
                )
            ) {

                $error =
                    __('change_password_error_same_as_current');

            } else {


                /*
                |--------------------------------------------------------------------------
                | Hash New Password
                |--------------------------------------------------------------------------
                */

                $newHash =
                    password_hash(
                        $new_password,
                        PASSWORD_DEFAULT
                    );


                /*
                |--------------------------------------------------------------------------
                | Update Password
                |--------------------------------------------------------------------------
                */

                $stmt = $pdo->prepare("
                    UPDATE users

                    SET password_hash = ?

                    WHERE user_id = ?

                      AND clan_id = ?
                ");

                $stmt->execute([
                    $newHash,
                    $user_id,
                    $clanId
                ]);


                $success =
                    __('password_changed_successfully');
            }
        }
    }
}


define(
    'USER_ROLE',
    $_SESSION['role_name'] ?? 'Member'
);

require_once '../includes/dashboard/header.php';


$role =
    strtolower(
        trim(
            $_SESSION['role_name']
            ?? 'member'
        )
    );


if (isAdministrator()) {

    require_once '../includes/dashboard/sidebars/admin.php';

} elseif ($role === 'chairperson') {

    require_once '../includes/dashboard/sidebars/chairperson.php';

} elseif ($role === 'treasurer') {

    require_once '../includes/dashboard/sidebars/treasurer.php';

} elseif ($role === 'secretary') {

    require_once '../includes/dashboard/sidebars/secretary.php';

} else {

    require_once '../includes/dashboard/sidebars/member.php';

}

require_once '../includes/dashboard/navbar.php';

?>

<section class="content">

<div class="container-fluid">

<div class="row justify-content-center">

<div class="col-md-7">

<div class="card card-warning">

<div class="card-header">

<h3 class="card-title">

<i class="fas fa-lock"></i>

<?= __('change_password') ?>

</h3>

</div>


<div class="card-body">


<?php if ($error): ?>

<div class="alert alert-danger">

<?= htmlspecialchars($error) ?>

</div>

<?php endif; ?>


<?php if ($success): ?>

<div class="alert alert-success">

<?= htmlspecialchars($success) ?>

</div>

<?php endif; ?>


<form method="POST">


<div class="form-group">

<label>
<?= __('current_password') ?>
</label>

<input
type="password"
name="current_password"
class="form-control"
required
>

</div>


<div class="form-group">

<label>
<?= __('new_password') ?>
</label>

<input
type="password"
name="new_password"
class="form-control"
minlength="8"
required
>

<small class="text-muted">

<?= __('minimum_8_characters') ?>

</small>

</div>


<div class="form-group">

<label>
<?= __('confirm_new_password') ?>
</label>

<input
type="password"
name="confirm_password"
class="form-control"
minlength="8"
required
>

</div>


<div class="form-group">

<button
type="submit"
class="btn btn-warning"
>

<i class="fas fa-key"></i>

<?= __('change_password') ?>

</button>


<a
href="index.php"
class="btn btn-secondary"
>

<?= __('back_to_profile') ?>

</a>

</div>


</form>

</div>

</div>

</div>

</div>

</div>

</section>

<?php

require_once '../includes/dashboard/footer.php';

?>