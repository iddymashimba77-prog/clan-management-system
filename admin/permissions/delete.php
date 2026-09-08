<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$permissionId = (int)(
    $_GET['id'] ?? 0
);


if ($permissionId <= 0) {

    $_SESSION['error'] =
        'Invalid permission ID.';

    header('Location: index.php');

    exit;
}


$database = new Database();

$pdo = $database->connect();


/*
|--------------------------------------------------------------------------
| GET PERMISSION
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        permission_id,
        permission_name,
        permission_key
    FROM permissions
    WHERE permission_id = ?
    LIMIT 1
");

$stmt->execute([
    $permissionId
]);

$permission = $stmt->fetch(
    PDO::FETCH_ASSOC
);


if (!$permission) {

    $_SESSION['error'] =
        'Permission not found.';

    header('Location: index.php');

    exit;
}


try {

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | REMOVE ROLE-PERMISSION RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        DELETE FROM role_permissions
        WHERE permission_id = ?
    ");

    $stmt->execute([
        $permissionId
    ]);


    /*
    |--------------------------------------------------------------------------
    | DELETE PERMISSION
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        DELETE FROM permissions
        WHERE permission_id = ?
    ");

    $stmt->execute([
        $permissionId
    ]);


    $pdo->commit();


    $_SESSION['success'] =
        'Permission deleted successfully.';


    header('Location: index.php');

    exit;


} catch (PDOException $e) {

    if ($pdo->inTransaction()) {

        $pdo->rollBack();

    }


    error_log(
        'Permission deletion error: '
        . $e->getMessage()
    );


    $_SESSION['error'] =
        'Unable to delete permission.';


    header('Location: index.php');

    exit;
}