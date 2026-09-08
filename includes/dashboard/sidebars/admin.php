<aside class="main-sidebar sidebar-dark-primary elevation-4">

    <a
        href="<?= BASE_URL ?>admin/dashboard.php"
        class="brand-link"
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
                <small class="text-primary"><?= __('administrator') ?></small>
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
                        href="<?= BASE_URL ?>admin/dashboard.php"
                        class="nav-link"
                    >

                        <i class="nav-icon fas fa-tachometer-alt"></i>

                        <p>
                            <?= __('dashboard') ?>
                        </p>

                    </a>

                </li>


                <!-- MEMBERS -->

                <li class="nav-item">

                    <a href="#" class="nav-link">

                        <i class="nav-icon fas fa-users"></i>

                        <p>

                            <?= __('members') ?>

                            <i class="right fas fa-angle-left"></i>

                        </p>

                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">

                            <a
                                href="<?= BASE_URL ?>admin/members/index.php"
                                class="nav-link"
                            >

                                <i class="far fa-circle nav-icon"></i>

                                <p>
                                    <?= __('all_members') ?>
                                </p>

                            </a>

                        </li>

                        <li class="nav-item">

                            <a
                                href="<?= BASE_URL ?>admin/members/create.php"
                                class="nav-link"
                            >

                                <i class="far fa-circle nav-icon"></i>

                                <p>
                                    <?= __('add_member') ?>
                                </p>

                            </a>

                        </li>

                    </ul>

                </li>


                <!-- FAMILY TREE -->

                <li class="nav-item">

                    <a
                        href="<?= BASE_URL ?>admin/family_tree/index.php"
                        class="nav-link"
                    >

                        <i class="nav-icon fas fa-sitemap"></i>

                        <p>
                            <?= __('family_tree') ?>
                        </p>

                    </a>

                </li>


                <li class="nav-item">

                <a href="#" class="nav-link">

                    <i class="nav-icon fas fa-user-shield"></i>

                    <p>
                        <?= __('roles_permissions') ?>
                        <i class="right fas fa-angle-left"></i>
                    </p>

                </a>


                <ul class="nav nav-treeview">


                    <!-- ROLES -->

                    <li class="nav-item">

                        <a
                            href="<?= BASE_URL ?>/admin/roles/index.php"
                            class="nav-link"
                        >

                            <i class="fas fa-users-cog nav-icon"></i>

                            <p>Roles</p>

                        </a>

                    </li>


                    <!-- PERMISSIONS -->

                    <li class="nav-item">

                        <a
                            href="<?= BASE_URL ?>/admin/permissions/index.php"
                            class="nav-link"
                        >

                            <i class="fas fa-key nav-icon"></i>

                            <p>Permissions</p>

                        </a>

                    </li>


                </ul>

                </li>


                <!-- CONTRIBUTIONS -->

                <li class="nav-item">

                    <a href="#" class="nav-link">

                        <i class="nav-icon fas fa-hand-holding-usd"></i>

                        <p>

                            <?= __('contributions') ?>

                            <i class="right fas fa-angle-left"></i>

                        </p>

                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">
                            <a
                                href="<?= BASE_URL ?>admin/contributions/index.php"
                                class="nav-link"
                            >
                                <i class="far fa-circle nav-icon"></i>
                                <p>All <?= __('contributions') ?></p>
                            </a>
                        </li>

                        <li class="nav-item">

                            <a
                                href="<?= BASE_URL ?>/admin/contribution_types/index.php"
                                class="nav-link"
                            >

                                <i class="nav-icon fas fa-layer-group"></i>

                                <p>
                                    <?= __('contribution_types') ?>
                                </p>

                            </a>

                        </li>

                        <li class="nav-item">
                            <a
                                href="<?= BASE_URL ?>admin/contributions/payments.php"
                                class="nav-link"
                            >
                                <i class="far fa-circle nav-icon"></i>
                                <p><?= __('payments') ?></p>
                            </a>
                        </li>

                    </ul>

                </li>


                <!-- WALLETS -->

                <li class="nav-item">

                    <a href="#" class="nav-link">

                        <i class="nav-icon fas fa-wallet"></i>

                        <p>

                            <?= __('wallets') ?>

                            <i class="right fas fa-angle-left"></i>

                        </p>

                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">
                            <a
                                href="<?= BASE_URL ?>admin/wallets/index.php"
                                class="nav-link"
                            >
                                <i class="far fa-circle nav-icon"></i>
                                <p><?= __('wallets') ?></p>
                            </a>
                        </li>

                        <li class="nav-item">

                        <a
                            href="<?= BASE_URL ?>/admin/transactions/index.php"
                            class="nav-link"
                        >

                            <i class="nav-icon fas fa-exchange-alt"></i>

                            <p>
                                <?= __('transactions') ?>
                            </p>

                        </a>

                    </li>
                    </ul>

                </li>


                <!-- FINANCE -->

                <li class="nav-item">

                    <a href="#" class="nav-link">

                        <i class="nav-icon fas fa-coins"></i>

                        <p>

                            <?= __('finance') ?>

                            <i class="right fas fa-angle-left"></i>

                        </p>

                    </a>

                    <ul class="nav nav-treeview">

                    <li class="nav-item">
                    
                        <a href="<?= BASE_URL ?>admin/finance/income/index.php"
                        class="nav-link">

                            <i class="nav-icon fas fa-arrow-circle-down text-success"></i>

                            <p>
                                <?= __('income') ?>
                            </p>

                        </a>

                        </li>


                        <li class="nav-item">
                        <a href="<?= BASE_URL ?>admin/finance/expenses/index.php"
                        
                        class="nav-link">

                            <i class="nav-icon fas fa-arrow-circle-up text-danger"></i>

                            <p>
                                <?= __('expenses') ?>
                            </p>

                        </a>

                        </li>

                        <li class="nav-item">
                            <a
                                href="<?= BASE_URL ?>admin/finance/transactions.php"
                                class="nav-link"
                            >
                            <i class="nav-icon fas fa-exchange-alt"></i>
                                <p><?= __('transactions') ?></p>
                            </a>
                        </li>

                    </ul>

                </li>


                <!-- REPORTS -->

                <li class="nav-item">

                    <a
                        href="<?= BASE_URL ?>admin/reports/index.php"
                        class="nav-link"
                    >

                        <i class="nav-icon fas fa-chart-bar"></i>

                        <p>
                            <?= __('reports_analytics') ?>
                        </p>

                    </a>

                </li>


                <!-- NOTIFICATIONS -->

                <li class="nav-item">

                    <a
                        href="<?= BASE_URL ?>admin/notifications/index.php"
                        class="nav-link"
                    >

                        <i class="nav-icon fas fa-bell"></i>

                        <p>
                            <?= __('notifications') ?>
                        </p>

                    </a>

                </li>


                <!-- MEETINGS -->

                <li class="nav-item">

                    <a
                        href="<?= BASE_URL ?>meetings/index.php"
                        class="nav-link"
                    >

                        <i class="nav-icon fas fa-video"></i>

                        <p>
                            <?= __('meetings') ?>
                        </p>

                    </a>

                </li>


                <!-- ACTIVITY LOGS -->

                <li class="nav-item">

                    <a
                        href="<?= BASE_URL ?>admin/activity_logs/index.php"
                        class="nav-link"
                    >

                        <i class="nav-icon fas fa-history"></i>

                        <p>
                            <?= __('activity_logs') ?>
                        </p>

                    </a>

                </li>


                <!-- CLAN SETTINGS -->

                <li class="nav-item">

                    <a
                        href="<?= BASE_URL ?>admin/clan_settings/index.php"
                        class="nav-link"
                    >

                        <i class="nav-icon fas fa-cog"></i>

                        <p>
                            <?= __('clan_settings') ?>
                        </p>

                    </a>

                </li>


                <!-- SYSTEM -->

                <li class="nav-item">

                    <a href="#" class="nav-link">

                        <i class="nav-icon fas fa-server"></i>

                        <p>

                            <?= __('system') ?>

                            <i class="right fas fa-angle-left"></i>

                        </p>

                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">
                                    
                        <a href="<?= BASE_URL ?>admin/system/backup.php"
                            class="nav-link">

                                <i class="fas fa-database nav-icon"></i>

                                <p><?= __('backup') ?></p>

                            </a>

                        </li>

                        <li class="nav-item">

                        <a href="<?= BASE_URL ?>admin/system/restore.php"
                            class="nav-link">

                                <i class="fas fa-upload nav-icon"></i>

                                <p><?= __('restore') ?></p>

                            </a>

                        </li>

                    </ul>

                </li>


                <!-- LOGOUT -->

                <li class="nav-item mt-3">

                    <a
                        href="<?= BASE_URL ?>admin/logout.php"
                        class="nav-link text-danger"
                    >

                        <i class="nav-icon fas fa-sign-out-alt"></i>

                        <p>
                            <?= __('logout') ?>
                        </p>

                    </a>

                </li>

            </ul>

        </nav>

    </div>

</aside>