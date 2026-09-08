<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$pdo = $database->connect();

$stmt = $pdo->query("
    SELECT
        user_id,
        member_code,
        first_name,
        middle_name,
        last_name,
        gender
    FROM users
    WHERE is_deleted = 0
      AND account_status = 'active'
    ORDER BY first_name ASC, last_name ASC
");

$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/admin.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">
<section class="content">

<div class="container-fluid clan-page-content">

<div class="card clan-page-card">

<div class="card-header">
    <h4 class="mb-0">
        <i class="fas fa-sitemap mr-2 text-primary"></i>
        Add Family Relationship
    </h4>
</div>

<div class="card-body">

<form action="store.php" method="POST">

    <div class="form-group">

        <label>
            Member <span class="text-danger">*</span>
        </label>

        <select
            name="member_user_id"
            class="form-control"
            required
        >

            <option value="">
                -- Select Member --
            </option>

            <?php foreach ($members as $member): ?>

                <option value="<?= (int)$member['user_id'] ?>">

                    <?= htmlspecialchars(
                        trim(
                            $member['first_name'].' '.
                            ($member['middle_name'] ?? '').' '.
                            $member['last_name']
                        )
                    ) ?>

                    —
                    <?= htmlspecialchars($member['member_code']) ?>

                    (<?= htmlspecialchars($member['gender']) ?>)

                </option>

            <?php endforeach; ?>

        </select>

    </div>


    <div class="row">

        <div class="col-md-6">

            <div class="form-group">

                <label><?= __('father') ?></label>

                <select name="father_user_id" class="form-control">

                    <option value="">
                        -- No Father --
                    </option>

                    <?php foreach ($members as $member): ?>

                        <?php if ($member['gender'] !== 'Male') continue; ?>

                        <option value="<?= (int)$member['user_id'] ?>">

                            <?= htmlspecialchars(
                                trim(
                                    $member['first_name'].' '.
                                    ($member['middle_name'] ?? '').' '.
                                    $member['last_name']
                                )
                            ) ?>

                            —
                            <?= htmlspecialchars($member['member_code']) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

        </div>


        <div class="col-md-6">

            <div class="form-group">

                <label><?= __('mother') ?></label>

                <select name="mother_user_id" class="form-control">

                    <option value="">
                        -- No Mother --
                    </option>

                    <?php foreach ($members as $member): ?>

                        <?php if ($member['gender'] !== 'Female') continue; ?>

                        <option value="<?= (int)$member['user_id'] ?>">

                            <?= htmlspecialchars(
                                trim(
                                    $member['first_name'].' '.
                                    ($member['middle_name'] ?? '').' '.
                                    $member['last_name']
                                )
                            ) ?>

                            —
                            <?= htmlspecialchars($member['member_code']) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

        </div>

    </div>


    <div class="form-group">

        <label><?= __('spouse') ?></label>

        <select name="spouse_user_id" class="form-control">

            <option value="">
                -- No Spouse --
            </option>

            <?php foreach ($members as $member): ?>

                <option value="<?= (int)$member['user_id'] ?>">

                    <?= htmlspecialchars(
                        trim(
                            $member['first_name'].' '.
                            ($member['middle_name'] ?? '').' '.
                            $member['last_name']
                        )
                    ) ?>

                    —
                    <?= htmlspecialchars($member['member_code']) ?>

                </option>

            <?php endforeach; ?>

        </select>

    </div>


    <div class="d-flex justify-content-between">

        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i>
            Back
        </a>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i>
            Save Relationship
        </button>

    </div>

</form>

</div>
</div>

</div>

</section>
</div>

<?php require_once '../../includes/dashboard/footer.php'; ?>