<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: generations.php');
    exit;
}

$pdo = $database->connect();

$generation_name = trim($_POST['generation_name'] ?? '');
$description = trim($_POST['description'] ?? '');

if ($generation_name === '') {
    header('Location: generations.php?error=name_required');
    exit;
}

try {

    $check = $pdo->prepare("
        SELECT generation_id
        FROM generations
        WHERE generation_name = ?
        LIMIT 1
    ");

    $check->execute([$generation_name]);

    if ($check->fetch()) {
        header('Location: generations.php?error=already_exists');
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO generations
        (
            generation_name,
            description,
            created_by_user_id
        )
        VALUES (?, ?, ?)
    ");

    $stmt->execute([
        $generation_name,
        $description !== '' ? $description : null,
        $_SESSION['user_id'] ?? null
    ]);

    $generationId = $pdo->lastInsertId();

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
        'CREATE_GENERATION',
        'generations',
        $generationId,
        'New family generation created: ' . $generation_name,
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);

    header('Location: generations.php?success=created');
    exit;

} catch (PDOException $e) {

    header('Location: generations.php?error=save_failed');
    exit;
}