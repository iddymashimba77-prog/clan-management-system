<?php

require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';
require_once 'functions.php';

requireLogin();

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header('Location: ../login.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Get logged-in user's actual role
|--------------------------------------------------------------------------
*/

$pdo = familyTreePDO();
$clanId = getCurrentClanId();

$stmt = $pdo->prepare("
    SELECT 
        u.user_id,
        u.role_id,
        r.role_name
    FROM users u
    LEFT JOIN roles r 
        ON r.role_id = u.role_id
    WHERE u.user_id = ?
      AND u.is_deleted = 0
      AND u.clan_id = ?
    LIMIT 1
");

$stmt->execute([$user_id, $clanId]);

$loggedInUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$loggedInUser) {
    header('Location: ../login.php');
    exit;
}

$userRole = $loggedInUser['role_name'] ?? 'Member';

$roleFolder = strtolower(trim($userRole));

// Map administrator to admin folder
if ($roleFolder === 'administrator') {
    $roleFolder = 'admin';
}

/*
|--------------------------------------------------------------------------
| Member being viewed
|--------------------------------------------------------------------------
*/

$viewedMemberId = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($viewedMemberId <= 0) {
    header('Location: index.php');
    exit;
}

$member = getFamilyMember($viewedMemberId);

if (!$member) {
    header('Location: index.php');
    exit;
}

$father   = getFather($viewedMemberId);
$mother   = getMother($viewedMemberId);
$spouse   = getSpouse($viewedMemberId);
$children = getChildren($viewedMemberId);

$name = familyMemberName($member);
$photo = familyMemberPhoto($member);
$deceased = isFamilyMemberDeceased($member);

require_once '../includes/dashboard/header.php';
require_once '../includes/dashboard/sidebars/'
    . $roleFolder
    . '.php';
require_once '../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

    <section class="content">

        <div class="container-fluid clan-page-content">

            <!-- BACK -->
            <div class="mb-3">
                <a href="index.php" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i>
                    <?= __('family_tree') ?>
                </a>
            </div>


            <!-- PROFILE HEADER -->
            <div class="card clan-page-card family-member-profile-card">

                <div class="card-body">

                    <div class="row align-items-center">

                        <div class="col-md-3 text-center">

                            <div class="family-member-profile-photo">

                                <img
                                    src="<?= htmlspecialchars($photo) ?>"
                                    alt="<?= htmlspecialchars($name) ?>"
                                    onerror="this.onerror=null;this.src='../assets/images/default-avatar.png';"
                                >

                                <?php if ($deceased): ?>

                                    <span
                                        class="family-tree-deceased"
                                        title="<?= __('deceased') ?>"
                                    >
                                        ★
                                    </span>

                                <?php endif; ?>

                            </div>

                        </div>


                        <div class="col-md-9">

                            <div class="family-member-profile-relation">
                            <?= $member['family_member_type'] === 'marriage'
                                ? __('marriage_member')
                                : __('clan_member')
                            ?>
                            </div>

                            <h2 class="family-member-profile-name">
                                <?= htmlspecialchars($name) ?>
                            </h2>

                            <div class="family-member-profile-code">
                                <?= htmlspecialchars($member['member_code']) ?>
                            </div>

                            <div class="mt-3">

                                <?php if ($deceased): ?>

                                    <span class="badge badge-secondary mr-2">
                                        <i class="fas fa-star mr-1"></i>
                                        <?= __('deceased') ?>
                                    </span>

                                <?php else: ?>

                                    <span class="badge badge-success mr-2">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        <?= __('living') ?>
                                    </span>

                                <?php endif; ?>

                                <?php if (!empty($member['gender'])): ?>

                                    <span class="badge badge-light mr-2">
                                        <?= htmlspecialchars($member['gender']) ?>
                                    </span>

                                <?php endif; ?>

                                <?php if (!empty($member['marital_status'])): ?>

                                    <span class="badge badge-light">
                                        <?= htmlspecialchars($member['marital_status']) ?>
                                    </span>

                                <?php endif; ?>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- BASIC INFORMATION -->
            <div class="card clan-page-card">

                <div class="card-header">

                    <h5 class="mb-0">
                        <i class="fas fa-user mr-2 text-primary"></i>
                        <?= __('personal_information') ?>
                    </h5>

                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-4 mb-3">

                            <small class="text-muted d-block">
                                <?= __('member_code') ?>
                            </small>

                            <strong>
                                <?= htmlspecialchars($member['member_code']) ?>
                            </strong>

                        </div>


                        <div class="col-md-4 mb-3">

                            <small class="text-muted d-block">
                                <?= __('gender') ?>
                            </small>

                            <strong>
                                <?= htmlspecialchars($member['gender'] ?? __('not_specified')) ?>
                            </strong>

                        </div>


                        <div class="col-md-4 mb-3">

                            <small class="text-muted d-block">
                                <?= __('date_of_birth') ?>
                            </small>

                            <strong>
                                <?= !empty($member['date_of_birth'])
                                    ? htmlspecialchars($member['date_of_birth'])
                                    : __('not_specified')
                                ?>
                            </strong>

                        </div>


                        <div class="col-md-4 mb-3">

                            <small class="text-muted d-block">
                                <?= __('marital_status') ?>
                            </small>

                            <strong>
                                <?= htmlspecialchars($member['marital_status'] ?? __('single')) ?>
                            </strong>

                        </div>


                        <?php if ($deceased): ?>

                            <div class="col-md-4 mb-3">

                                <small class="text-muted d-block">
                                    <?= __('death_date') ?>
                                </small>

                                <strong>
                                    <?= !empty($member['death_date'])
                                        ? htmlspecialchars($member['death_date'])
                                        : __('not_specified')
                                    ?>
                                </strong>

                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </div>


            <!-- PARENTS -->
            <div class="card clan-page-card">

                <div class="card-header">

                    <h5 class="mb-0">
                        <i class="fas fa-users mr-2 text-primary"></i>
                        <?= __('parents') ?>
                    </h5>

                </div>

                <div class="card-body">

                    <div class="row">

                        <!-- FATHER -->
                        <div class="col-md-6 mb-3">

                            <div class="family-member-relation-card">

                                <div class="family-member-relation-icon">
                                    <i class="fas fa-male"></i>
                                </div>

                                <div>

                                    <small class="text-muted d-block">
                                        <?= __('father') ?>
                                    </small>

                                    <?php if ($father): ?>

                                        <a
                                            href="member.php?id=<?= (int)$father['user_id'] ?>"
                                            class="font-weight-bold"
                                        >
                                            <?= htmlspecialchars(familyMemberName($father)) ?>
                                        </a>

                                        <small class="d-block text-muted">
                                            <?= htmlspecialchars($father['member_code']) ?>
                                        </small>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            <?= __('not_recorded') ?>
                                        </span>

                                    <?php endif; ?>

                                </div>

                            </div>

                        </div>


                        <!-- MOTHER -->
                        <div class="col-md-6 mb-3">

                            <div class="family-member-relation-card">

                                <div class="family-member-relation-icon female">
                                    <i class="fas fa-female"></i>
                                </div>

                                <div>

                                    <small class="text-muted d-block">
                                        <?= __('mother') ?>
                                    </small>

                                    <?php if ($mother): ?>

                                        <a
                                            href="member.php?id=<?= (int)$mother['user_id'] ?>"
                                            class="font-weight-bold"
                                        >
                                            <?= htmlspecialchars(familyMemberName($mother)) ?>
                                        </a>

                                        <small class="d-block text-muted">
                                            <?= htmlspecialchars($mother['member_code']) ?>
                                        </small>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            <?= __('not_recorded') ?>
                                        </span>

                                    <?php endif; ?>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- SPOUSE -->
            <div class="card clan-page-card">

                <div class="card-header">

                    <h5 class="mb-0">
                        <i class="fas fa-heart mr-2 text-danger"></i>
                        <?= __('spouse') ?>
                    </h5>

                </div>

                <div class="card-body">

                    <?php if ($spouse): ?>

                        <div class="family-member-relation-card">

                            <div class="family-member-relation-icon spouse">
                                <i class="fas fa-heart"></i>
                            </div>

                            <div>

                                <small class="text-muted d-block">
                                    <?= __('spouse') ?>
                                </small>

                                <a
                                    href="member.php?id=<?= (int)$spouse['user_id'] ?>"
                                    class="font-weight-bold"
                                >
                                    <?= htmlspecialchars(familyMemberName($spouse)) ?>
                                </a>

                                <small class="d-block text-muted">
                                    <?= htmlspecialchars($spouse['member_code']) ?>
                                </small>

                            </div>

                        </div>

                    <?php else: ?>

                        <span class="text-muted">
                            <?= __('no_spouse_recorded') ?>
                        </span>

                    <?php endif; ?>

                </div>

            </div>


            <!-- CHILDREN -->
            <div class="card clan-page-card">

                <div class="card-header d-flex justify-content-between align-items-center">

                    <h5 class="mb-0">
                        <i class="fas fa-child mr-2 text-primary"></i>
                        <?= __('children') ?>
                    </h5>

                    <span class="badge badge-primary">
                        <?= count($children) ?>
                    </span>

                </div>


                <div class="card-body">

                    <?php if (!empty($children)): ?>

                        <div class="row">

                            <?php foreach ($children as $child): ?>

                                <div class="col-lg-4 col-md-6 mb-3">

                                    <a
                                        href="member.php?id=<?= (int)$child['user_id'] ?>"
                                        class="family-member-child-card"
                                    >

                                        <img
                                            src="<?= htmlspecialchars(familyMemberPhoto($child)) ?>"
                                            alt="<?= htmlspecialchars(familyMemberName($child)) ?>"
                                            onerror="this.onerror=null;this.src='../assets/images/default-avatar.png';"
                                        >

                                        <div>

                                            <strong>
                                                <?= htmlspecialchars(familyMemberName($child)) ?>
                                            </strong>

                                            <small>
                                                <?= htmlspecialchars($child['member_code']) ?>
                                            </small>

                                            <small>
                                                <?= htmlspecialchars($child['gender']) ?>
                                            </small>

                                        </div>

                                    </a>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php else: ?>

                        <div class="text-center py-4">

                            <i class="fas fa-child fa-2x text-muted mb-2"></i>

                            <p class="text-muted mb-0">
                                <?= __('no_children_recorded') ?>
                            </p>

                        </div>

                    <?php endif; ?>

                </div>

            </div>


            <!-- VIEW FULL TREE -->
            <div class="text-center mb-4">

                <a
                    href="index.php"
                    class="btn btn-primary"
                >
                    <i class="fas fa-sitemap mr-2"></i>
                    <?= __('view_complete_family_tree') ?>
                </a>

            </div>

        </div>

    </section>

</div>


<?php

require_once '../includes/dashboard/footer.php';

?>