<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->query("
    SELECT
        user_id,
        member_code,
        first_name,
        middle_name,
        last_name,
        gender,
        phone,
        email
    FROM users
    WHERE is_deleted = 0
    ORDER BY user_id DESC
");

$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Members List</title>
</head>
<body>

<h2>Family Members</h2>

<p>
    <a href="create.php">+ Add New Member</a>
</p>

<table border="1" cellpadding="10">

<tr>
    <th>ID</th>
    <th>Member Code</th>
    <th>Full Name</th>
    <th>Gender</th>
    <th>Phone</th>
    <th>Email</th>
    <th>Actions</th>
</tr>

<?php foreach ($members as $member): ?>

    <tr>

<td><?= $member['user_id']; ?></td>

<td><?= htmlspecialchars($member['member_code']); ?></td>

<td>
    <?= htmlspecialchars(
        $member['first_name'] . ' ' .
        $member['middle_name'] . ' ' .
        $member['last_name']
    ); ?>
</td>

<td><?= htmlspecialchars($member['gender']); ?></td>

<td><?= htmlspecialchars($member['phone']); ?></td>

<td><?= htmlspecialchars($member['email']); ?></td>

<td>
    <a href="view.php?id=<?= $member['user_id']; ?>">
        View
    </a>
</td>

</tr>

<?php endforeach; ?>

</table>

</body>
</html>