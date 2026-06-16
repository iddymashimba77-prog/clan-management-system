<?php

require_once '../config/constants.php';
require_once '../config/auth.php';

requireLogin();

if (!hasRole(ROLE_CHAIRPERSON)) {

    header("Location: ../login.php");

    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Chairperson Dashboard</title>

</head>

<body>

    <h1>Welcome <?= htmlspecialchars($_SESSION['full_name']); ?></h1>

    <hr>

    <p>Member Code:
        <?= htmlspecialchars($_SESSION['member_code']); ?>
    </p>

    <p>Role:
        <?= htmlspecialchars((string) $_SESSION['role_id']); ?>
    </p>

    <br>

    <a href="../logout.php">
        Logout
    </a>

</body>

</html>