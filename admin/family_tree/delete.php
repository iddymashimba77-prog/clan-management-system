<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$pdo = $database->connect();

try {

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | Get spouse first
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT spouse_user_id
        FROM users
        WHERE user_id = ?
          AND is_deleted = 0
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $spouseId = $stmt->fetchColumn();


    /*
    |--------------------------------------------------------------------------
    | Remove member's relationships
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE users
        SET
            father_user_id = NULL,
            mother_user_id = NULL,
            spouse_user_id = NULL,
            updated_at = CURRENT_TIMESTAMP
        WHERE user_id = ?
          AND is_deleted = 0
    ");

    $stmt->execute([$id]);


    /*
    |--------------------------------------------------------------------------
    | Remove reciprocal spouse relationship
    |--------------------------------------------------------------------------
    */

    if ($spouseId) {

        $stmt = $pdo->prepare("
            UPDATE users
            SET
                spouse_user_id = NULL,
                updated_at = CURRENT_TIMESTAMP
            WHERE user_id = ?
              AND spouse_user_id = ?
        ");

        $stmt->execute([
            $spouseId,
            $id
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
        'DELETE_FAMILY_RELATIONSHIP',
        'users',
        $id,
        'Family relationships removed by administrator.',
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);


    $pdo->commit();

    header('Location: index.php?deleted=1');
    exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die(
        'Unable to remove family relationship: ' .
        htmlspecialchars($e->getMessage())
    );
}