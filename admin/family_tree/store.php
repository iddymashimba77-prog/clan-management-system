<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$memberId = (int)($_POST['member_user_id'] ?? 0);
$fatherId = !empty($_POST['father_user_id'])
    ? (int)$_POST['father_user_id']
    : null;
$motherId = !empty($_POST['mother_user_id'])
    ? (int)$_POST['mother_user_id']
    : null;
$spouseId = !empty($_POST['spouse_user_id'])
    ? (int)$_POST['spouse_user_id']
    : null;

if ($memberId <= 0) {
    header('Location: create.php?error=invalid_member');
    exit;
}

if ($fatherId === $memberId) {
    header('Location: create.php?error=self_father');
    exit;
}

if ($motherId === $memberId) {
    header('Location: create.php?error=self_mother');
    exit;
}

if ($spouseId === $memberId) {
    header('Location: create.php?error=self_spouse');
    exit;
}

$pdo = $database->connect();

try {

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        UPDATE users
        SET
            father_user_id = ?,
            mother_user_id = ?,
            spouse_user_id = ?,
            updated_at = CURRENT_TIMESTAMP
        WHERE user_id = ?
          AND is_deleted = 0
    ");

    $stmt->execute([
        $fatherId,
        $motherId,
        $spouseId,
        $memberId
    ]);


    /*
     * Keep spouse relationship synchronized.
     */
    if ($spouseId) {

        $stmt = $pdo->prepare("
            UPDATE users
            SET spouse_user_id = ?
            WHERE user_id = ?
              AND is_deleted = 0
        ");

        $stmt->execute([
            $memberId,
            $spouseId
        ]);
    }


    /*
     * Activity log
     */
    $stmt = $pdo->prepare("
        INSERT INTO activity_logs
        (
            user_id,
            action,
            table_name,
            record_id,
            description,
            ip_address
        )
        VALUES
        (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $_SESSION['user_id'] ?? null,
        'CREATE_FAMILY_RELATIONSHIP',
        'users',
        $memberId,
        'Family relationship created/updated by administrator.',
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);

    $pdo->commit();

    header('Location: view.php?id=' . $memberId . '&success=1');
    exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die('Unable to save family relationship: ' . htmlspecialchars($e->getMessage()));
}