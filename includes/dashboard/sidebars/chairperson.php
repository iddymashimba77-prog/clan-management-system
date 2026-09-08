<?php

require_once __DIR__ . '/../../../config/constants.php';
require_once __DIR__ . '/../../../config/language.php';

?>

<?php
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
?>

<aside class="main-sidebar sidebar-dark-primary elevation-4"> 


    <!-- =====================================================
         BRAND
    ====================================================== -->

    <a
        href="<?= BASE_URL ?>chairperson/dashboard.php"
        class="brand-link clan-brand"
    >

        <div class="clan-brand-icon">

            <i class="fas fa-users"></i>

        </div>


        <span class="brand-text">

            <strong>Clan</strong>

            <span>CMS</span>

        </span>

    </a>


    <!-- =====================================================
         SIDEBAR
    ====================================================== -->

    <div class="sidebar">


        <!-- =================================================
             USER PANEL
        ================================================== -->

        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="info">
                <a href="<?= BASE_URL ?>profile/index.php" class="d-block">
                    <?= htmlspecialchars($_SESSION['full_name'] ?? 'User') ?>
                </a>
                <small class="text-primary"><?= __('chairperson') ?></small>
            </div>
        </div>


        <!-- =================================================
             NAVIGATION
        ================================================== -->

        <nav class="mt-2">

            <ul
                class="nav nav-pills nav-sidebar flex-column"
                data-widget="treeview"
                role="menu"
            >


                <!-- DASHBOARD -->

                <li class="nav-item">

                    <a
                        href="<?= BASE_URL ?>chairperson/dashboard.php"
                        class="nav-link <?= strpos($currentPath, 'chairperson/dashboard.php') !== false ? 'active' : '' ?>"
                    >

                        <i class="nav-icon fas fa-home"></i>

                        <p>
                            <?= __('dashboard') ?>
                        </p>

                    </a>

                </li>


                <!-- MEMBERS -->
                <li class="nav-header">FAMILY</li>

                <li class="nav-item">

                    <a
                        href="<?= BASE_URL ?>chairperson/members/index.php"
                        class="nav-link <?= strpos($currentPath, 'chairperson/members') !== false ? 'active' : '' ?>"
                    >

                        <i class="nav-icon fas fa-users"></i>

                        <p>
                            <?= __('members') ?>
                        </p>

                    </a>

                </li>

                <!-- FAMILY TREE -->

                <li class="nav-item">

                    <a
                        href="<?= BASE_URL ?>family_tree/index.php"
                        class="nav-link <?= strpos($currentPath, 'family_tree') !== false ? 'active' : '' ?>"
                    >

                        <i class="nav-icon fas fa-sitemap"></i>

                        <p>
                            <?= __('family_tree') ?>
                        </p>

                    </a>

                </li>

                 <!-- LEADERSHIP -->

                 <li class="nav-item">

                <a
                    href="<?= BASE_URL ?>chairperson/leadership/index.php"
                    class="nav-link <?= strpos($currentPath, 'chairperson/leadership') !== false ? 'active' : '' ?>"
                >

                    <i class="nav-icon fas fa-user-tie"></i>

                    <p>
                        Leadership
                    </p>

                </a>

                </li>




                    <li class="nav-header">COMMUNITY</li>

                    <!-- MESSAGES -->

                    <li class="nav-item">

                        <a
                            href="<?= BASE_URL ?>messages/index.php"
                            class="nav-link <?= strpos($currentPath, 'messages') !== false ? 'active' : '' ?>"
                        >

                            <i class="nav-icon fas fa-comments"></i>

                            <p>

                                <?= __('messages') ?>

                                <span
                                    id="messageBadge"
                                    class="right badge badge-danger"
                                    style="display:none;"
                                >
                                    0
                                </span>

                            </p>

                        </a>

                    </li>



                    <!-- EVENTS -->

                    <li class="nav-item">

                        <a
                            href="<?= BASE_URL ?>chairperson/events/index.php"
                            class="nav-link <?= strpos($currentPath, 'chairperson/events') !== false ? 'active' : '' ?>"
                        >

                            <i class="nav-icon fas fa-calendar-alt"></i>

                            <p>
                                <?= __('events') ?>
                            </p>

                        </a>

                    </li>


                    <!-- MEETINGS -->

                    <li class="nav-item">

                        <a
                            href="<?= BASE_URL ?>meetings/index.php"
                            class="nav-link <?= strpos($currentPath, 'meetings') !== false ? 'active' : '' ?>"
                        >

                            <i class="nav-icon fas fa-video"></i>

                            <p>
                                <?= __('meetings') ?>
                            </p>

                        </a>

                    </li>


                    <!-- GALLERY -->

                    <li class="nav-item">

                        <a
                            href="<?= BASE_URL ?>gallery/index.php"
                            class="nav-link <?= strpos($currentPath, 'gallery') !== false ? 'active' : '' ?>"
                        >

                            <i class="nav-icon fas fa-images"></i>

                            <p>
                                <?= __('gallery') ?>
                            </p>

                        </a>

                    </li>


                    <li class="nav-header">FINANCE</li>

                        <!-- MY CONTRIBUTIONS -->


                    <li class="nav-item">

                    <a
                        href="<?= BASE_URL ?>chairperson/my_contributions/index.php"
                        class="nav-link <?= strpos($currentPath, 'chairperson/my_contributions') !== false ? 'active' : '' ?>"
                    >

                        <i class="nav-icon fas fa-hand-holding-usd"></i>

                        <p>
                            <?= __('my_contributions') ?>
                        </p>

                    </a>

                    </li>


                    <!-- CONTRIBUTION APPROVAL -->

                    <li class="nav-item">

                    <a
                        href="<?= BASE_URL ?>chairperson/contributions/index.php"
                        class="nav-link <?= strpos($currentPath, 'chairperson/contributions/index.php') !== false ? 'active' : '' ?>"
                    >

                        <i class="nav-icon fas fa-check-double"></i>

                        <p>
                            Final Contribution Approval
                        </p>

                    </a>

                    </li>


                    <!-- MANAGE CONTRIBUTIONS -->

                    <li class="nav-item">

                    <a
                        href="<?= BASE_URL ?>chairperson/event_contributions/index.php"
                        class="nav-link <?= strpos($currentPath, 'chairperson/event_contributions') !== false ? 'active' : '' ?>"
                    >

                        <i class="nav-icon fas fa-hand-holding-usd"></i>

                        <p>
                            <?= __('contributions') ?>
                        </p>

                    </a>

                    </li>


                    <!-- PAYMENT HISTORY -->

                    <li class="nav-item">

                    <a
                        href="<?= BASE_URL ?>chairperson/contributions/history.php"
                        class="nav-link <?= strpos($currentPath, 'chairperson/contributions/history') !== false ? 'active' : '' ?>"
                    >

                        <i class="nav-icon fas fa-history"></i>

                        <p>
                            <?= __('payment_history') ?>
                        </p>

                    </a>

                    </li>


                    <!-- FAMILY WALLETS -->

                    <li class="nav-item">

                    <a
                        href="<?= BASE_URL ?>chairperson/wallets/index.php"
                        class="nav-link <?= strpos($currentPath, 'chairperson/wallets') !== false ? 'active' : '' ?>"
                    >

                        <i class="nav-icon fas fa-wallet"></i>

                        <p>
                            <?= __('wallets') ?>
                        </p>

                    </a>

                    </li>


                    <!-- MY WALLETS -->

                    <li class="nav-item">

                    <a
                        href="<?= BASE_URL ?>chairperson/my_wallets/index.php"
                        class="nav-link <?= strpos($currentPath, 'chairperson/my_wallets') !== false ? 'active' : '' ?>"
                    >

                        <i class="nav-icon fas fa-wallet"></i>

                        <p>
                            <?= __('my_wallets') ?>
                        </p>

                    </a>

                    </li>

                    <li class="nav-item mt-2">
                    <a href="<?= BASE_URL ?>logout.php"
                       class="nav-link text-danger">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p><?= __('logout') ?></p>
                    </a>
                </li>

            </ul>

        </nav>

    </div>

</aside>
