<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$pdo = $database->connect();

$stmt = $pdo->prepare("
    SELECT
        u.*,

        CONCAT(
            f.first_name, ' ',
            COALESCE(f.middle_name, ''), ' ',
            f.last_name
        ) AS father_name,

        f.member_code AS father_code,

        CONCAT(
            m.first_name, ' ',
            COALESCE(m.middle_name, ''), ' ',
            m.last_name
        ) AS mother_name,

        m.member_code AS mother_code,

        CONCAT(
            s.first_name, ' ',
            COALESCE(s.middle_name, ''), ' ',
            s.last_name
        ) AS spouse_name,

        s.member_code AS spouse_code

    FROM users u

    LEFT JOIN users f
        ON f.user_id = u.father_user_id

    LEFT JOIN users m
        ON m.user_id = u.mother_user_id

    LEFT JOIN users s
        ON s.user_id = u.spouse_user_id

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


$stmt = $pdo->prepare("
    SELECT
        user_id,
        member_code,
        first_name,
        middle_name,
        last_name,
        gender
    FROM users
    WHERE is_deleted = 0
      AND (
            father_user_id = ?
            OR mother_user_id = ?
          )
    ORDER BY first_name ASC
");

$stmt->execute([$id, $id]);

$children = $stmt->fetchAll(PDO::FETCH_ASSOC);


$name = trim(
    $member['first_name'].' '.
    ($member['middle_name'] ?? '').' '.
    $member['last_name']
);

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/admin.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">
<section class="content">

<div class="container-fluid clan-page-content">

<div class="card clan-page-card">

<div class="card-header d-flex justify-content-between">

    <h4 class="mb-0">
        <i class="fas fa-sitemap mr-2 text-primary"></i>
        Family Relationship
    </h4>

    <div>

        <a
            href="edit.php?id=<?= $id ?>"
            class="btn btn-warning btn-sm"
        >
            <i class="fas fa-edit mr-1"></i>
            Edit
        </a>

        <a
            href="index.php"
            class="btn btn-secondary btn-sm"
        >
            <?= __('back') ?>
        </a>

    </div>

</div>

<div class="card-body">

<h4>
    <?= htmlspecialchars($name) ?>
</h4>

<p class="text-muted">
    <?= htmlspecialchars($member['member_code']) ?>
</p>

<hr>


<div class="row">

    <div class="col-md-4 mb-4">

        <h6 class="text-primary">
            Father
        </h6>

        <?php if ($member['father_user_id']): ?>

            <a href="view.php?id=<?= (int)$member['father_user_id'] ?>">
                <?= htmlspecialchars($member['father_name']) ?>
            </a>

            <small class="d-block text-muted">
                <?= htmlspecialchars($member['father_code']) ?>
            </small>

        <?php else: ?>

            <span class="text-muted">
                Not recorded
            </span>

        <?php endif; ?>

    </div>


    <div class="col-md-4 mb-4">

        <h6 class="text-danger">
            Mother
        </h6>

        <?php if ($member['mother_user_id']): ?>

            <a href="view.php?id=<?= (int)$member['mother_user_id'] ?>">
                <?= htmlspecialchars($member['mother_name']) ?>
            </a>

            <small class="d-block text-muted">
                <?= htmlspecialchars($member['mother_code']) ?>
            </small>

        <?php else: ?>

            <span class="text-muted">
                Not recorded
            </span>

        <?php endif; ?>

    </div>


    <div class="col-md-4 mb-4">

        <h6 class="text-danger">
            <?= __('spouse') ?>
        </h6>

        <?php if ($member['spouse_user_id']): ?>

            <a href="view.php?id=<?= (int)$member['spouse_user_id'] ?>">
                <?= htmlspecialchars($member['spouse_name']) ?>
            </a>

            <small class="d-block text-muted">
                <?= htmlspecialchars($member['spouse_code']) ?>
            </small>

        <?php else: ?>

            <span class="text-muted">
                Not recorded
            </span>

        <?php endif; ?>

    </div>

</div>


<hr>


<h5>
    <i class="fas fa-child mr-2 text-primary"></i>
    <?= __('children') ?>
    <span class="badge badge-primary">
        <?= count($children) ?>
    </span>
</h5>

<?php if (!empty($children)): ?>

<div class="row mt-3">

<?php foreach ($children as $child): ?>

<div class="col-md-4 mb-3">

<div class="border rounded p-3">

<strong>
    <?= htmlspecialchars(
        trim(
            $child['first_name'].' '.
            ($child['middle_name'] ?? '').' '.
            $child['last_name']
        )
    ) ?>
</strong>

<small class="d-block text-muted">
    <?= htmlspecialchars($child['member_code']) ?>
</small>

<a
    href="view.php?id=<?= (int)$child['user_id'] ?>"
    class="btn btn-outline-primary btn-sm mt-2"
>
    View
</a>

</div>

</div>

<?php endforeach; ?>

</div>

<?php else: ?>

<p class="text-muted mt-3">
    No children recorded.
</p>

<?php endif; ?>

</div>
</div>

</div>

</section>
</div>

<?php require_once '../../includes/dashboard/footer.php'; ?>