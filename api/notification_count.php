<?php
header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0, 'error' => 'Not logged in']);
    exit;
}

require_once '../config/database.php';
require_once '../config/tenant.php';

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
    echo json_encode(['count' => 0, 'error' => 'Database connection failed']);
    exit;
}

try {
    // Auto-generate today's Birthday notifications (idempotent).
    // Runs on every dashboard load / badge poll so it appears "as soon
    // as the system is opened", without needing a cron job.
    require_once __DIR__ . '/../config/birthday_notifications.php';
    generateBirthdayNotifications($pdo, (int)$clanId);

    // First check if is_read column exists
    $checkColumn = $pdo->query("SHOW COLUMNS FROM notifications LIKE 'is_read'");
    $columnExists = $checkColumn->rowCount() > 0;
    
    if (!$columnExists) {
        // If column doesn't exist, count all notifications
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM notifications
            WHERE user_id = ?
              AND clan_id = ?
        ");
    } else {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM notifications
            WHERE user_id = ?
              AND is_read = 0
              AND clan_id = ?
        ");
    }
    
    $stmt->execute([$_SESSION['user_id'], $clanId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'count' => (int)$result['count'],
        'user_id' => $_SESSION['user_id'],
        'column_exists' => $columnExists
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['count' => 0, 'error' => $e->getMessage()]);
}
