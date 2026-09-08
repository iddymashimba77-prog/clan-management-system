<?php

require_once '../../../config/database.php';
require_once '../../../config/constants.php';
require_once '../../../config/auth.php';

require_once '../../../config/language.php';
requireLogin();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: index.php');

    exit;
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals(
        $_SESSION['csrf_token'],
        $_POST['csrf_token']
    )
) {

    $_SESSION['error'] =
        'Invalid security token.';

    header('Location: index.php');

    exit;
}

$id = (int)(
    $_POST['expense_id'] ?? 0
);

$title = trim(
    $_POST['expense_title'] ?? ''
);

$category = trim(
    $_POST['expense_category'] ?? ''
);

$amount = (float)(
    $_POST['amount'] ?? 0
);

$date = $_POST['expense_date'] ?? '';

$paidTo = trim(
    $_POST['paid_to'] ?? ''
);

$reference = trim(
    $_POST['reference_number'] ?? ''
);

$paymentMethod = trim(
    $_POST['payment_method'] ?? ''
);

$description = trim(
    $_POST['description'] ?? ''
);

if (
    $id <= 0 ||
    $title === '' ||
    $category === '' ||
    $amount <= 0 ||
    $date === ''
) {

    $_SESSION['error'] =
        'Please provide valid information.';

    header(
        'Location: edit.php?id=' . $id
    );

    exit;
}

try {

    $database = new Database();

    $pdo = $database->connect();

    $stmt = $pdo->prepare("
        UPDATE expenses
        SET
            expense_title = ?,
            expense_category = ?,
            amount = ?,
            expense_date = ?,
            description = ?,
            reference_number = ?,
            paid_to = ?,
            payment_method = ?
        WHERE expense_id = ?
    ");

    $stmt->execute([
        $title,
        $category,
        $amount,
        $date,
        $description !== '' ? $description : null,
        $reference !== '' ? $reference : null,
        $paidTo !== '' ? $paidTo : null,
        $paymentMethod !== '' ? $paymentMethod : null,
        $id
    ]);

    $_SESSION['success'] =
        'Expense updated successfully.';

    header('Location: index.php');

    exit;

} catch (PDOException $e) {

    error_log(
        'Expense Update Error: ' .
        $e->getMessage()
    );

    $_SESSION['error'] =
        'Failed to update expense.';

    header(
        'Location: edit.php?id=' . $id
    );

    exit;
}