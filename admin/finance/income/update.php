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

$id = (int)($_POST['income_id'] ?? 0);

$title = trim(
    $_POST['income_title'] ?? ''
);

$category = trim(
    $_POST['income_category'] ?? ''
);

$amount = (float)(
    $_POST['amount'] ?? 0
);

$date = $_POST['income_date'] ?? '';

$receivedFrom = trim(
    $_POST['received_from'] ?? ''
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
        UPDATE income
        SET
            income_title = ?,
            income_category = ?,
            amount = ?,
            income_date = ?,
            description = ?,
            reference_number = ?,
            received_from = ?,
            payment_method = ?
        WHERE income_id = ?
    ");

    $stmt->execute([
        $title,
        $category,
        $amount,
        $date,
        $description !== '' ? $description : null,
        $reference !== '' ? $reference : null,
        $receivedFrom !== '' ? $receivedFrom : null,
        $paymentMethod !== '' ? $paymentMethod : null,
        $id
    ]);

    $_SESSION['success'] =
        'Income updated successfully.';

    header('Location: index.php');

    exit;

} catch (PDOException $e) {

    error_log(
        'Income Update Error: ' .
        $e->getMessage()
    );

    $_SESSION['error'] =
        'Failed to update income.';

    header(
        'Location: edit.php?id=' . $id
    );

    exit;
}