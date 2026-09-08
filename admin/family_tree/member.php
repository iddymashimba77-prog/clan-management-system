<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';
require_once '../family_tree/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header('Location: ../../login.php');
    exit;
}

$pdo = familyTreePDO();

/*
|--------------------------------------------------------------------------
| Member being viewed
|--------------------------------------------------------------------------
*/

$viewedMemberId = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($viewedMemberId <= 0) {
    header('Location: tree.php');
    exit;
}

$member = getFamilyMember($viewedMemberId);

if (!$member) {
    header('Location: tree.php');
    exit;
}

$father   = getFather($viewedMemberId);
$mother   = getMother($viewedMemberId);
$spouse   = getSpouse($viewedMemberId);
$children = getChildren($viewedMemberId);

$name = familyMemberName($member);
$photo = familyMemberPhoto($member);
$deceased = isFamilyMemberDeceased($member);

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebar.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

    <section class="content">

        <div class="container-fluid clan-page-content">

            <!-- BACK -->
            <div class="mb-3">
                <a href="tree.php" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Family Tree
                </a>
            </div>


            <!-- MEMBER DETAILS -->
            <div class="card clan-page-card">

                <div class="card-header">

                    <h4 class="mb-0">
                        <i class="fas fa-user mr-2 text-primary"></i>
                        <?= htmlspecialchars($name) ?>
                    </h4>

                </div>


                <div class="card-body">

                    <div class="row">

                        <!-- PHOTO -->
                        <div class="col-md-4 text-center">

                            <div class="family-member-photo-large mb-3">

                                <img
                                    src="<?= htmlspecialchars($photo) ?>"
                                    alt="<?= htmlspecialchars($name) ?>"
                                    class="img-fluid rounded-circle"
                                    style="max-width: 200px;"
                                    onerror="this.onerror=null;this.src='../../assets/images/default-avatar.png';"
                                >

                            </div>

                            <?php if ($deceased): ?>

                                <div class="alert alert-danger">
                                    <i class="fas fa-star mr-1"></i>
                                    Deceased
                                </div>

                            <?php endif; ?>

                            <div class="text-muted">
                                <strong>Member Code:</strong><br>
                                <?= htmlspecialchars($member['member_code'] ?? '') ?>
                            </div>

                        </div>


                        <!-- DETAILS -->
                        <div class="col-md-8">

                            <h5 class="mb-3">Family Relationships</h5>


                            <?php if ($father): ?>

                                <div class="card mb-3">

                                    <div class="card-body">

                                        <h6 class="card-title text-primary">
                                            <i class="fas fa-male mr-1"></i>
                                            Father
                                        </h6>

                                        <p class="mb-0">
                                            <?= htmlspecialchars(familyMemberName($father)) ?>
                                        </p>

                                        <small class="text-muted">
                                            <?= htmlspecialchars($father['member_code'] ?? '') ?>
                                        </small>

                                    </div>

                                </div>

                            <?php else: ?>

                                <div class="alert alert-secondary mb-3">
                                    <i class="fas fa-male mr-1"></i>
                                    Father: Not recorded
                                </div>

                            <?php endif; ?>


                            <?php if ($mother): ?>

                                <div class="card mb-3">

                                    <div class="card-body">

                                        <h6 class="card-title text-danger">
                                            <i class="fas fa-female mr-1"></i>
                                            Mother
                                        </h6>

                                        <p class="mb-0">
                                            <?= htmlspecialchars(familyMemberName($mother)) ?>
                                        </p>

                                        <small class="text-muted">
                                            <?= htmlspecialchars($mother['member_code'] ?? '') ?>
                                        </small>

                                    </div>

                                </div>

                            <?php else: ?>

                                <div class="alert alert-secondary mb-3">
                                    <i class="fas fa-female mr-1"></i>
                                    Mother: Not recorded
                                </div>

                            <?php endif; ?>


                            <?php if ($spouse): ?>

                                <div class="card mb-3">

                                    <div class="card-body">

                                        <h6 class="card-title text-danger">
                                            <i class="fas fa-heart mr-1"></i>
                                            Spouse
                                        </h6>

                                        <p class="mb-0">
                                            <?= htmlspecialchars(familyMemberName($spouse)) ?>
                                        </p>

                                        <small class="text-muted">
                                            <?= htmlspecialchars($spouse['member_code'] ?? '') ?>
                                        </small>

                                    </div>

                                </div>

                            <?php else: ?>

                                <div class="alert alert-secondary mb-3">
                                    <i class="fas fa-heart mr-1"></i>
                                    Spouse: None
                                </div>

                            <?php endif; ?>


                            <?php if (!empty($children)): ?>

                                <div class="card">

                                    <div class="card-body">

                                        <h6 class="card-title text-success">
                                            <i class="fas fa-child mr-1"></i>
                                            Children (<?= count($children) ?>)
                                        </h6>

                                        <ul class="list-group list-group-flush">

                                            <?php foreach ($children as $child): ?>

                                                <li class="list-group-item">
                                                    <?= htmlspecialchars(familyMemberName($child)) ?>
                                                    <small class="text-muted d-block">
                                                        <?= htmlspecialchars($child['member_code'] ?? '') ?>
                                                    </small>
                                                </li>

                                            <?php endforeach; ?>

                                        </ul>

                                    </div>

                                </div>

                            <?php else: ?>

                                <div class="alert alert-secondary">
                                    <i class="fas fa-child mr-1"></i>
                                    Children: None recorded
                                </div>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>

</div>

<?php require_once '../../includes/dashboard/footer.php'; ?>
