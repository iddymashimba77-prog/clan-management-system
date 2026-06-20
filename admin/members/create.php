<?php

require_once '../../config/constants.php';
require_once '../../config/auth.php';


requireLogin();

if (!hasRole(ROLE_CHAIRPERSON)) {
    header("Location: /Clan_Management_System/login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Family Member</title>

</head>

<body>

    <h2>Add Family Member</h2>

    <form action="store.php" method="POST" enctype="multipart/form-data">

        <label>First Name</label><br>
        <input type="text" name="first_name" required><br><br>

        <label>Middle Name</label><br>
        <input type="text" name="middle_name"><br><br>

        <label>Last Name</label><br>
        <input type="text" name="last_name" required><br><br>

        <label>Gender</label><br>
        <select name="gender" required>
            <option value="">Select</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select><br><br>

        <label>Date of Birth</label><br>
        <input type="date" name="date_of_birth"><br><br>

        <label>Phone</label><br>
        <input type="text" name="phone"><br><br>

        <label>Email</label><br>
        <input type="email" name="email"><br><br>

        <label>Birth Place</label><br>
        <input type="text" name="birth_place"><br><br>

        <label>Marital Status</label><br>

    <select name="marital_status">

        <option value="Single">Single</option>

        <option value="Married">Married</option>

        <option value="Divorced">Divorced</option>

        <option value="Widowed">Widowed</option>

    </select>

    <br><br>

        <label>Current Residence</label><br>
        <input type="text" name="current_residence"><br><br>

        <label>Biography</label><br>
        <textarea name="biography"></textarea><br><br>

        <label>Profile Picture</label><br>
        <input type="file" name="profile_picture"><br><br>

        <button type="submit">
            Save Member
        </button>

    </form>

</body>

</html>