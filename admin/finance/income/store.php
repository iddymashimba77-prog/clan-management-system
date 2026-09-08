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

    $_SESSION['error'] = 'Invalid security token.';

    header('Location: create.php');

    exit;
}

$incomeTitle = trim($_POST['income_title'] ?? '');

$incomeCategory = trim(
    $_POST['income_category'] ?? ''
);

$amount = (float)($_POST['amount'] ?? 0);

$incomeDate = $_POST['income_date'] ?? '';

$receivedFrom = trim(
    $_POST['received_from'] ?? ''
);

$referenceNumber = trim(
    $_POST['reference_number'] ?? ''
);

$paymentMethod = trim(
    $_POST['payment_method'] ?? ''
);

$description = trim(
    $_POST['description'] ?? ''
);

if (
    $incomeTitle === '' ||
    $incomeCategory === '' ||
    $amount <= 0 ||
    $incomeDate === ''
) {

    $_SESSION['error'] =
        'Please fill all required fields correctly.';

    header('Location: create.php');

    exit;
}

if (
    !preg_match(
        '/^\d{4}-\d{2}-\d{2}$/',
        $incomeDate
    )
) {

    $_SESSION['error'] = 'Invalid income date.';

    header('Location: create.php');

    exit;
}

try {

    $database = new Database();

    $pdo = $database->connect();

    $createdBy = $_SESSION['user_id'] ?? null;

    $stmt = $pdo->prepare("
        INSERT INTO income (
            income_title,
            income_category,
            amount,
            income_date,
            description,
            reference_number,
            received_from,
            payment_method,
            created_by_user_id
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $incomeTitle,
        $incomeCategory,
        $amount,
        $incomeDate,
        $description !== '' ? $description : null,
        $referenceNumber !== '' ? $referenceNumber : null,
        $receivedFrom !== '' ? $receivedFrom : null,
        $paymentMethod !== '' ? $paymentMethod : null,
        $createdBy
    ]);

    $_SESSION['success'] =
        'Income record added successfully.';

    header('Location: index.php');

    exit;

} catch (PDOException $e) {

    error_log(
        'Income Store Error: ' . $e->getMessage()
    );

    $_SESSION['error'] =
        'Failed to save income record.';

    header('Location: create.php');

    exit;
}