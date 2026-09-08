<?php

require_once '../../../config/database.php';
require_once '../../../config/constants.php';
require_once '../../../config/auth.php';

require_once '../../../config/language.php';
requireLogin();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {

    $_SESSION['error'] =
        'Invalid income ID.';

    header('Location: index.php');

    exit;
}

try {

    $database = new Database();

    $pdo = $database->connect();

    $stmt = $pdo->prepare("
        DELETE FROM income
        WHERE income_id = ?
    ");

    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {

        $_SESSION['success'] =
            'Income deleted successfully.';

    } else {

        $_SESSION['error'] =
            'Income record not found.';
    }

} catch (PDOException $e) {

    error_log(
        'Income Delete Error: ' .
        $e->getMessage()
    );

    $_SESSION['error'] =
        'Failed to delete income.';
}

header('Location: index.php');

exit;