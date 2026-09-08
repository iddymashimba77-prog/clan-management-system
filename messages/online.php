<?php

require_once '../config/database.php';
require_once '../config/auth.php';

requireLogin();

header(
    'Content-Type: application/json'
);

$database = new Database();
$pdo = $database->connect();

$user_id =
    (int)$_SESSION['user_id'];

$clanId = getCurrentClanId();


/*
|--------------------------------------------------------------------------
| Update Current User
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    INSERT INTO user_online_status
    (
        user_id,
        last_seen,
        is_online,
        clan_id
    )

    VALUES
    (
        ?,
        NOW(),
        1,
        ?
    )

    ON DUPLICATE KEY UPDATE

        last_seen = NOW(),

        is_online = 1
");


$stmt->execute([
    $user_id,
    $clanId
]);


/*
|--------------------------------------------------------------------------
| Mark Old Users Offline
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    UPDATE user_online_status

    SET is_online = 0

    WHERE last_seen <
        DATE_SUB(
            NOW(),
            INTERVAL 15 SECOND
        )

      AND clan_id = ?
");

$stmt->execute([$clanId]);


/*
|--------------------------------------------------------------------------
| Get Online Members
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT

        u.user_id,

        u.username,

        u.profile_picture,

        r.role_name

    FROM user_online_status os

    INNER JOIN users u
        ON os.user_id = u.user_id

    LEFT JOIN roles r
        ON u.role_id = r.role_id

    WHERE
        os.is_online = 1

    AND os.clan_id = ?

    AND u.account_status = 'active'

    ORDER BY
        u.username ASC
");

$stmt->execute([$clanId]);


$members =
    $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );


echo json_encode([

    'success' => true,

    'online_count' =>
        count($members),

    'members' =>
        $members

]);

exit;