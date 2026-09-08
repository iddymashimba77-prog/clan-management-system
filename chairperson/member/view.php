<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        u.*,

        father.first_name AS father_first_name,
        father.last_name AS father_last_name,

        mother.first_name AS mother_first_name,
        mother.last_name AS mother_last_name,

        spouse.first_name AS spouse_first_name,
        spouse.last_name AS spouse_last_name

    FROM users u

    LEFT JOIN users father
        ON father.user_id = u.father_user_id

    LEFT JOIN users mother
        ON mother.user_id = u.mother_user_id

    LEFT JOIN users spouse
        ON spouse.user_id = u.spouse_user_id

    WHERE u.user_id = ?
      AND u.is_deleted = 0

    LIMIT 1
");

$stmt->execute([$id]);

$member = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$member) {
    header('Location: index.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Profile Picture
|--------------------------------------------------------------------------
*/

if (!empty($member['profile_picture'])) {

    $image = '../../uploads/profile_pictures/'
        . basename($member['profile_picture']);

} else {

    $image = '../../assets/images/default-avatar.png';

}


/*
|--------------------------------------------------------------------------
| Full Name
|--------------------------------------------------------------------------
*

$fullName = trim(
    $member['first_name'] . ' ' .
    ($member['middle_name'] ?? '') . ' ' .
    $member['last_name']
);*/

?>

<?php require_once '../../includes/dashboard/header.php'; ?>

<?php require_once '../../includes/dashboard/sidebars/chairperson.php'; ?>

<?php require_once '../../includes/dashboard/navbar.php'; ?>


<div class="content-wrapper">

    <section class="content">

        <div class="container-fluid clan-page-content">


            <!-- ACTIONS -->

            <div class="mb-3">

                <a
                    href="edit.php?id=<?= (int)$member['user_id']; ?>"
                    class="btn btn-warning"
                >
                    <i class="fas fa-edit mr-1"></i>
                    Edit
                </a>


                <a
                    href="index.php"
                    class="btn btn-dark"
                >
                    <i class="fas fa-arrow-left mr-1"></i>
                    Back
                </a>


                <a
                    href="../../family_tree/index.php"
                    class="btn btn-primary"
                >
                    <i class="fas fa-sitemap mr-1"></i>
                    Family Tree
                </a>


                <button
                    onclick="window.print()"
                    class="btn btn-secondary"
                >
                    <i class="fas fa-print mr-1"></i>
                    Print
                </button>

            </div>


            <!-- MEMBER PROFILE -->

            <div class="row">


                <!-- PROFILE CARD -->

                <div class="col-md-4">

                    <div class="card clan-page-card">

                        <div class="card-body text-center">

                            <div class="clan-member-profile-image">

                                <img
                                    src="<?= htmlspecialchars($image); ?>"
                                    alt="Profile Picture"
                                    onerror="this.onerror=null;this.src='../../assets/images/default-avatar.png';"
                                >

                            </div>


                            <div class="family-tree-name">
                            <h3 class="mt-3 mb-1">
     
                            <?= htmlspecialchars($member['first_name'] . ' ' .
                                                ($member['middle_name'] ?? '') . ' ' .
                                                $member['last_name']);?>
                    </h3>
                </div>
  
                <div class="family-tree-code">
                <p class="text-muted mb-2">
                
                <?= htmlspecialchars($member['member_code']);?>
                </p>
                </div>


                <span class="badge badge-primary">

                                <?= htmlspecialchars(
                                    $member['gender']
                                ); ?>

                            </span>

                        </div>

                    </div>

                </div>


                <!-- INFORMATION -->

                <div class="col-md-8">

                    <div class="card clan-page-card">

                        <div class="card-header">

                            <h3 class="card-title">

                                <i class="fas fa-user mr-2 text-primary"></i>

                                Member Information

                            </h3>

                        </div>


                        <div class="card-body">

                            <div class="table-responsive">

                                <table class="table table-bordered clan-member-details">

                                    <tr>
                                        <th>First Name</th>
                                        <td>
                                            <?= htmlspecialchars(
                                                $member['first_name']
                                            ); ?>
                                        </td>
                                    </tr>


                                    <tr>
                                        <th>Middle Name</th>
                                        <td>
                                            <?= htmlspecialchars(
                                                $member['middle_name'] ?? ''
                                            ); ?>
                                        </td>
                                    </tr>


                                    <tr>
                                        <th>Last Name</th>
                                        <td>
                                            <?= htmlspecialchars(
                                                $member['last_name']
                                            ); ?>
                                        </td>
                                    </tr>


                                    <tr>
                                        <th>Date of Birth</th>
                                        <td>
                                            <?= htmlspecialchars(
                                                $member['date_of_birth'] ?? ''
                                            ); ?>
                                        </td>
                                    </tr>


                                    <tr>
                                        <th>Phone</th>
                                        <td>
                                            <?= htmlspecialchars(
                                                $member['phone'] ?? ''
                                            ); ?>
                                        </td>
                                    </tr>


                                    <tr>
                                        <th>Email</th>
                                        <td>
                                            <?= htmlspecialchars(
                                                $member['email'] ?? ''
                                            ); ?>
                                        </td>
                                    </tr>


                                    <tr>
                                        <th>Birth Place</th>
                                        <td>
                                            <?= htmlspecialchars(
                                                $member['birth_place'] ?? ''
                                            ); ?>
                                        </td>
                                    </tr>


                                    <tr>
                                        <th>Current Residence</th>
                                        <td>
                                            <?= htmlspecialchars(
                                                $member['current_residence'] ?? ''
                                            ); ?>
                                        </td>
                                    </tr>


                                    <tr>
                                        <th>Marital Status</th>
                                        <td>
                                            <?= htmlspecialchars(
                                                $member['marital_status'] ?? ''
                                            ); ?>
                                        </td>
                                    </tr>


                                    <tr>

                                        <th>Father</th>

                                        <td>

                                            <?php if (!empty($member['father_user_id'])): ?>

                                                <a href="view.php?id=<?= (int)$member['father_user_id']; ?>">

                                                    <?= htmlspecialchars(
                                                        $member['father_first_name']
                                                        . ' '
                                                        . $member['father_last_name']
                                                    ); ?>

                                                </a>

                                            <?php else: ?>

                                                <span class="text-muted">
                                                    Unknown
                                                </span>

                                            <?php endif; ?>

                                        </td>

                                    </tr>


                                    <tr>

                                        <th>Mother</th>

                                        <td>

                                            <?php if (!empty($member['mother_user_id'])): ?>

                                                <a href="view.php?id=<?= (int)$member['mother_user_id']; ?>">

                                                    <?= htmlspecialchars(
                                                        $member['mother_first_name']
                                                        . ' '
                                                        . $member['mother_last_name']
                                                    ); ?>

                                                </a>

                                            <?php else: ?>

                                                <span class="text-muted">
                                                    Unknown
                                                </span>

                                            <?php endif; ?>

                                        </td>

                                    </tr>


                                    <tr>

                                        <th>Spouse</th>

                                        <td>

                                            <?php if (!empty($member['spouse_user_id'])): ?>

                                                <a href="view.php?id=<?= (int)$member['spouse_user_id']; ?>">

                                                    <?= htmlspecialchars(
                                                        $member['spouse_first_name']
                                                        . ' '
                                                        . $member['spouse_last_name']
                                                    ); ?>

                                                </a>

                                            <?php else: ?>

                                                <span class="text-muted">
                                                    Not Married
                                                </span>

                                            <?php endif; ?>

                                        </td>

                                    </tr>


                                    <tr>

                                        <th>Biography</th>

                                        <td>

                                            <?= nl2br(
                                                htmlspecialchars(
                                                    $member['biography'] ?? ''
                                                )
                                            ); ?>

                                        </td>

                                    </tr>

                                </table>

                            </div>


                            <div class="mt-3">

                                <a
                                    href="edit.php?id=<?= (int)$member['user_id']; ?>"
                                    class="btn btn-primary"
                                >

                                    <i class="fas fa-edit mr-1"></i>

                                    Edit Member

                                </a>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>

</div>


<?php require_once '../../includes/dashboard/footer.php'; ?>