<?php

require_once '../config/constants.php';
require_once '../config/auth.php';

requireLogin();

if (!hasRole(ROLE_CHAIRPERSON)) {

    header("Location: ../login.php");

    exit;
}

include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/sidebar.php';

?>

<div class="main-content">

    <h1>Dashboard</h1>

    <p>
        Welcome,
        <?= htmlspecialchars($_SESSION['full_name']); ?>
    </p>

</div>

<?php include 'includes/footer.php'; ?>