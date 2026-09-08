<?php

require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$clanId = getCurrentClanId();

$stmt = $pdo->prepare("
SELECT
g.*,
CONCAT(u.first_name,' ',u.last_name) AS uploader
FROM gallery g
INNER JOIN users u
ON g.user_id=u.user_id
WHERE g.clan_id = ?
ORDER BY g.uploaded_at DESC
");

$stmt->execute([$clanId]);

$photos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$database = new Database();
$pdo = $database->connect();

$user_id = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT
        u.*,
        r.role_name
    FROM users u
    LEFT JOIN roles r
        ON u.role_id = r.role_id
    WHERE u.user_id = ?
      AND u.clan_id = ?
    LIMIT 1
");

$stmt->execute([$user_id, $clanId]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

define(
    'USER_ROLE',
    $user['role_name'] ?? 'Member'
);

require_once '../includes/dashboard/header.php';

$role = strtolower(
    trim($user['role_name'] ?? 'member')
);

if ($role === 'chairperson') {

    require_once
        '../includes/dashboard/sidebars/chairperson.php';

} elseif ($role === 'treasurer') {

    require_once
        '../includes/dashboard/sidebars/treasurer.php';

} elseif ($role === 'secretary') {

    require_once
        '../includes/dashboard/sidebars/secretary.php';

} else {

    require_once
        '../includes/dashboard/sidebars/member.php';
}

require_once '../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content gallery-page">

<div class="container-fluid">

<div class="card">

<div class="card-header">

<h3 class="card-title">

<?= __('clan_gallery') ?>

</h3>

<div class="card-tools">

<a href="upload.php" class="btn btn-primary">

<i class="fas fa-upload"></i>

<?= __('upload_photo') ?>

</a>

</div>

</div>

<div class="card-body">

<div class="row">

<?php foreach($photos as $photo): ?>

<div class="col-6 col-md-3">

<div class="card">

<img
src="<?= BASE_URL ?>gallery/uploads/<?= htmlspecialchars($photo['file_name']) ?>"
class="card-img-top gallery-tile"
style="height:220px;object-fit:cover;cursor:pointer;"
alt="<?= htmlspecialchars($photo['title']) ?>"
data-toggle="modal"
data-target="#photoModal"
data-full="<?= BASE_URL . 'gallery/uploads/' . htmlspecialchars($photo['file_name']) ?>"
data-title="<?= htmlspecialchars($photo['title']) ?>"
>

<div class="card-body">

<h6><?= htmlspecialchars($photo['title']) ?></h6>

<small>

<?= __('uploaded_by') ?>

<b><?= htmlspecialchars($photo['uploader']) ?></b>

</small>

<br><br>

<?php

$canDelete=false;

if($_SESSION['user_id']==$photo['user_id']){

    $canDelete=true;

}

if(hasRole('Chairperson') || hasRole('Administrator')){

    $canDelete=true;

}

?>

<?php if($canDelete): ?>

<a
href="delete.php?id=<?= $photo['gallery_id'] ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('<?= __('delete_photo_confirm') ?>');">

<?= __('delete') ?>

</a>

<?php endif; ?>

</div>

</div>

</div>

<?php endforeach; ?>

</div>
<!-- /.row -->

</div>
<!-- /.card-body -->

</div>
<!-- /.card -->

</section>

</div>

<!-- ======================================================
     PHOTO LIGHTBOX MODAL
====================================================== -->

<div
class="modal fade"
id="photoModal"
tabindex="-1"
role="dialog"
aria-hidden="true"
>
<div class="modal-dialog modal-lg modal-dialog-centered">
<div class="modal-content">

<div class="modal-header">

<h5 class="modal-title" id="photoModalTitle"></h5>

<button
type="button"
class="close"
data-dismiss="modal"
aria-label="Close"
>
<span aria-hidden="true">&times;</span>
</button>

</div>

<div class="modal-body p-0 text-center bg-dark">

<img
id="photoModalImage"
src=""
alt=""
class="img-fluid"
style="max-height:80vh;width:auto;margin:0 auto;object-fit:contain;">

</div>

</div>
</div>
</div>

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        var modalImage = document.getElementById('photoModalImage');
        var modalTitle = document.getElementById('photoModalTitle');

        document
            .querySelectorAll('.gallery-tile')
            .forEach(function (img) {

                img.addEventListener(
                    'click',
                    function () {

                        modalImage.src = this.dataset.full;
                        modalImage.alt = this.dataset.title || '';
                        modalTitle.textContent =
                            this.dataset.title || '';

                    }
                );

            });

    }
);

</script>

<?php
require_once '../includes/dashboard/footer.php';

?>