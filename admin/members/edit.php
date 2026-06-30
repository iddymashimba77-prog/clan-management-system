<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

if (!isset($_GET['id'])) {
    die("Member ID is missing.");
}

$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->prepare("
    SELECT *
    FROM users
    WHERE user_id = :id
    LIMIT 1
");

$stmt->execute([
    ':id' => $_GET['id']
]);

$member = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$member) {
    die("Member not found.");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Member</title>
</head>
<body>

<h2>Edit Member</h2>

<form action="update.php" method="POST" enctype="multipart/form-data">

    <input type="hidden" name="user_id" value="<?= $member['user_id']; ?>">

    <label>First Name</label><br>
    <input
        type="text"
        name="first_name"
        value="<?= htmlspecialchars($member['first_name']); ?>"
        required>
    <br><br>

    <label>Middle Name</label><br>
    <input
        type="text"
        name="middle_name"
        value="<?= htmlspecialchars($member['middle_name']); ?>">
    <br><br>

    <label>Last Name</label><br>
    <input
        type="text"
        name="last_name"
        value="<?= htmlspecialchars($member['last_name']); ?>"
        required>
    <br><br>

    <label>Gender</label><br>

    <select name="gender" required>

        <option value="Male"
            <?= $member['gender']=='Male' ? 'selected' : ''; ?>>
            Male
        </option>

        <option value="Female"
            <?= $member['gender']=='Female' ? 'selected' : ''; ?>>
            Female
        </option>

    </select>

    <br><br>

    <label>Date of Birth</label><br>
    <input
        type="date"
        name="date_of_birth"
        value="<?= $member['date_of_birth']; ?>">
    <br><br>

    <label>Phone</label><br>
    <input
        type="text"
        name="phone"
        value="<?= htmlspecialchars($member['phone']); ?>">
    <br><br>

    <label>Email</label><br>
    <input
        type="email"
        name="email"
        value="<?= htmlspecialchars($member['email']); ?>">
    <br><br>

    <label>Birth Place</label><br>
    <input
        type="text"
        name="birth_place"
        value="<?= htmlspecialchars($member['birth_place']); ?>">
    <br><br>

    <label>Current Residence</label><br>
    <input
        type="text"
        name="current_residence"
        value="<?= htmlspecialchars($member['current_residence']); ?>">
    <br><br>

    <label>Marital Status</label><br>

    <select name="marital_status">

        <option value="Single" <?= $member['marital_status']=="Single" ? "selected" : ""; ?>>Single</option>

        <option value="Married" <?= $member['marital_status']=="Married" ? "selected" : ""; ?>>Married</option>

        <option value="Divorced" <?= $member['marital_status']=="Divorced" ? "selected" : ""; ?>>Divorced</option>

        <option value="Widowed" <?= $member['marital_status']=="Widowed" ? "selected" : ""; ?>>Widowed</option>

    </select>

    <br><br>

    <label>Father</label><br>

    <select name="father_user_id">

        <option value="">Select Father</option>

        <?php

        $fathers = $pdo->query("
            SELECT user_id, member_code, first_name, last_name
            FROM users
            WHERE is_deleted = 0
            ORDER BY first_name
        ");

        while($father = $fathers->fetch(PDO::FETCH_ASSOC)):

        ?>

        <option
            value="<?= $father['user_id']; ?>"
            <?= $member['father_user_id']==$father['user_id'] ? "selected" : ""; ?>>

            <?= htmlspecialchars($father['member_code']." - ".$father['first_name']." ".$father['last_name']); ?>

        </option>

        <?php endwhile; ?>

    </select>

    <br><br>

    <label>Mother</label><br>

    <select name="mother_user_id">

        <option value="">Select Mother</option>

        <?php

        $mothers = $pdo->query("
            SELECT user_id, member_code, first_name, last_name
            FROM users
            WHERE is_deleted = 0
            ORDER BY first_name
        ");

        while($mother = $mothers->fetch(PDO::FETCH_ASSOC)):

        ?>

        <option
            value="<?= $mother['user_id']; ?>"
            <?= $member['mother_user_id']==$mother['user_id'] ? "selected" : ""; ?>>

            <?= htmlspecialchars($mother['member_code']." - ".$mother['first_name']." ".$mother['last_name']); ?>

        </option>

        <?php endwhile; ?>

    </select>

    <br><br>

    <label>Spouse</label><br>

    <select name="spouse_user_id">

        <option value="">Select Spouse</option>

        <?php

        $spouses = $pdo->query("
            SELECT user_id, member_code, first_name, last_name
            FROM users
            WHERE is_deleted = 0
            ORDER BY first_name
        ");

        while($spouse = $spouses->fetch(PDO::FETCH_ASSOC)):

        ?>

        <option
            value="<?= $spouse['user_id']; ?>"
            <?= $member['spouse_user_id']==$spouse['user_id'] ? "selected" : ""; ?>>

            <?= htmlspecialchars($spouse['member_code']." - ".$spouse['first_name']." ".$spouse['last_name']); ?>

        </option>

        <?php endwhile; ?>

    </select>

    <br><br>

    <label>Biography</label><br>

    <textarea
        name="biography"
        rows="5"
        cols="60"><?= htmlspecialchars($member['biography']); ?></textarea>

    <br><br>

    <label>Profile Picture</label><br>

    <input
        type="file"
        name="profile_picture">

    <br><br>

    <button type="submit">
        Update Member
    </button>

</form>

</body>
</html>