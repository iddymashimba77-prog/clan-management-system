<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database = new Database();
$pdo = $database->connect();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit;
}


$stmt = $pdo->prepare("
    SELECT *
    FROM users
    WHERE user_id = ?
      AND is_deleted = 0
    LIMIT 1
");

$stmt->execute([$id]);

$member = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$member) {
    header('Location: index.php');
    exit;
}

if ((int)$member['role_id'] === ROLE_ADMINISTRATOR) {
    $_SESSION['error'] = __('cannot_edit_administrator');
    header('Location: index.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Available Parents / Spouses
|--------------------------------------------------------------------------
*/

$peopleStmt = $pdo->prepare("
    SELECT
        user_id,
        member_code,
        first_name,
        middle_name,
        last_name,
        gender
    FROM users
    WHERE is_deleted = 0
      AND user_id != ?
    ORDER BY first_name ASC, last_name ASC
");

$peopleStmt->execute([$id]);

$people = $peopleStmt->fetchAll(PDO::FETCH_ASSOC);


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


require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/chairperson.php';
require_once '../../includes/dashboard/navbar.php';

?>


<div class="content-wrapper">

    <section class="content">

        <div class="container-fluid clan-page-content">


            <!-- PAGE HEADER -->

            <div class="card clan-page-card mb-3">

                <div class="card-body">

                    <div class="d-flex align-items-center justify-content-between">

                        <div>

                            <h3 class="mb-1">

                                <i class="fas fa-user-edit mr-2 text-primary"></i>

                                <?= __('edit_family_member') ?>

                            </h3>

                            <p class="text-muted mb-0">

                                <?= __('update_member_information') ?>

                            </p>

                        </div>


                        <a
                            href="view.php?id=<?= (int)$member['user_id']; ?>"
                            class="btn btn-secondary"
                        >

                            <i class="fas fa-arrow-left mr-1"></i>

                            <?= __('back') ?>

                        </a>

                    </div>

                </div>

            </div>


            <!-- FORM -->

            <div class="card clan-page-card">

                <div class="card-body">

                    <form
                        action="update.php"
                        method="POST"
                        enctype="multipart/form-data"
                    >

                        <input
                            type="hidden"
                            name="user_id"
                            value="<?= (int)$member['user_id']; ?>"
                        >


                        <!-- BASIC INFORMATION -->

                        <h5 class="clan-form-section-title">

                            <i class="fas fa-user mr-2"></i>

                            <?= __('personal_information') ?>

                        </h5>


                        <div class="row">


                            <div class="col-md-4">

                                <div class="form-group">

                                    <label><?= __('first_name') ?></label>

                                    <input
                                        type="text"
                                        name="first_name"
                                        class="form-control"
                                        value="<?= htmlspecialchars($member['first_name']); ?>"
                                        required
                                    >

                                </div>

                            </div>


                            <div class="col-md-4">

                                <div class="form-group">

                                    <label><?= __('middle_name') ?></label>

                                    <input
                                        type="text"
                                        name="middle_name"
                                        class="form-control"
                                        value="<?= htmlspecialchars($member['middle_name'] ?? ''); ?>"
                                    >

                                </div>

                            </div>


                            <div class="col-md-4">

                                <div class="form-group">

                                    <label><?= __('last_name') ?></label>

                                    <input
                                        type="text"
                                        name="last_name"
                                        class="form-control"
                                        value="<?= htmlspecialchars($member['last_name']); ?>"
                                        required
                                    >

                                </div>

                            </div>

                        </div>


                        <div class="row">


                            <div class="col-md-4">

                                <div class="form-group">

                                    <label><?= __('gender') ?></label>

                                    <select
                                        name="gender"
                                        class="form-control"
                                        required
                                    >

                                        <option value="Male"
                                            <?= $member['gender'] === 'Male'
                                                ? 'selected'
                                                : ''; ?>
                                        >
                                            <?= __('male') ?>
                                        </option>

                                        <option value="Female"
                                            <?= $member['gender'] === 'Female'
                                                ? 'selected'
                                                : ''; ?>
                                        >
                                            <?= __('female') ?>
                                        </option>

                                    </select>

                                </div>

                            </div>


                            <div class="col-md-4">

                                <div class="form-group">

                                    <label><?= __('date_of_birth') ?></label>

                                    <input
                                        type="date"
                                        name="date_of_birth"
                                        class="form-control"
                                        value="<?= htmlspecialchars($member['date_of_birth'] ?? ''); ?>"
                                    >

                                </div>

                            </div>


                            <div class="col-md-4">

                                <div class="form-group">

                                    <label><?= __('marital_status') ?></label>

                                    <select
                                        name="marital_status"
                                        class="form-control"
                                    >

                                        <?php foreach (
                                            ['Single','Married','Divorced','Widowed']
                                            as $status
                                        ): ?>

                                            <option
                                                value="<?= $status; ?>"
                                                <?= $member['marital_status'] === $status
                                                    ? 'selected'
                                                    : ''; ?>
                                            >
                                                <?= __(strtolower($status)); ?>
                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>

                            </div>

                        </div>


                        <div class="row">


                            <div class="col-md-4">

                                <div class="form-group">

                                    <label><?= __('phone') ?></label>

                                    <input
                                        type="text"
                                        name="phone"
                                        class="form-control"
                                        value="<?= htmlspecialchars($member['phone'] ?? ''); ?>"
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
                                        value="<?= htmlspecialchars($member['email'] ?? ''); ?>"
                                    >

                                </div>

                            </div>


                            <div class="col-md-4">

                                <div class="form-group">

                                    <label><?= __('national_id') ?></label>

                                    <input
                                        type="text"
                                        name="national_id"
                                        class="form-control"
                                        value="<?= htmlspecialchars($member['national_id'] ?? ''); ?>"
                                    >

                                </div>

                            </div>

                        </div>


                        <div class="row">


                            <div class="col-md-4">

                                <div class="form-group">

                                    <label><?= __('birth_place') ?></label>

                                    <input
                                        type="text"
                                        name="birth_place"
                                        class="form-control"
                                        value="<?= htmlspecialchars($member['birth_place'] ?? ''); ?>"
                                    >

                                </div>

                            </div>


                            <div class="col-md-4">

                                <div class="form-group">

                                    <label><?= __('occupation') ?></label>

                                    <input
                                        type="text"
                                        name="occupation"
                                        class="form-control"
                                        value="<?= htmlspecialchars($member['occupation'] ?? ''); ?>"
                                    >

                                </div>

                            </div>


                            <div class="col-md-4">

                                <div class="form-group">

                                    <label><?= __('current_residence') ?></label>

                                    <input
                                        type="text"
                                        name="current_residence"
                                        class="form-control"
                                        value="<?= htmlspecialchars($member['current_residence'] ?? ''); ?>"
                                    >

                                </div>

                            </div>

                        </div>


                        <div class="row">


                            <div class="col-md-4">

                                <div class="form-group">

                                    <label><?= __('marital_status') ?></label>

                                    <select
                                        name="marital_status"
                                        class="form-control"
                                    >

                                        <?php foreach (
                                            ['Single','Married','Divorced','Widowed']
                                            as $status
                                        ): ?>

                                            <option
                                                value="<?= $status; ?>"
                                                <?= $member['marital_status'] === $status
                                                    ? 'selected'
                                                    : ''; ?>
                                            >
                                                <?= __(strtolower($status)); ?>
                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>

                            </div>


                            <div class="col-md-4">

                                <div class="form-group">

                                    <label><?= __('profile_picture_label') ?></label>

                                    <input
                                        type="file"
                                        name="profile_picture"
                                        class="form-control-file"
                                        accept="image/*"
                                    >

                                    <?php if (!empty($member['profile_picture'])): ?>

                                        <img
                                            src="../../uploads/profile_pictures/<?= htmlspecialchars($member['profile_picture']); ?>"
                                            style="width:80px;height:80px;object-fit:cover;border-radius:50%;margin-top:6px;"
                                        >

                                    <?php endif; ?>

                                </div>

                            </div>


                            <div class="col-md-4">

                                <div class="form-group">

                                    <label><?= __('cover_photo_label') ?></label>

                                    <input
                                        type="file"
                                        name="cover_photo"
                                        class="form-control-file"
                                        accept="image/*"
                                    >

                                    <?php if (!empty($member['cover_photo'])): ?>

                                        <img
                                            src="../../uploads/cover_photos/<?= htmlspecialchars($member['cover_photo']); ?>"
                                            style="width:160px;height:70px;object-fit:cover;margin-top:6px;"
                                        >

                                    <?php endif; ?>

                                </div>

                            </div>

                        </div>


                        <!-- FAMILY RELATIONSHIPS -->

                        <h5 class="clan-form-section-title mt-4">

                            <i class="fas fa-sitemap mr-2"></i>

                            <?= __('family_relationships') ?>

                        </h5>


                        <div class="row">


                            <div class="col-md-4">

                                <div class="form-group">

                                    <label><?= __('father') ?></label>

                                    <select
                                        name="father_user_id"
                                        class="form-control"
                                    >

                                        <option value="">
                                            <?= __('unknown') ?>
                                        </option>

                                        <?php foreach ($people as $person): ?>

                                            <?php if (($person['gender'] ?? '') !== 'Male') continue; ?>

                                            <option
                                                value="<?= (int)$person['user_id']; ?>"
                                                <?= (int)$member['father_user_id']
                                                    === (int)$person['user_id']
                                                    ? 'selected'
                                                    : ''; ?>
                                            >

                                                <?= htmlspecialchars(
                                                    $person['member_code']
                                                    . ' - '
                                                    . $person['first_name']
                                                    . ' '
                                                    . $person['last_name']
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

                                        <option value="">
                                            <?= __('unknown') ?>
                                        </option>

                                        <?php foreach ($people as $person): ?>

                                            <?php if (($person['gender'] ?? '') !== 'Female') continue; ?>

                                            <option
                                                value="<?= (int)$person['user_id']; ?>"
                                                <?= (int)$member['mother_user_id']
                                                    === (int)$person['user_id']
                                                    ? 'selected'
                                                    : ''; ?>
                                            >

                                                <?= htmlspecialchars(
                                                    $person['member_code']
                                                    . ' - '
                                                    . $person['first_name']
                                                    . ' '
                                                    . $person['last_name']
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

                                        <option value="">
                                            <?= __('no_spouse') ?>
                                        </option>

                                        <?php
                                        $spouseGender = ($member['gender'] ?? '') === 'Male'
                                            ? 'Female'
                                            : 'Male';
                                        ?>

                                        <?php foreach ($people as $person): ?>

                                            <?php if (($person['gender'] ?? '') !== $spouseGender) continue; ?>

                                            <option
                                                value="<?= (int)$person['user_id']; ?>"
                                                <?= (int)$member['spouse_user_id']
                                                    === (int)$person['user_id']
                                                    ? 'selected'
                                                    : ''; ?>
                                            >

                                                <?= htmlspecialchars(
                                                    $person['member_code']
                                                    . ' - '
                                                    . $person['first_name']
                                                    . ' '
                                                    . $person['last_name']
                                                ); ?>

                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>

                            </div>

                        </div>


                        <!-- BIOGRAPHY -->

                        <h5 class="clan-form-section-title mt-4">

                            <i class="fas fa-align-left mr-2"></i>

                            <?= __('biography') ?>

                        </h5>


                        <div class="form-group">

                            <textarea
                                name="biography"
                                class="form-control"
                                rows="5"
                            ><?= htmlspecialchars(
                                $member['biography'] ?? ''
                            ); ?></textarea>

                        </div>


                        <!-- ACCOUNT & ACCESS -->

                        <h5 class="clan-form-section-title mt-4">

                            <i class="fas fa-user-shield mr-2"></i>

                            <?= __('account_info') ?>

                        </h5>


                        <div class="row">


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
                                                <?= (int)$member['role_id']
                                                    === (int)$role['role_id']
                                                    ? 'selected'
                                                    : ''; ?>
                                            >
                                                <?= htmlspecialchars($role['role_name']); ?>
                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>

                            </div>


                            <div class="col-md-4">

                                <div class="form-group">

                                    <label><?= __('username') ?></label>

                                    <input
                                        type="text"
                                        name="username"
                                        class="form-control"
                                        value="<?= htmlspecialchars($member['username'] ?? ''); ?>"
                                        autocomplete="off"
                                    >

                                </div>

                            </div>


                            <div class="col-md-4">

                                <div class="form-group">

                                    <label><?= __('account_status') ?></label>

                                    <select
                                        name="account_status"
                                        class="form-control"
                                    >

                                        <option value="active"
                                            <?= $member['account_status'] === 'active'
                                                ? 'selected'
                                                : ''; ?>
                                        >
                                            <?= __('active') ?>
                                        </option>

                                        <option value="inactive"
                                            <?= $member['account_status'] === 'inactive'
                                                ? 'selected'
                                                : ''; ?>
                                        >
                                            <?= __('inactive') ?>
                                        </option>

                                    </select>

                                </div>

                            </div>

                        </div>


                        <div class="row">


                            <div class="col-md-4">

                                <div class="form-group">

                                    <label><?= __('family_member_type') ?></label>

                                    <select
                                        name="family_member_type"
                                        class="form-control"
                                    >

                                        <option value="blood"
                                            <?= ($member['family_member_type'] ?? 'blood')
                                                === 'blood'
                                                ? 'selected'
                                                : ''; ?>
                                        >
                                            <?= __('blood') ?>
                                        </option>

                                        <option value="marriage"
                                            <?= ($member['family_member_type'] ?? 'blood')
                                                === 'marriage'
                                                ? 'selected'
                                                : ''; ?>
                                        >
                                            <?= __('marriage') ?>
                                        </option>

                                    </select>

                                </div>

                            </div>


                            <div class="col-md-4">

                                <div class="form-group">

                                    <label><?= __('deceased_status') ?></label>

                                    <select
                                        name="is_deceased"
                                        class="form-control"
                                    >

                                        <option value="0"
                                            <?= empty($member['is_deceased'])
                                                ? 'selected'
                                                : ''; ?>
                                        >
                                            <?= __('alive') ?>
                                        </option>

                                        <option value="1"
                                            <?= !empty($member['is_deceased'])
                                                ? 'selected'
                                                : ''; ?>
                                        >
                                            <?= __('deceased') ?>
                                        </option>

                                    </select>

                                </div>

                            </div>


                            <div class="col-md-4">

                                <div class="form-group">

                                    <label><?= __('death_date') ?></label>

                                    <input
                                        type="date"
                                        name="death_date"
                                        class="form-control"
                                        value="<?= htmlspecialchars($member['death_date'] ?? ''); ?>"
                                    >

                                </div>

                            </div>

                        </div>


                        <div class="row">


                            <div class="col-md-4">

                                <div class="form-group">

                                    <label><?= __('death_place') ?></label>

                                    <input
                                        type="text"
                                        name="death_place"
                                        class="form-control"
                                        value="<?= htmlspecialchars($member['death_place'] ?? ''); ?>"
                                    >

                                </div>

                            </div>

                        </div>


                        <!-- ACTIONS -->

                        <div class="mt-4">

                            <button
                                type="submit"
                                class="btn btn-success"
                            >

                                <i class="fas fa-save mr-1"></i>

                                <?= __('update_member') ?>

                            </button>


                            <a
                                href="view.php?id=<?= (int)$member['user_id']; ?>"
                                class="btn btn-secondary"
                            >

                                <?= __('cancel') ?>

                            </a>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </section>

</div>


<?php require_once '../../includes/dashboard/footer.php'; ?>