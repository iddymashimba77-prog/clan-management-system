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
        href="<?= BASE_URL ?>member/dashboard.php"
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

    <div class="sidebar">

        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="info">
                <a href="<?= BASE_URL ?>profile/index.php" class="d-block">
                    <?= htmlspecialchars($_SESSION['full_name'] ?? 'User') ?>
                </a>
                <small class="text-success"><?= __('member') ?></small>
            </div>
        </div>

        <nav class="mt-2">

            <ul class="nav nav-pills nav-sidebar flex-column">

                <li class="nav-item">
                    <a href="<?= BASE_URL ?>member/dashboard.php" class="nav-link <?= strpos($currentPath, 'member/dashboard.php') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-home"></i>
                        <p><?= __('dashboard') ?></p>
                    </a>
                </li>

                <li class="nav-header"><?= __('family') ?></li>

                <li class="nav-item">
                    <a href="<?= BASE_URL ?>family_tree/index.php" class="nav-link <?= strpos($currentPath, 'family_tree') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-sitemap"></i>
                        <p><?= __('family_tree') ?></p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= BASE_URL ?>member/members/index.php" class="nav-link <?= strpos($currentPath, 'member/members') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p><?= __('family_members') ?></p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= BASE_URL ?>gallery/index.php" class="nav-link <?= strpos($currentPath, 'gallery') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-images"></i>
                        <p><?= __('gallery') ?></p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= BASE_URL ?>profile/index.php" class="nav-link <?= strpos($currentPath, 'profile') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-user-circle"></i>
                        <p><?= __('profile') ?></p>
                    </a>
                </li>
                





                <li class="nav-header">COMMUNITY</li>

                <li class="nav-item">
                    <a href="<?= BASE_URL ?>messages/index.php" class="nav-link <?= strpos($currentPath, 'messages') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-comments"></i>
                        <p>
                            <?= __('messages') ?>
                            <span id="messageBadge"
                                  class="right badge badge-danger"
                                  style="display:none;">
                                0
                            </span>
                        </p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= BASE_URL ?>events/index.php" class="nav-link <?= strpos($currentPath, 'events') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-calendar-alt"></i>
                        <p><?= __('events') ?></p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= BASE_URL ?>meetings/index.php" class="nav-link <?= strpos($currentPath, 'meetings') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-video"></i>
                        <p><?= __('meetings') ?></p>
                    </a>
                </li>


                <li class="nav-header">FINANCE</li>

                <li class="nav-item">
                    <a href="<?= BASE_URL ?>member/contributions/index.php" class="nav-link <?= strpos($currentPath, 'member/contributions/index.php') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-hand-holding-usd"></i>
                        <p><?= __('my_contributions') ?></p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= BASE_URL ?>member/wallets/index.php" class="nav-link <?= strpos($currentPath, 'member/wallets') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-wallet"></i>
                        <p><?= __('my_wallets') ?></p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= BASE_URL ?>member/receipts/index.php" class="nav-link <?= strpos($currentPath, 'member/receipts') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-receipt"></i>
                        <p><?= __('receipts') ?></p>
                    </a>
                </li>

                            <li class="nav-item">
                <a href="<?= BASE_URL ?>member/contributions/history.php"
                class="nav-link <?= strpos($currentPath, 'member/contributions/history') !== false ? 'active' : '' ?>">

                    <i class="nav-icon fas fa-history"></i>

                    <p><?= __('payment_history') ?></p>

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