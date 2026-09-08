<?php

require_once '../config/database.php';
require_once '../config/auth.php';

requireLogin();

header('Content-Type: application/json');

$database = new Database();
$pdo = $database->connect();

$clanId = getCurrentClanId();

$q = trim($_GET['q'] ?? '');

if ($q === '') {
    echo json_encode([
        'success' => true,
        'members' => []
    ]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        u.user_id,
        u.username,
        u.profile_picture,
        r.role_name

    FROM users u

    LEFT JOIN roles r
        ON u.role_id = r.role_id

    WHERE
        u.account_status = 'active'

    AND u.clan_id = ?

    AND u.username LIKE ?

    ORDER BY u.username ASC

    LIMIT 8
");

$stmt->execute([
    $clanId,
    '%' . $q . '%'
]);

$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'members' => $members
]);

exit;