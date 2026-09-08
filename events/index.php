<?php

require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$clanId = getCurrentClanId();

$user_id = (int) $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Current User
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

define(
    'USER_ROLE',
    $user['role_name'] ?? 'Member'
);


/*
|--------------------------------------------------------------------------
| Get Events
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        e.event_id,
        e.title,
        e.description,
        e.event_date,
        e.start_time,
        e.end_time,
        e.location,
        e.event_image,
        e.event_status,
        e.created_at,

        CONCAT(
            u.first_name,
            ' ',
            COALESCE(
                CONCAT(u.middle_name, ' '),
                ''
            ),
            u.last_name
        ) AS creator_name

    FROM events e

    LEFT JOIN users u
        ON e.created_by_user_id = u.user_id

    WHERE
        e.clan_id = ?

    ORDER BY
        e.event_date ASC,
        e.start_time ASC
");

$stmt->execute([$clanId]);

$events = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Dashboard Layout
|--------------------------------------------------------------------------
*/

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

<section class="content events-page">

<div class="container-fluid">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h3 class="mb-1">

<i class="fas fa-calendar-alt text-primary mr-2"></i>

<?= __('family_events') ?>

</h3>

<p class="text-muted mb-0">

<?= __('events_and_activities') ?>

</p>

</div>

</div>


<div class="row">

<?php if (!$events): ?>

<div class="col-12">

<div class="card">

<div class="card-body text-center py-5">

<i
class="fas fa-calendar-times fa-3x text-muted mb-3"
></i>

<h5><?= __('no_events_available') ?></h5>

<p class="text-muted">

<?= __('no_events_message') ?>

</p>

</div>

</div>

</div>

<?php endif; ?>


<?php foreach ($events as $event): ?>

<?php

$status =
    strtolower(
        $event['event_status'] ?? 'upcoming'
    );


$statusClass = [

    'upcoming' =>
        'badge-primary',

    'ongoing' =>
        'badge-success',

    'completed' =>
        'badge-secondary',

    'cancelled' =>
        'badge-danger'

][$status] ?? 'badge-secondary';


$image = '';

if (!empty($event['event_image'])) {

    $image =
        BASE_URL
        . 'uploads/events/'
        . $event['event_image'];

}

?>

<div class="col-xl-4 col-lg-6 col-md-6 mb-4">

<div class="card h-100 shadow-sm">


<?php if ($image): ?>

<img
src="<?= htmlspecialchars($image) ?>"
class="card-img-top"
style="
height:200px;
object-fit:cover;
"
alt="<?= __('event_image') ?>"
>

<?php else: ?>

<div
class="d-flex align-items-center justify-content-center bg-light"
style="height:200px;"
>

<i
class="fas fa-calendar-day fa-4x text-muted"
></i>

</div>

<?php endif; ?>


<div class="card-body">

<div
class="d-flex justify-content-between align-items-start mb-2"
>

<h5 class="font-weight-bold mb-0">

<?= htmlspecialchars(
    $event['title']
) ?>

</h5>

<span
class="badge <?= $statusClass ?>"
>

<?= ucfirst(__($status)) ?>

</span>

</div>


<?php if (!empty($event['description'])): ?>

<p class="text-muted">

<?= nl2br(
    htmlspecialchars(
        $event['description']
    )
) ?>

</p>

<?php endif; ?>


<hr>


<p class="mb-2">

<i
class="fas fa-calendar text-primary mr-2"
></i>

<strong><?= __('date_label') ?></strong>

<?= date(
    'd M Y',
    strtotime(
        $event['event_date']
    )
) ?>

</p>


<?php if (
    !empty($event['start_time'])
): ?>

<p class="mb-2">

<i
class="fas fa-clock text-primary mr-2"
></i>

<strong><?= __('time_label') ?></strong>

<?= date(
    'h:i A',
    strtotime(
        $event['start_time']
    )
) ?>


<?php if (
    !empty($event['end_time'])
): ?>

-

<?= date(
    'h:i A',
    strtotime(
        $event['end_time']
    )
) ?>

<?php endif; ?>

</p>

<?php endif; ?>


<?php if (
    !empty($event['location'])
): ?>

<p class="mb-2">

<i
class="fas fa-map-marker-alt text-danger mr-2"
></i>

<strong><?= __('location_label') ?></strong>

<?= htmlspecialchars(
    $event['location']
) ?>

</p>

<?php endif; ?>

</div>


<div class="card-footer bg-white">

<small class="text-muted">

<i class="fas fa-user mr-1"></i>

<?= __('created_by') ?>:

<strong>

<?= htmlspecialchars(
    trim(
        $event['creator_name']
        ?? __('family_administration')
    )
) ?>

</strong>

</small>

</div>

</div>

</div>

<?php endforeach; ?>

</div>

</div>

</div>

</div>

</section>

</div>

<?php

require_once '../includes/dashboard/footer.php';

?>