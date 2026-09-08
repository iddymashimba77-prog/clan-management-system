<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$database = new Database();
$pdo = $database->connect();


/* Check type */

$stmt = $pdo->prepare("
    SELECT type_name
    FROM contribution_types
    WHERE contribution_type_id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$type = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$type) {

    $_SESSION['error'] = 'Contribution type not found.';

    header('Location: index.php');
    exit;
}


/*
 * Because contributions.contribution_type_id
 * uses ON DELETE SET NULL, existing contributions
 * will NOT be deleted.
 */

$stmt = $pdo->prepare("
    DELETE FROM contribution_types
    WHERE contribution_type_id = ?
");

$stmt->execute([$id]);


$_SESSION['success'] =
    'Contribution type deleted successfully. Existing contributions were preserved.';

header('Location: index.php');
exit;