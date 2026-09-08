<?php

require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/language.php';
require_once '../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$clanId = getCurrentClanId();

$user_id = (int) $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Current User + Role
|--------------------------------------------------------------------------
*/

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

$roleName = strtolower(
    trim($user['role_name'] ?? 'Member')
);

$isOrganizer = in_array(
    $roleName,
    ['administrator', 'chairperson'],
    true
);

/*
|--------------------------------------------------------------------------
| Meetings (upcoming & live first, then ended/cancelled)
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        m.meeting_id,
        m.title,
        m.description,
        m.meeting_date,
        m.start_time,
        m.end_time,
        m.room_code,
        m.meeting_status,
        m.created_at,

        TRIM(CONCAT(
            u.first_name, ' ',
            COALESCE(u.middle_name, ''), ' ',
            COALESCE(u.last_name, '')
        )) AS creator_name

    FROM meetings m

    LEFT JOIN users u
        ON m.created_by_user_id = u.user_id

    WHERE m.clan_id = ?

    ORDER BY
        FIELD(m.meeting_status, 'live', 'upcoming', 'ended', 'cancelled') ASC,
        m.meeting_date ASC,
        m.start_time ASC
");

$stmt->execute([$clanId]);

$meetings = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Dashboard Layout
|--------------------------------------------------------------------------
*/

define('USER_ROLE', $user['role_name'] ?? 'Member');

require_once '../includes/dashboard/header.php';

if ($roleName === 'administrator') {

    require_once '../includes/dashboard/sidebars/admin.php';

} elseif ($roleName === 'chairperson') {

    require_once '../includes/dashboard/sidebars/chairperson.php';

} elseif ($roleName === 'treasurer') {

    require_once '../includes/dashboard/sidebars/treasurer.php';

} elseif ($roleName === 'secretary') {

    require_once '../includes/dashboard/sidebars/secretary.php';

} else {

    require_once '../includes/dashboard/sidebars/member.php';
}

require_once '../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content meetings-page">

<div class="container-fluid">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h3 class="mb-1">

<i class="fas fa-video text-primary mr-2"></i>

<?= __('meetings') ?>

</h3>

<p class="text-muted mb-0">

<?= __('meetings_info') ?>

</p>

</div>

<?php if ($isOrganizer): ?>

<a
href="create.php"
class="btn btn-primary btn-sm"
>

<i class="fas fa-plus mr-1"></i>

<?= __('new_meeting') ?>

</a>

<?php endif; ?>

</div>


<?php if (isset($_GET['success'])): ?>

<div class="alert alert-success">

<?= __('saved_successfully') ?>

</div>

<?php endif; ?>


<?php if (isset($_GET['started'])): ?>

<div class="alert alert-success">

<?= __('meeting_started_message') ?>

</div>

<?php endif; ?>


<?php if (isset($_GET['ended'])): ?>

<div class="alert alert-success">

<?= __('meeting_ended_message') ?>

</div>

<?php endif; ?>


<?php if (isset($_GET['deleted'])): ?>

<div class="alert alert-danger">

<?= __('meeting_deleted_message') ?>

</div>

<?php endif; ?>


<div class="row">

<?php if (!$meetings): ?>

<div class="col-12">

<div class="card">

<div class="card-body text-center py-5">

<i
class="fas fa-video-slash fa-3x text-muted mb-3"
></i>

<h5><?= __('no_meetings_available') ?></h5>

<p class="text-muted">

<?= __('no_meetings_message') ?>

</p>

</div>

</div>

</div>

<?php endif; ?>


<?php foreach ($meetings as $meeting): ?>

<?php

$status =
    strtolower(
        $meeting['meeting_status'] ?? 'upcoming'
    );

$statusClass = [

    'upcoming' =>
        'badge-primary',

    'live' =>
        'badge-success',

    'ended' =>
        'badge-secondary',

    'cancelled' =>
        'badge-danger'

][$status] ?? 'badge-secondary';

$meetLink =
    'https://meet.jit.si/'
    . rawurlencode(
        $meeting['room_code']
    );

$isLive = ($status === 'live');

$isUpcoming = ($status === 'upcoming');

?>

<div class="col-xl-4 col-lg-6 col-md-6 mb-4">

<div class="card h-100 shadow-sm">

<div class="card-body">

<div
class="d-flex justify-content-between align-items-start mb-2"
>

<h5 class="font-weight-bold mb-0">

<?= htmlspecialchars(
    $meeting['title']
) ?>

</h5>

<span
class="badge <?= $statusClass ?>"
>

<?php if ($isLive): ?>

<i class="fas fa-circle text-white mr-1"></i>

<?php endif; ?>

<?= __('meeting_status_' . $status) ?>

</span>

</div>


<?php if (!empty($meeting['description'])): ?>

<p class="text-muted">

<?= nl2br(
    htmlspecialchars(
        $meeting['description']
    )
) ?>

</p>

<?php endif; ?>


<p class="mb-2">

<i
class="fas fa-calendar text-primary mr-2"
></i>

<strong><?= __('date_label') ?></strong>

<?= date(
    'd M Y',
    strtotime(
        $meeting['meeting_date']
    )
) ?>

</p>


<?php if (
    !empty($meeting['start_time'])
): ?>

<p class="mb-2">

<i
class="fas fa-clock text-primary mr-2"
></i>

<strong><?= __('time_label') ?></strong>

<?= date(
    'h:i A',
    strtotime(
        $meeting['start_time']
    )
) ?>


<?php if (
    !empty($meeting['end_time'])
): ?>

-

<?= date(
    'h:i A',
    strtotime(
        $meeting['end_time']
    )
) ?>

<?php endif; ?>

</p>

<?php endif; ?>


<p class="mb-2">

<i
class="fas fa-link text-primary mr-2"
></i>

<strong><?= __('room_code') ?>:</strong>

<span class="text-monospace">

<?= htmlspecialchars(
    $meeting['room_code']
) ?>

</span>

</p>

</div>


<div class="card-footer bg-white">

<div class="d-flex justify-content-between align-items-center">

<small class="text-muted">

<i class="fas fa-user mr-1"></i>

<?= __('created_by') ?>:

<strong>

<?= htmlspecialchars(
    trim(
        $meeting['creator_name']
        ?? __('family_administration')
    )
) ?>

</strong>

</small>

</div>

<div class="mt-2 d-flex flex-wrap">

<?php if ($isLive): ?>

<a
href="<?= htmlspecialchars($meetLink) ?>"
target="_blank"
rel="noopener"
class="btn btn-success btn-sm mr-1 mb-1"
>

<i class="fas fa-video mr-1"></i>

<?= __('join_meeting') ?>

</a>

<?php if ($isOrganizer): ?>

<form
method="POST"
action="end.php"
class="mb-1"
>

<input
type="hidden"
name="meeting_id"
value="<?= (int) $meeting['meeting_id'] ?>"
>

<button
type="submit"
class="btn btn-warning btn-sm"
>

<i class="fas fa-stop mr-1"></i>

<?= __('end_meeting') ?>

</button>

</form>

<?php endif; ?>


<?php elseif ($isUpcoming): ?>

<?php if ($isOrganizer): ?>

<form
method="POST"
action="start.php"
class="mr-1 mb-1"
>

<input
type="hidden"
name="meeting_id"
value="<?= (int) $meeting['meeting_id'] ?>"
>

<button
type="submit"
class="btn btn-success btn-sm"
>

<i class="fas fa-play mr-1"></i>

<?= __('start_meeting') ?>

</button>

</form>

<a
href="edit.php?id=<?= (int) $meeting['meeting_id'] ?>"
class="btn btn-info btn-sm mr-1 mb-1"
>

<i class="fas fa-edit mr-1"></i>

<?= __('edit') ?>

</a>

<form
method="POST"
action="delete.php"
class="mb-1"
onsubmit="return confirm('<?= __('delete_meeting_confirm') ?>');"
>

<input
type="hidden"
name="meeting_id"
value="<?= (int) $meeting['meeting_id'] ?>"
>

<button
type="submit"
class="btn btn-danger btn-sm"
>

<i class="fas fa-trash mr-1"></i>

<?= __('delete') ?>

</button>

</form>

<?php else: ?>

<span class="badge badge-light">

<i class="fas fa-hourglass-half mr-1"></i>

<?= __('meeting_not_started') ?>

</span>

<?php endif; ?>

<?php endif; ?>

</div>

</div>

</div>

</div>

<?php endforeach; ?>

</div>

</div>

</section>

</div>

<?php

require_once '../includes/dashboard/footer.php';

?>