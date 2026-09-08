<?php
require_once 'config/database.php';
require_once 'config/language.php';
require_once 'config/clan_db.php';

// Resolve database by domain for multi-tenant support
$domainDatabase = getCurrentDomainDatabase();
$database = new Database($domainDatabase);
$pdo = $database->connect();

/* Statistics */
$members = 0;
$events = 0;
$contributions = 0;

try {

    $members = (int)$pdo->query("
        SELECT COUNT(*)
        FROM users
        WHERE account_status = 'active'
    ")->fetchColumn();

    $events = (int)$pdo->query("
        SELECT COUNT(*)
        FROM events
    ")->fetchColumn();

    $contributions = (int)$pdo->query("
        SELECT COUNT(*)
        FROM contributions
        WHERE status = 'active'
    ")->fetchColumn();

} catch (Exception $e) {
    // Keep homepage working even if statistics tables are unavailable
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title><?= __('clan_management_system') ?></title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
rel="stylesheet">

<style>

/* =========================================================
   GLOBAL
========================================================= */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    font-family: "Segoe UI", Arial, sans-serif;
    color: #172033;
    background: #fff;
}

a {
    text-decoration: none;
}


/* =========================================================
   NAVBAR
========================================================= */

.navbar {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1000;
    padding: 22px 0;
    background: transparent;
}

.navbar.scrolled {
    position: fixed;
    background: rgba(8, 20, 38, .96);
    box-shadow: 0 5px 25px rgba(0,0,0,.15);
    padding: 13px 0;
}

.brand {
    color: #fff;
    font-size: 25px;
    font-weight: 800;
    letter-spacing: .5px;
}

.brand i {
    margin-right: 9px;
}

.navbar-nav .nav-link {
    color: rgba(255,255,255,.9);
    font-weight: 500;
    margin-left: 25px;
}

.navbar-nav .nav-link:hover {
    color: #fff;
}

.login-btn {
    background: #fff;
    color: #172033 !important;
    padding: 10px 22px !important;
    border-radius: 30px;
    font-weight: 700 !important;
}


/* =========================================================
   HERO
========================================================= */

.hero {
    min-height: 100vh;
    position: relative;
    display: flex;
    align-items: center;
    background:
        linear-gradient(
            90deg,
            rgba(5,15,30,.88),
            rgba(5,15,30,.55),
            rgba(5,15,30,.25)
        ),
        url("assets/images/family-2.jpg")
        center/cover no-repeat;
}

.hero-content {
    max-width: 760px;
    color: white;
    padding-top: 50px;
}

.hero-label {
    display: inline-block;
    padding: 8px 18px;
    border: 1px solid rgba(255,255,255,.4);
    border-radius: 30px;
    font-size: 13px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    margin-bottom: 22px;
}

.hero h1 {
    font-size: clamp(45px, 6vw, 78px);
    line-height: 1.05;
    font-weight: 800;
    margin-bottom: 25px;
}

.hero h1 span {
    color: #f5c542;
}

.hero p {
    font-size: 20px;
    line-height: 1.7;
    color: rgba(255,255,255,.86);
    max-width: 650px;
}

.hero-buttons {
    margin-top: 35px;
}

.btn-main {
    background: #f5c542;
    color: #172033;
    padding: 14px 30px;
    border-radius: 35px;
    font-weight: 700;
    display: inline-block;
    margin-right: 10px;
}

.btn-main:hover {
    background: #fff;
    color: #172033;
}

.btn-outline {
    color: #fff;
    border: 1px solid rgba(255,255,255,.6);
    padding: 13px 28px;
    border-radius: 35px;
    display: inline-block;
}

.btn-outline:hover {
    background: #fff;
    color: #172033;
}


/* =========================================================
   HERO BOTTOM
========================================================= */

.hero-bottom {
    position: absolute;
    bottom: 35px;
    left: 0;
    width: 100%;
    color: white;
}

.hero-bottom small {
    opacity: .7;
}


/* =========================================================
   SECTION
========================================================= */

.section {
    padding: 100px 0;
}

.section-label {
    text-transform: uppercase;
    letter-spacing: 2px;
    color: #b28a19;
    font-size: 13px;
    font-weight: 700;
}

.section-title {
    font-size: 42px;
    font-weight: 800;
    margin-top: 10px;
    margin-bottom: 20px;
}

.section-text {
    color: #667085;
    line-height: 1.8;
    font-size: 17px;
}


/* =========================================================
   ABOUT IMAGE
========================================================= */

.about-image {
    height: 500px;
    border-radius: 25px;
    background:
        url("assets/images/family-1.jpg")
        center/cover no-repeat;
    position: relative;
    overflow: hidden;
}

.about-image::after {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(
        to top,
        rgba(0,0,0,.45),
        transparent
    );
}


/* =========================================================
   VALUES
========================================================= */

.value-card {
    padding: 35px;
    border-radius: 20px;
    background: #fff;
    box-shadow: 0 15px 45px rgba(15,23,42,.08);
    height: 100%;
    transition: .3s;
}

.value-card:hover {
    transform: translateY(-8px);
}

.value-icon {
    width: 58px;
    height: 58px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #172033;
    color: #f5c542;
    font-size: 23px;
    margin-bottom: 22px;
}

.value-card h4 {
    font-weight: 750;
}


/* =========================================================
   STATISTICS
========================================================= */

.stats {
    background:
        linear-gradient(
            rgba(7,19,35,.92),
            rgba(7,19,35,.92)
        ),
        url("assets/images/family-3.jpg")
        center/cover fixed;
    color: white;
}

.stat {
    text-align: center;
    padding: 25px;
}

.stat i {
    color: #f5c542;
    font-size: 30px;
    margin-bottom: 15px;
}

.stat h2 {
    font-size: 45px;
    font-weight: 800;
}

.stat p {
    opacity: .7;
}


/* =========================================================
   FAMILY COMMUNITY
========================================================= */

.community {
    background: #f7f8fa;
}

.community-image {
    height: 560px;
    border-radius: 25px;
    background:
        linear-gradient(
            rgba(0,0,0,.15),
            rgba(0,0,0,.15)
        ),
        url("assets/images/family-3.jpg")
        center/cover no-repeat;
}


/* =========================================================
   FEATURES
========================================================= */

.feature {
    padding: 25px 0;
    border-bottom: 1px solid #e6e8ec;
}

.feature:last-child {
    border-bottom: none;
}

.feature i {
    color: #b28a19;
    font-size: 22px;
    margin-right: 15px;
}


/* =========================================================
   CTA
========================================================= */

.cta {
    padding: 100px 0;
    background:
        linear-gradient(
            90deg,
            rgba(8,20,38,.94),
            rgba(8,20,38,.75)
        ),
        url("assets/images/family-2.jpg")
        center/cover;
    color: white;
}

.cta h2 {
    font-size: 45px;
    font-weight: 800;
}


/* =========================================================
   FOOTER
========================================================= */

footer {
    background: #071321;
    color: white;
    padding: 55px 0 25px;
}

footer p {
    color: rgba(255,255,255,.6);
}

.footer-line {
    border-top: 1px solid rgba(255,255,255,.1);
    margin-top: 35px;
    padding-top: 20px;
    color: rgba(255,255,255,.45);
}


/* =========================================================
   MOBILE
========================================================= */

@media(max-width: 768px) {

    .navbar {
        padding: 16px 0;
    }

    .navbar-nav {
        background: rgba(8,20,38,.97);
        padding: 20px;
        border-radius: 15px;
        margin-top: 12px;
    }

    .navbar-nav .nav-link {
        margin: 5px 0;
    }

    .navbar-toggler {
        border-color: rgba(255,255,255,.5);
    }

    /* Light toggler icon is not visible over dark hero; draw custom one */
    .navbar-toggler .navbar-toggler-icon {
        background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba(255,255,255,0.9)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
    }

    .hero {
        min-height: 90vh;
    }

    .hero h1 {
        font-size: 45px;
    }

    .hero p {
        font-size: 17px;
    }

    .hero-bottom {
        bottom: 20px;
        padding: 0 10px;
    }

    .hero-bottom .d-flex {
        flex-wrap: wrap;
        justify-content: center !important;
        text-align: center;
        font-size: 12px;
        line-height: 1.5;
    }

    .hero-bottom small {
        display: inline-flex;
        align-items: center;
        white-space: normal;
    }

    .hero-bottom .d-flex small {
        margin: 2px 6px;
    }

    .section {
        padding: 70px 0;
    }

    .section-title {
        font-size: 34px;
    }

    .about-image,
    .community-image {
        height: 350px;
        margin-bottom: 35px;
    }

    .cta h2 {
        font-size: 35px;
    }

}

/* =========================================================
   EXTRA SMALL DEVICES (phones) - ensure everything fits
========================================================= */

@media(max-width: 575.98px) {

    .navbar {
        padding: 14px 0;
    }

    .brand {
        font-size: 18px;
    }

    .hero {
        min-height: 88vh;
    }

    .hero h1 {
        font-size: 34px;
        line-height: 1.15;
    }

    .hero p {
        font-size: 16px;
        line-height: 1.6;
    }

    .hero-buttons {
        display: flex;
        flex-direction: column;
        gap: 12px;
        width: 100%;
    }

    .hero-buttons .btn-main,
    .hero-buttons .btn-outline {
        margin-right: 0;
        text-align: center;
        width: 100%;
    }

    .section-title {
        font-size: 28px;
    }

    .section {
        padding: 60px 0;
    }

    .value-card {
        padding: 24px;
    }

    .stat {
        padding: 20px;
    }

    .stat h2 {
        font-size: 34px;
    }

    .feature {
        padding: 18px 0;
    }

    .feature .section-text {
        margin-left: 0 !important;
    }

    .cta {
        padding: 70px 0;
    }

    .cta h2 {
        font-size: 28px;
    }

    .cta p {
        font-size: 16px !important;
    }

    /* Prevent horizontal overflow from broken-out leaves/content */
    .hero-bottom small {
        white-space: normal;
        line-height: 1.4;
    }

    .hero-bottom .container > div {
        gap: 6px;
    }

    .hero-bottom .d-flex small {
        margin: 3px 4px;
    }

    /* Keep Bootstrap g-5 rows (about/community) inside the viewport */
    .row.g-5 {
        --bs-gutter-x: 1.5rem;
    }

}

</style>

</head>


<body>


<!-- ======================================================
     NAVBAR
======================================================= -->

<nav class="navbar navbar-expand-lg">

<div class="container">

<a href="home.php" class="brand">

<i class="fas fa-people-group"></i>

<?= __('clan_management_system') ?>

</a>


<button
class="navbar-toggler"
type="button"
data-bs-toggle="collapse"
data-bs-target="#mainNav"
>

<span class="navbar-toggler-icon"></span>

</button>


<div
class="collapse navbar-collapse"
id="mainNav"
>

<ul class="navbar-nav ms-auto align-items-lg-center">

<li class="nav-item">
<a class="nav-link" href="#about">
<?= __('about') ?>
</a>
</li>

<li class="nav-item">
<a class="nav-link" href="#values">
<?= __('values') ?>
</a>
</li>

<li class="nav-item">
<a class="nav-link" href="#community">
<?= __('community') ?>
</a>
</li>

<li class="nav-item">
<a class="nav-link" href="#events">
<?= __('events') ?>
</a>
</li>

<li class="nav-item ms-lg-3">
<a
class="nav-link login-btn"
href="login.php"
>
<?= __('login') ?>
</a>
</li>

</ul>

</div>

</div>

</nav>


<!-- ======================================================
     HERO
======================================================= -->

<section class="hero">

<div class="container">

<div class="hero-content">

<div class="hero-label">

<i class="fas fa-heart"></i>

<?= __('together_as_one') ?>

</div>


<h1>

<?= __('our_roots') ?>

<br>

<span><?= __('our_family') ?></span>

<br>

<?= __('our_future') ?>

</h1>


<p>

<?= __('hero_description') ?>

</p>


<div class="hero-buttons">

<a
href="login.php"
class="btn-main"
>

<?= __('enter_family_portal') ?>

<i class="fas fa-arrow-right ms-2"></i>

</a>


<a
href="#about"
class="btn-outline"
>

<?= __('discover_more') ?>

</a>

</div>

</div>

</div>


<div class="hero-bottom">

<div class="container">

<div class="d-flex justify-content-between">

<small>
<?= __('preserving_heritage') ?>
</small>

<small>
<?= __('connecting_generations') ?>
</small>

<small>
<?= __('building_tomorrow') ?>
</small>

</div>

</div>

</div>

</section>


<!-- ======================================================
     ABOUT
======================================================= -->

<section
class="section"
id="about"
>

<div class="container">

<div class="row align-items-center g-5">

<div class="col-lg-6">

<div class="about-image"></div>

</div>


<div class="col-lg-6">

<div class="section-label">
<?= __('who_we_are') ?>
</div>

<h2 class="section-title">

<?= __('more_than_a_family') ?>
<br>
<?= __('a_connected_community') ?>

</h2>

<p class="section-text">

<?= __('about_description') ?>

</p>

<p class="section-text">

<?= __('about_description_2') ?>

</p>

<a
href="login.php"
class="btn-main mt-3"
>

<?= __('explore_the_portal') ?>

</a>

</div>

</div>

</div>

</section>


<!-- ======================================================
     VALUES
======================================================= -->

<section
class="section"
id="values"
style="background:#f7f8fa;"
>

<div class="container">

<div class="text-center mb-5">

<div class="section-label">
<?= __('what_we_believe') ?>
</div>

<h2 class="section-title">
<?= __('built_around_family_values') ?>
</h2>

<p class="section-text mx-auto"
style="max-width:650px;">

<?= __('tech_brings_people_closer') ?>

</p>

</div>


<div class="row g-4">


<div class="col-md-4">

<div class="value-card">

<div class="value-icon">

<i class="fas fa-tree"></i>

</div>

<h4><?= __('preserve_heritage') ?></h4>

<p class="section-text">

<?= __('preserve_heritage_desc') ?>

</p>

</div>

</div>


<div class="col-md-4">

<div class="value-card">

<div class="value-icon">

<i class="fas fa-handshake"></i>

</div>

<h4><?= __('strengthen_unity') ?></h4>

<p class="section-text">

<?= __('strengthen_unity_desc') ?>

</p>

</div>

</div>


<div class="col-md-4">

<div class="value-card">

<div class="value-icon">

<i class="fas fa-seedling"></i>

</div>

<h4><?= __('build_the_future') ?></h4>

<p class="section-text">

<?= __('build_the_future_desc') ?>

</p>

</div>

</div>


</div>

</div>

</section>


<!-- ======================================================
     STATISTICS
======================================================= -->

<section class="section stats">

<div class="container">

<div class="text-center mb-5">

<div class="section-label">
<?= __('our_community') ?>
</div>

<h2 class="section-title text-white">

<?= __('growing_together') ?>

</h2>

</div>


<div class="row">


<div class="col-md-4">

<div class="stat">

<i class="fas fa-users"></i>

<h2><?= $members ?></h2>

<p><?= __('family_members') ?></p>

</div>

</div>


<div class="col-md-4">

<div class="stat">

<i class="fas fa-calendar-days"></i>

<h2><?= $events ?></h2>

<p><?= __('family_events') ?></p>

</div>

</div>


<div class="col-md-4">

<div class="stat">

<i class="fas fa-hand-holding-heart"></i>

<h2><?= $contributions ?></h2>

<p><?= __('active_contributions') ?></p>

</div>

</div>


</div>

</div>

</section>


<!-- ======================================================
     COMMUNITY
======================================================= -->

<section
class="section community"
id="community"
>

<div class="container">

<div class="row align-items-center g-5">


<div class="col-lg-6">

<div class="community-image"></div>

</div>


<div class="col-lg-6">

<div class="section-label">
<?= __('one_digital_community') ?>
</div>

<h2 class="section-title">

<?= __('everything_your_family_needs') ?>

</h2>


<div class="feature">

<i class="fas fa-sitemap"></i>

<strong><?= __('family_tree') ?></strong>

<p class="section-text ms-4">

<?= __('family_tree_desc') ?>

</p>

</div>


<div class="feature">

<i class="fas fa-comments"></i>

<strong><?= __('family_messages') ?></strong>

<p class="section-text ms-4">

<?= __('family_messages_desc') ?>

</p>

</div>


<div class="feature">

<i class="fas fa-calendar-check"></i>

<strong><?= __('events') ?></strong>

<p class="section-text ms-4">

<?= __('events_desc') ?>

</p>

</div>


<div class="feature">

<i class="fas fa-wallet"></i>

<strong><?= __('contributions_wallets') ?></strong>

<p class="section-text ms-4">

<?= __('contributions_wallets_desc') ?>

</p>

</div>


<div class="feature">

<i class="fas fa-images"></i>

<strong><?= __('family_gallery') ?></strong>

<p class="section-text ms-4">

<?= __('family_gallery_desc') ?>

</p>

</div>


</div>

</div>

</div>

</section>


<!-- ======================================================
     CTA
======================================================= -->

<section class="cta">

<div class="container text-center">

<div class="section-label">
<?= __('your_family_your_legacy') ?>
</div>

<h2 class="mt-3 mb-4">

<?= __('stay_connected') ?>
<br>
<?= __('stay_together') ?>

</h2>

<p
class="mx-auto mb-4"
style="max-width:650px;color:rgba(255,255,255,.75);font-size:18px;"
>

<?= __('join_family_digital_community') ?>

</p>


<a
href="login.php"
class="btn-main"
>

<?= __('access_family_portal') ?>

<i class="fas fa-arrow-right ms-2"></i>

</a>

</div>

</section>


<!-- ======================================================
     FOOTER
======================================================= -->

<footer>

<div class="container">

<div class="row g-4">


<div class="col-lg-6">

<h3>

<i class="fas fa-people-group"></i>

<?= __('clan_management_system') ?>

</h3>

<p class="mt-3">

<?= __('preserving_heritage_strengthening') ?>

</p>

</div>


<div class="col-lg-3">

<h5><?= __('quick_links') ?></h5>

<p class="mt-3">

<a
href="#about"
style="color:rgba(255,255,255,.6);"
>
<?= __('about') ?>
</a>

</p>

<p>

<a
href="#community"
style="color:rgba(255,255,255,.6);"
>
<?= __('community') ?>
</a>

</p>

<p>

<a
href="login.php"
style="color:rgba(255,255,255,.6);"
>
<?= __('login') ?>
</a>

</p>

</div>


<div class="col-lg-3">

<h5><?= __('contact') ?></h5>

<p class="mt-3">

<i class="fas fa-phone me-2"></i>

+255 763 801 187

</p>

<p>

<i class="fas fa-envelope me-2"></i>

iddymashimba77@gmail.com

</p>

<p>

<i class="fas fa-location-dot me-2"></i>

Tanzania

</p>

</div>


</div>


<div class="footer-line text-center">

© <?= date('Y') ?>

<?= __('clan_management_system') ?>.

<?= __('all_rights_reserved') ?>

</div>

</div>

</footer>


<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


<script>

window.addEventListener("scroll", function() {

    const navbar =
        document.querySelector(".navbar");

    if (window.scrollY > 80) {

        navbar.classList.add("scrolled");

    } else {

        navbar.classList.remove("scrolled");

    }

});

</script>


</body>

</html>