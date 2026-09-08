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
        'Invalid expense ID.';

    header('Location: index.php');

    exit;
}

try {

    $database = new Database();

    $pdo = $database->connect();

    $stmt = $pdo->prepare("
        DELETE FROM expenses
        WHERE expense_id = ?
    ");

    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {

        $_SESSION['success'] =
            'Expense deleted successfully.';

    } else {

        $_SESSION['error'] =
            'Expense record not found.';
    }

} catch (PDOException $e) {

    error_log(
        'Expense Delete Error: ' .
        $e->getMessage()
    );

    $_SESSION['error'] =
        'Failed to delete expense.';
}

header('Location: index.php');

exit;