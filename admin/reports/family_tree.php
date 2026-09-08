<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$pdo = $database->connect();

$totalMembers = (int)$pdo->query("
    SELECT COUNT(*)
    FROM users
    WHERE is_deleted = 0
")->fetchColumn();

$roots = (int)$pdo->query("
    SELECT COUNT(*)
    FROM users
    WHERE is_deleted = 0
      AND father_user_id IS NULL
      AND mother_user_id IS NULL
")->fetchColumn();

$marriages = (int)$pdo->query("
    SELECT COUNT(*)
    FROM marriages
")->fetchColumn();

$generations = (int)$pdo->query("
    SELECT COUNT(*)
    FROM generations
")->fetchColumn();

$relationships = (int)$pdo->query("
    SELECT COUNT(*)
    FROM family_relationships
")->fetchColumn();

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
Family Tree Report
</h4>

<a href="export.php?report=family_tree"
class="btn btn-success btn-sm">
<i class="fas fa-download mr-1"></i>
Export
</a>

</div>

<div class="card-body">

<div class="row">

<div class="col-md-3">
<div class="small-box bg-primary">
<div class="inner">
<h3><?= $totalMembers ?></h3>
<p><?= __('members') ?></p>
</div>
</div>
</div>

<div class="col-md-3">
<div class="small-box bg-info">
<div class="inner">
<h3><?= $roots ?></h3>
<p>Root Ancestors</p>
</div>
</div>
</div>

<div class="col-md-2">
<div class="small-box bg-danger">
<div class="inner">
<h3><?= $marriages ?></h3>
<p><?= __('marriages') ?></p>
</div>
</div>
</div>

<div class="col-md-2">
<div class="small-box bg-success">
<div class="inner">
<h3><?= $generations ?></h3>
<p><?= __('generations') ?></p>
</div>
</div>
</div>

<div class="col-md-2">
<div class="small-box bg-warning">
<div class="inner">
<h3><?= $relationships ?></h3>
<p><?= __('relationships') ?></p>
</div>
</div>
</div>

</div>

<div class="alert alert-info">
<i class="fas fa-info-circle mr-2"></i>
Family tree statistics are generated directly from the current clan database.
</div>

</div>

</div>

</div>
</section>
</div>

<?php require_once '../../includes/dashboard/footer.php'; ?>