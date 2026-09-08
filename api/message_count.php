<?php
header('Content-Type: application/json');

require_once '../config/tenant.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0, 'error' => 'Not logged in', 'session' => session_id()]);
    exit;
}

require_once '../config/database.php';

$database = new Database();
$pdo = $database->connect();

$clanId = getCurrentClanId();

try {
    // First check if is_read column exists
    $checkColumn = $pdo->query("SHOW COLUMNS FROM messages LIKE 'is_read'");
    $columnExists = $checkColumn->rowCount() > 0;
    
    if (!$columnExists) {
        // If column doesn't exist, count all messages
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM messages
            WHERE receiver_id = ?
              AND clan_id = ?
        ");
    } else {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM messages
            WHERE receiver_id = ?
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
