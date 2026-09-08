<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

require_once '../../config/tenant.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$clanId = getCurrentClanId();

// Get database name from session first
$databaseName = $_SESSION['database_name'] ?? null;

// If session doesn't have database name, try to get from clan_owner
if ($databaseName === null || $databaseName === '') {
    try {
        $ownerPdo = new PDO(
            'mysql:unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=clan_owner;charset=utf8mb4',
            'root',
            '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
        $stmt = $ownerPdo->prepare("SELECT database_name FROM owner_installations WHERE clan_id = ? LIMIT 1");
        $stmt->execute([$clanId]);
        $databaseName = $stmt->fetchColumn();
        
        // Set it in session for future requests
        if ($databaseName && session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['database_name'] = $databaseName;
        }
    } catch (Exception $e) {
        // Fallback to default
        $databaseName = null;
    }
}

try {
    $database = new Database($databaseName);
    $pdo = $database->connect();
} catch (Exception $e) {
    die(__('db_connection_failed') . " " . $e->getMessage());
}

$user_id  = (int)$_POST['user_id'];
$username = trim($_POST['username']);
$email    = trim($_POST['email']);
$phone    = trim($_POST['phone']);
$role_id  = (int)$_POST['role_id'];

$stmt = $pdo->prepare("
SELECT role_name
FROM roles
WHERE role_id=(
    SELECT role_id
    FROM users
    WHERE user_id=?
    AND clan_id=?
)
");

$stmt->execute([$user_id, $clanId]);

$currentRole = $stmt->fetchColumn();

if($currentRole == 'Administrator'){
    die(__('access_denied'));
}

$stmt = $pdo->prepare("
UPDATE users
SET
    username = ?,
    email = ?,
    phone = ?,
    role_id = ?
WHERE user_id = ?
AND clan_id = ?
");

$stmt->execute([
    $username,
    $email,
    $phone,
    $role_id,
    $user_id,
    $clanId
]);

header("Location: index.php?updated=1");
exit;