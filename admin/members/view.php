<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

if (!isset($_GET['id'])) {
    die('Member ID missing.');
}

$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->prepare("
    SELECT *
    FROM users
    WHERE user_id = :user_id
    LIMIT 1
");

$stmt->execute([
    ':user_id' => $_GET['id']
]);

$member = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$member) {
    die('Member not found.');
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Member Profile</title>
</head>
<body>

<h2>Member Profile</h2>

<hr>

<p><strong>Member Code:</strong> <?= htmlspecialchars($member['member_code']); ?></p>

<p><strong>Full Name:</strong>
<?= htmlspecialchars(
    $member['first_name'] . ' ' .
    $member['middle_name'] . ' ' .
    $member['last_name']
); ?>
</p>

<p><strong>Gender:</strong> <?= htmlspecialchars($member['gender']); ?></p>

<p><strong>Date of Birth:</strong> <?= htmlspecialchars($member['date_of_birth']); ?></p>

<p><strong>Phone:</strong> <?= htmlspecialchars($member['phone']); ?></p>

<p><strong>Email:</strong> <?= htmlspecialchars($member['email']); ?></p>

<p><strong>Birth Place:</strong> <?= htmlspecialchars($member['birth_place']); ?></p>

<p><strong>Current Residence:</strong> <?= htmlspecialchars($member['current_residence']); ?></p>

<p><strong>Marital Status:</strong> <?= htmlspecialchars($member['marital_status']); ?></p>

<p><strong>Biography:</strong></p>

<p>
<?= nl2br(htmlspecialchars($member['biography'] ?? '')); ?>
</p>

<hr>

<p>
    <a href="index.php">← Back to Members</a>
</p>

</body>
</html>