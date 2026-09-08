<?php

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/language.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| User Information
|--------------------------------------------------------------------------
*/

$userName = $_SESSION['username'] ?? 'User';

$fullName = $_SESSION['full_name'] ?? 'User';

$userRole = $_SESSION['role_name'] ?? 'Member';




/*
|--------------------------------------------------------------------------
| Profile Picture
|--------------------------------------------------------------------------
*/

$profilePicture = $_SESSION['profile_picture'] ?? '';

if (!empty($profilePicture)) {

    $avatar = BASE_URL . 'uploads/profiles/' . $profilePicture;

} else {

    $avatar = BASE_URL . 'assets/images/default-avatar.png';

}


/*
|--------------------------------------------------------------------------
| Current Language
|--------------------------------------------------------------------------
*/

$current_language =
    $_SESSION['language'] ?? 'en';

?>

<nav class="main-header navbar navbar-expand navbar-white navbar-light clan-navbar" style="padding-right: 20px; padding-bottom: 20px;">

    <!-- =====================================================
         LEFT SIDE
    ====================================================== -->

    <ul class="navbar-nav">

        <li class="nav-item">

            <a
                class="nav-link clan-menu-toggle"
                data-widget="pushmenu"
                href="#"
                role="button"
            >

                <i class="fas fa-bars"></i>

            </a>

        </li>


        <li class="nav-item d-none d-md-inline-block">

            <a
                href="<?= BASE_URL ?>"
                class="nav-link"
            >

                <i class="fas fa-home mr-1"></i>

                <?= __('home') ?>

            </a>

        </li>

    </ul>


    <!-- =====================================================
         RIGHT SIDE
    ====================================================== -->

    <ul class="navbar-nav ml-auto" style="padding-right: 15px;">


        <!-- =================================================
             LANGUAGE
        ================================================== -->

        <li class="nav-item dropdown">

            <a
                class="nav-link dropdown-toggle clan-nav-action"
                href="#"
                data-toggle="dropdown"
                title="Language"
            >

                <i class="fas fa-language"></i>

                <span class="d-none d-md-inline ml-1">

                    <?= strtoupper($current_language) ?>

                </span>

            </a>


            <div class="dropdown-menu dropdown-menu-right clan-dropdown">

                <div class="dropdown-header">

                    <i class="fas fa-language mr-1"></i>

                    <?= __('language') ?>

                </div>


                <a
                    href="?lang=en"
                    class="dropdown-item
                    <?= $current_language === 'en'
                        ? 'active'
                        : '' ?>"
                >

                    🇬🇧

                    English

                    <?php if ($current_language === 'en'): ?>

                        <i class="fas fa-check text-success float-right mt-1"></i>

                    <?php endif; ?>

                </a>


                <a
                    href="?lang=sw"
                    class="dropdown-item
                    <?= $current_language === 'sw'
                        ? 'active'
                        : '' ?>"
                >

                    🇹🇿

                    Kiswahili

                    <?php if ($current_language === 'sw'): ?>

                        <i class="fas fa-check text-success float-right mt-1"></i>

                    <?php endif; ?>

                </a>

            </div>

        </li>


        <!-- =================================================
             NOTIFICATIONS
        ================================================== -->

        <li class="nav-item dropdown">

    <a
        class="nav-link clan-nav-action position-relative"
        href="#"
        data-toggle="dropdown"
        title="<?= __('notifications') ?>"
    >

        <i class="far fa-bell"></i>

        <span
            id="notificationBadge"
            class="badge badge-danger navbar-badge clan-notification-badge"
            style="display:none; position: absolute; top: -6px; right: -6px; font-size: 0.55rem; padding: 0.15rem 0.35rem; min-width: 16px; height: 16px; line-height: 1; border-radius: 50%;"
        >
            0
        </span>

    </a>


    <div
        class="dropdown-menu dropdown-menu-lg dropdown-menu-right clan-dropdown"
    >

        <span class="dropdown-item dropdown-header">

            <?= __('notifications') ?>

        </span>


        <div class="dropdown-divider"></div>
<?php

        $roleName = strtolower($_SESSION['role_name'] ?? 'member');
        $roleFolder = $roleName === 'administrator' ? 'admin' : $roleName;
?>

<a
    href="<?= BASE_URL . $roleFolder ?>/notifications/index.php"
    class="dropdown-item"
>
    <i class="fas fa-bell mr-2 text-primary"></i>

    <?= __('view') ?>
    <?= __('notifications') ?>

</a>

    </div>

</li>


        <!-- =================================================
             PROFILE
        ================================================== -->

        <li class="nav-item dropdown">

            <a
                class="nav-link dropdown-toggle d-flex align-items-center clan-profile-toggle"
                href="#"
                data-toggle="dropdown"
            >

                <div class="clan-avatar-wrapper">

                    <img
                        src="<?= htmlspecialchars($avatar) ?>"
                        class="clan-avatar"
                        alt="Profile"
                        onerror="this.src='<?= BASE_URL ?>assets/images/default-avatar.png';"
                    >

                    <span class="clan-online-indicator"></span>

                </div>


                <div class="d-none d-md-block ml-2">

                    <div class="clan-navbar-name">

                        <?= htmlspecialchars($userName) ?>

                    </div>

                    <div class="clan-navbar-role">

                        <?= htmlspecialchars($userRole) ?>

                    </div>

                </div>

            </a>


            <div
                class="dropdown-menu dropdown-menu-right clan-profile-menu"
            >

                <!-- PROFILE HEADER -->

                <div class="clan-profile-header">

                    <img
                        src="<?= htmlspecialchars($avatar) ?>"
                        class="clan-profile-avatar"
                        alt="Profile"
                        onerror="this.src='<?= BASE_URL ?>assets/images/default-avatar.png';"
                    >


                    <div class="mt-2">

                        <strong>

                            <?= htmlspecialchars($fullName) ?>

                        </strong>

                        <div class="clan-role-badge">

                            <?= htmlspecialchars($userRole) ?>

                        </div>

                    </div>

                </div>


                <div class="dropdown-divider"></div>


                <!-- PROFILE -->

                <a
                    href="<?= BASE_URL ?>profile/index.php"
                    class="dropdown-item"
                >

                    <i class="fas fa-user-circle mr-2"></i>

                    <?= __('profile') ?>

                </a>


                <!-- ACCOUNT -->

                <a
                    href="<?= BASE_URL ?>profile/index.php"
                    class="dropdown-item"
                >

                    <i class="fas fa-cog mr-2"></i>

                    <?= __('account_settings') ?>

                </a>


                <div class="dropdown-divider"></div>


                <!-- LOGOUT -->

                <a
                    href="<?= BASE_URL ?>logout.php"
                    class="dropdown-item text-danger"
                >

                    <i class="fas fa-sign-out-alt mr-2"></i>

                    <?= __('logout') ?>

                </a>

            </div>

        </li>

    </ul>

</nav>
