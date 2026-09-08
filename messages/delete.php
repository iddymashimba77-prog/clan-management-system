<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/language.php';
require_once '../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$user_id = (int)$_SESSION['user_id'];
$clanId = getCurrentClanId();
$message_id = (int)$_GET['id'] ?? 0;
$delete_type = $_GET['type'] ?? 'me';

// Check if message exists
$stmt = $pdo->prepare("
    SELECT sender_id 
    FROM family_messages 
    WHERE message_id = ?
      AND clan_id = ?
");
$stmt->execute([$message_id, $clanId]);
$message = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$message) {
    die(__('message_not_found'));
}

$is_sender = ($message['sender_id'] == $user_id);

if($delete_type == 'all' && $is_sender) {
    // Delete for everyone - hard delete (only sender can do this)
    $stmt = $pdo->prepare("DELETE FROM family_messages WHERE message_id = ? AND clan_id = ?");
    $stmt->execute([$message_id, $clanId]);
} else {
    // Delete for me only - soft delete (for both sender and other users)
    // Check if already deleted for this user
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM family_message_deletions 
        WHERE message_id = ? AND user_id = ? AND clan_id = ?
    ");
    $stmt->execute([$message_id, $user_id, $clanId]);
    
    if($stmt->fetchColumn() == 0) {
        // Add deletion record
        $stmt = $pdo->prepare("
            INSERT INTO family_message_deletions (message_id, user_id, deleted_at, clan_id)
            VALUES (?, ?, NOW(), ?)
        ");
        $stmt->execute([$message_id, $user_id, $clanId]);
    }
}

header("Location: index.php");
exit;
