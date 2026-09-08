<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    header('Location: index.php?error=invalid_id');
    exit;
}

$log_id = (int) $_GET['id'];

$database = new Database();
$pdo = $database->connect();

try {

    $stmt = $pdo->prepare("
        SELECT *
        FROM activity_logs
        WHERE log_id = ?
        LIMIT 1
    ");

    $stmt->execute([$log_id]);

    $log = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$log) {

        header('Location: index.php?error=log_not_found');
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        DELETE FROM activity_logs
        WHERE log_id = ?
    ");

    $stmt->execute([$log_id]);


    /*
    |--------------------------------------------------------------------------
    | NOTE:
    | Do NOT insert another activity log here.
    | Otherwise deleting an activity log would create another log.
    |--------------------------------------------------------------------------
    */


    header('Location: index.php?success=deleted');
    exit;

} catch (Throwable $e) {

    header(
        'Location: index.php?error=' .
        urlencode($e->getMessage())
    );

    exit;
}