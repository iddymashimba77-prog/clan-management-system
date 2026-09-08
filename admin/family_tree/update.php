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
    header('Location: index.php');
    exit;
}


if ($fatherId === $memberId) {
    die('A member cannot be their own father.');
}

if ($motherId === $memberId) {
    die('A member cannot be their own mother.');
}

if ($spouseId === $memberId) {
    die('A member cannot be their own spouse.');
}


$pdo = $database->connect();

try {

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | Remove old spouse reciprocal link
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT spouse_user_id
        FROM users
        WHERE user_id = ?
        LIMIT 1
    ");

    $stmt->execute([$memberId]);

    $oldSpouseId = $stmt->fetchColumn();


    if (
        $oldSpouseId &&
        (!$spouseId || (int)$oldSpouseId !== $spouseId)
    ) {

        $stmt = $pdo->prepare("
            UPDATE users
            SET spouse_user_id = NULL
            WHERE user_id = ?
              AND spouse_user_id = ?
        ");

        $stmt->execute([
            $oldSpouseId,
            $memberId
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Update member
    |--------------------------------------------------------------------------
    */

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
    |--------------------------------------------------------------------------
    | Synchronize spouse
    |--------------------------------------------------------------------------
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
    |--------------------------------------------------------------------------
    | Activity log
    |--------------------------------------------------------------------------
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
        'UPDATE_FAMILY_RELATIONSHIP',
        'users',
        $memberId,
        'Family relationship updated by administrator.',
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);


    $pdo->commit();

    header('Location: view.php?id=' . $memberId . '&updated=1');
    exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die(
        'Unable to update family relationship: ' .
        htmlspecialchars($e->getMessage())
    );
}