<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: settings.php');
    exit;
}

$pdo = $database->connect();

$userId = $_SESSION['user_id'] ?? 0;


/*
|--------------------------------------------------------------------------
| Check admin
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT r.role_name
    FROM users u
    LEFT JOIN roles r ON r.role_id = u.role_id
    WHERE u.user_id = ?
      AND u.is_deleted = 0
      AND u.account_status = 'active'
    LIMIT 1
");

$stmt->execute([$userId]);

$role = $stmt->fetchColumn();

if (!$role || strtolower($role) !== 'administrator') {
    http_response_code(403);
    exit('Access denied.');
}


/*
|--------------------------------------------------------------------------
| CSRF
|--------------------------------------------------------------------------
*/

$token = $_POST['csrf_token'] ?? '';

if (
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $token)
) {
    http_response_code(403);
    exit('Invalid CSRF token.');
}


/*
|--------------------------------------------------------------------------
| Get columns dynamically
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SHOW COLUMNS FROM clan_settings
");

$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

$allowed = [];

foreach ($columns as $column) {

    if (
        in_array(
            $column,
            ['id', 'created_at', 'updated_at'],
            true
        )
    ) {
        continue;
    }

    $allowed[] = $column;
}


/*
|--------------------------------------------------------------------------
| Update settings
|--------------------------------------------------------------------------
*/

$setParts = [];
$params = [];

foreach ($allowed as $column) {

    if (!array_key_exists($column, $_POST)) {
        continue;
    }

    $setParts[] = "`{$column}` = ?";
    $params[] = trim((string)$_POST[$column]);
}

if (!$setParts) {
    header('Location: settings.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Update first settings record
|--------------------------------------------------------------------------
*/

$sql = "
    UPDATE clan_settings
    SET " . implode(', ', $setParts) . "
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);


/*
|--------------------------------------------------------------------------
| Activity log
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("
        INSERT INTO activity_logs
        (
            user_id,
            action,
            table_name,
            description,
            ip_address
        )
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $userId,
        'UPDATE_SYSTEM_SETTINGS',
        'clan_settings',
        'System settings updated by administrator.',
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);

} catch (PDOException $e) {
    // Do not break settings update if logging fails.
}


header('Location: settings.php?success=1');
exit;