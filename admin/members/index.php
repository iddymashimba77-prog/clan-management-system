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

    <meta charset="UTF-8">

    <title>Members List</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>

<h2>Family Members</h2>

<p>
    <a href="create.php">
        <i class="fa fa-plus"></i> Add New Member
    </a>
</p>

<table border="1" cellpadding="10" cellspacing="0">

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
                trim(
                    $member['first_name'] . ' ' .
                    $member['middle_name'] . ' ' .
                    $member['last_name']
                )
            ); ?>
        </td>

        <td><?= htmlspecialchars($member['gender']); ?></td>

        <td><?= htmlspecialchars($member['phone']); ?></td>

        <td><?= htmlspecialchars($member['email']); ?></td>

        <td>

            <a href="view.php?id=<?= $member['user_id']; ?>" title="View">
                <i class="fa fa-eye"></i>
            </a>

            &nbsp;&nbsp;

            <a href="edit.php?id=<?= $member['user_id']; ?>" title="Edit">
                <i class="fa fa-pen"></i>
            </a>

            &nbsp;&nbsp;

            <a href="delete.php?id=<?= $member['user_id']; ?>"
               onclick="return confirm('Are you sure you want to delete this member?');"
               title="Delete">

                <i class="fa fa-trash"></i>

            </a>

        </td>

    </tr>

    <?php endforeach; ?>

</table>

</body>
</html>