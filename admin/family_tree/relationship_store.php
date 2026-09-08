<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: relationships.php');
    exit;
}

$pdo = $database->connect();

$member_user_id = (int)($_POST['member_user_id'] ?? 0);
$father_user_id = !empty($_POST['father_user_id'])
    ? (int)$_POST['father_user_id']
    : null;

$mother_user_id = !empty($_POST['mother_user_id'])
    ? (int)$_POST['mother_user_id']
    : null;

$child_type = $_POST['child_type'] ?? 'biological';

if ($member_user_id <= 0) {
    header('Location: relationships.php?error=invalid_member');
    exit;
}

if (!in_array($child_type, ['biological', 'adopted'], true)) {
    $child_type = 'biological';
}

try {

    $stmt = $pdo->prepare("
        INSERT INTO family_relationships
        (
            member_user_id,
            father_user_id,
            mother_user_id,
            child_type
        )
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
        $member_user_id,
        $father_user_id,
        $mother_user_id,
        $child_type
    ]);

    /*
     * Keep users table synchronized with family_relationships.
     */

    $update = $pdo->prepare("
        UPDATE users
        SET
            father_user_id = ?,
            mother_user_id = ?
        WHERE user_id = ?
        LIMIT 1
    ");

    $update->execute([
        $father_user_id,
        $mother_user_id,
        $member_user_id
    ]);

    $log = $pdo->prepare("
        INSERT INTO activity_logs
        (
            user_id,
            action,
            table_name,
            record_id,
            description,
            ip_address
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $log->execute([
        $_SESSION['user_id'] ?? null,
        'CREATE_FAMILY_RELATIONSHIP',
        'family_relationships',
        $pdo->lastInsertId(),
        'Family relationship created.',
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);

    header('Location: relationships.php?success=created');
    exit;

} catch (PDOException $e) {

    header('Location: relationships.php?error=save_failed');
    exit;
}