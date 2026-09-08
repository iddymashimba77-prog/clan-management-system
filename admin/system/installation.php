<?php

declare(strict_types=1);

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';
require_once '../../config/installation.php';

requireLogin();

requireAdministrator();


$database =
    new Database();

$pdo =
    $database->connect();


$installation =
    getInstallationInformation(
        $pdo
    );


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

<i class="fas fa-server text-primary mr-2"></i>

Installation Information

</h4>

</div>


<div class="card-body">

<?php if ($installation): ?>


<div class="row">


<div class="col-md-6 mb-3">

<div class="card bg-light">

<div class="card-body">

<small class="text-muted">

Installation ID

</small>

<h6 class="mt-2">

<?= htmlspecialchars(
    $installation['installation_id'],
    ENT_QUOTES,
    'UTF-8'
) ?>

</h6>

</div>

</div>

</div>


<div class="col-md-6 mb-3">

<div class="card bg-light">

<div class="card-body">

<small class="text-muted">

Domain

</small>

<h6 class="mt-2">

<?= htmlspecialchars(
    $installation['domain_name']
        ?: 'Not bound',
    ENT_QUOTES,
    'UTF-8'
) ?>

</h6>

</div>

</div>

</div>


<div class="col-md-4 mb-3">

<div class="card bg-light">

<div class="card-body">

<small class="text-muted">

Administrator

</small>

<h6 class="mt-2">

<?= htmlspecialchars(
    $installation['username'],
    ENT_QUOTES,
    'UTF-8'
) ?>

</h6>

</div>

</div>

</div>


<div class="col-md-4 mb-3">

<div class="card bg-light">

<div class="card-body">

<small class="text-muted">

Status

</small>

<h6 class="mt-2">


<?php

$status =
    $installation['status'];

?>


<?php if ($status === 'used'): ?>

<span class="badge badge-success">

<i class="fas fa-check mr-1"></i>

Activated

</span>

<?php elseif ($status === 'locked'): ?>

<span class="badge badge-danger">

<i class="fas fa-lock mr-1"></i>

Locked

</span>

<?php else: ?>

<span class="badge badge-warning">

<?= htmlspecialchars(
    ucfirst($status)
) ?>

</span>

<?php endif; ?>


</h6>

</div>

</div>

</div>


<div class="col-md-4 mb-3">

<div class="card bg-light">

<div class="card-body">

<small class="text-muted">

Created

</small>

<h6 class="mt-2">

<?= htmlspecialchars(
    $installation['created_at'],
    ENT_QUOTES,
    'UTF-8'
) ?>

</h6>

</div>

</div>

</div>


</div>


<div class="alert alert-info">

<i class="fas fa-info-circle mr-2"></i>

This installation is permanently bound to its installation identity.

The activation token is one-time use and cannot be reused after activation.

</div>


<?php else: ?>


<div class="alert alert-warning">

<i class="fas fa-exclamation-triangle mr-2"></i>

No installation activation record was found.

</div>


<?php endif; ?>

</div>

</div>

</div>

</section>

</div>


<?php

require_once '../../includes/dashboard/footer.php';

?>