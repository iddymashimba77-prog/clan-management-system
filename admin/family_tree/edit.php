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
        user_id,
        member_code,
        first_name,
        middle_name,
        last_name,
        gender,
        father_user_id,
        mother_user_id,
        spouse_user_id
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
        <i class="fas fa-edit mr-2 text-warning"></i>
        Edit Family Relationship
    </h4>

</div>

<div class="card-body">

<form action="update.php" method="POST">

<input
    type="hidden"
    name="member_user_id"
    value="<?= (int)$member['user_id'] ?>"
>


<div class="form-group">

<label><?= __('member') ?></label>

<input
    type="text"
    class="form-control"
    value="<?= htmlspecialchars(
        trim(
            $member['first_name'].' '.
            ($member['middle_name'] ?? '').' '.
            $member['last_name']
        ).' — '.$member['member_code']
    ) ?>"
    readonly
>

</div>


<div class="row">

<div class="col-md-6">

<div class="form-group">

<label><?= __('father') ?></label>

<select
    name="father_user_id"
    class="form-control"
>

<option value="">
    -- No Father --
</option>

<?php foreach ($members as $item): ?>

<?php
if (
    $item['gender'] !== 'Male' ||
    (int)$item['user_id'] === (int)$id
) {
    continue;
}
?>

<option
    value="<?= (int)$item['user_id'] ?>"
    <?= (int)$member['father_user_id'] === (int)$item['user_id']
        ? 'selected'
        : ''
    ?>
>

<?= htmlspecialchars(
    trim(
        $item['first_name'].' '.
        ($item['middle_name'] ?? '').' '.
        $item['last_name']
    )
) ?>

—
<?= htmlspecialchars($item['member_code']) ?>

</option>

<?php endforeach; ?>

</select>

</div>

</div>


<div class="col-md-6">

<div class="form-group">

<label><?= __('mother') ?></label>

<select
    name="mother_user_id"
    class="form-control"
>

<option value="">
    -- No Mother --
</option>

<?php foreach ($members as $item): ?>

<?php
if (
    $item['gender'] !== 'Female' ||
    (int)$item['user_id'] === (int)$id
) {
    continue;
}
?>

<option
    value="<?= (int)$item['user_id'] ?>"
    <?= (int)$member['mother_user_id'] === (int)$item['user_id']
        ? 'selected'
        : ''
    ?>
>

<?= htmlspecialchars(
    trim(
        $item['first_name'].' '.
        ($item['middle_name'] ?? '').' '.
        $item['last_name']
    )
) ?>

—
<?= htmlspecialchars($item['member_code']) ?>

</option>

<?php endforeach; ?>

</select>

</div>

</div>

</div>


<div class="form-group">

<label><?= __('spouse') ?></label>

<select
    name="spouse_user_id"
    class="form-control"
>

<option value="">
    -- No Spouse --
</option>

<?php foreach ($members as $item): ?>

<?php
if ((int)$item['user_id'] === (int)$id) {
    continue;
}

$spouseGender = ($member['gender'] ?? '') === 'Male'
    ? 'Female'
    : 'Male';

if (($item['gender'] ?? '') !== $spouseGender) {
    continue;
}
?>

<option
    value="<?= (int)$item['user_id'] ?>"
    <?= (int)$member['spouse_user_id'] === (int)$item['user_id']
        ? 'selected'
        : ''
    ?>
>

<?= htmlspecialchars(
    trim(
        $item['first_name'].' '.
        ($item['middle_name'] ?? '').' '.
        $item['last_name']
    )
) ?>

—
<?= htmlspecialchars($item['member_code']) ?>

</option>

<?php endforeach; ?>

</select>

</div>


<div class="d-flex justify-content-between">

<a
    href="view.php?id=<?= $id ?>"
    class="btn btn-secondary"
>
    <?= __('cancel') ?>
</a>

<button
    type="submit"
    class="btn btn-primary"
>
    <i class="fas fa-save mr-1"></i>
    Update Relationship
</button>

</div>

</form>

</div>
</div>

</div>

</section>
</div>

<?php require_once '../../includes/dashboard/footer.php'; ?>