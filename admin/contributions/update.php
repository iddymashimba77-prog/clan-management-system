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

$database = new Database();
$pdo = $database->connect();

$contribution_id = (int)($_POST['contribution_id'] ?? 0);

if ($contribution_id <= 0) {
    header('Location: index.php?error=Invalid contribution');
    exit;
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$target_amount = (float)($_POST['target_amount'] ?? 0);
$allow_partial_payment = isset($_POST['allow_partial_payment']) ? 1 : 0;
$minimum_amount = (float)($_POST['minimum_amount'] ?? 0);
$start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
$end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
$status = $_POST['status'] ?? 'active';

if ($title === '' || $target_amount <= 0) {
    header('Location: edit.php?id=' . $contribution_id . '&error=Please fill required fields');
    exit;
}

if (!in_array($status, ['active', 'closed'], true)) {
    $status = 'active';
}

try {

    $stmt = $pdo->prepare("
        UPDATE contributions
        SET
            title = ?,
            description = ?,
            target_amount = ?,
            allow_partial_payment = ?,
            minimum_amount = ?,
            start_date = ?,
            end_date = ?,
            status = ?
        WHERE contribution_id = ?
    ");

    $stmt->execute([
        $title,
        $description,
        $target_amount,
        $allow_partial_payment,
        $minimum_amount,
        $start_date,
        $end_date,
        $status,
        $contribution_id
    ]);

    if (function_exists('logActivity')) {
        logActivity(
            $_SESSION['user_id'] ?? null,
            'Updated contribution',
            'contributions',
            $contribution_id,
            'Contribution updated by administrator'
        );
    }

    header(
        'Location: view.php?id=' .
        $contribution_id .
        '&success=Contribution updated successfully'
    );
    exit;

} catch (PDOException $e) {

    header(
        'Location: edit.php?id=' .
        $contribution_id .
        '&error=' .
        urlencode($e->getMessage())
    );
    exit;
}