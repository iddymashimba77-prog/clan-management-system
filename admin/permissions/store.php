<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| ONLY POST REQUEST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: index.php');

    exit;
}


/*
|--------------------------------------------------------------------------
| FORM DATA
|--------------------------------------------------------------------------
*/

$permissionId = (int)(
    $_POST['permission_id'] ?? 0
);

$permissionName = trim(
    $_POST['permission_name'] ?? ''
);

$permissionKey = trim(
    $_POST['permission_key'] ?? ''
);

$description = trim(
    $_POST['description'] ?? ''
);

$module = trim(
    $_POST['module'] ?? ''
);

$status = $_POST['status'] ?? 'active';


/*
|--------------------------------------------------------------------------
| BASIC VALIDATION
|--------------------------------------------------------------------------
*/

if ($permissionName === '') {

    $_SESSION['error'] =
        'Permission name is required.';

    header(
        'Location: '
        . ($permissionId > 0
            ? 'edit.php?id=' . $permissionId
            : 'create.php')
    );

    exit;
}


if ($permissionKey === '') {

    $_SESSION['error'] =
        'Permission key is required.';

    header(
        'Location: '
        . ($permissionId > 0
            ? 'edit.php?id=' . $permissionId
            : 'create.php')
    );

    exit;
}


if ($module === '') {

    $_SESSION['error'] =
        'Module is required.';

    header(
        'Location: '
        . ($permissionId > 0
            ? 'edit.php?id=' . $permissionId
            : 'create.php')
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| VALIDATE PERMISSION KEY
|--------------------------------------------------------------------------
*/

if (!preg_match('/^[a-z0-9_]+$/', $permissionKey)) {

    $_SESSION['error'] =
        'Permission key can only contain lowercase letters, numbers and underscores.';

    header(
        'Location: '
        . ($permissionId > 0
            ? 'edit.php?id=' . $permissionId
            : 'create.php')
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| VALIDATE STATUS
|--------------------------------------------------------------------------
*/

if (!in_array(
    $status,
    ['active', 'inactive'],
    true
)) {

    $status = 'active';
}


/*
|--------------------------------------------------------------------------
| DATABASE
|--------------------------------------------------------------------------
*/

$database = new Database();

$pdo = $database->connect();


/*
|--------------------------------------------------------------------------
| CHECK PERMISSION EXISTS WHEN EDITING
|--------------------------------------------------------------------------
*/

if ($permissionId > 0) {

    $stmt = $pdo->prepare("
        SELECT permission_id
        FROM permissions
        WHERE permission_id = ?
        LIMIT 1
    ");

    $stmt->execute([
        $permissionId
    ]);


    if (!$stmt->fetch()) {

        $_SESSION['error'] =
            'Permission not found.';

        header('Location: index.php');

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| CHECK DUPLICATE PERMISSION KEY
|--------------------------------------------------------------------------
*/

if ($permissionId > 0) {

    $stmt = $pdo->prepare("
        SELECT permission_id
        FROM permissions
        WHERE permission_key = ?
          AND permission_id != ?
        LIMIT 1
    ");

    $stmt->execute([
        $permissionKey,
        $permissionId
    ]);

} else {

    $stmt = $pdo->prepare("
        SELECT permission_id
        FROM permissions
        WHERE permission_key = ?
        LIMIT 1
    ");

    $stmt->execute([
        $permissionKey
    ]);
}


if ($stmt->fetch()) {

    $_SESSION['error'] =
        'A permission with this key already exists.';

    header(
        'Location: '
        . ($permissionId > 0
            ? 'edit.php?id=' . $permissionId
            : 'create.php')
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| CHECK DUPLICATE PERMISSION NAME
|--------------------------------------------------------------------------
*/

if ($permissionId > 0) {

    $stmt = $pdo->prepare("
        SELECT permission_id
        FROM permissions
        WHERE permission_name = ?
          AND permission_id != ?
        LIMIT 1
    ");

    $stmt->execute([
        $permissionName,
        $permissionId
    ]);

} else {

    $stmt = $pdo->prepare("
        SELECT permission_id
        FROM permissions
        WHERE permission_name = ?
        LIMIT 1
    ");

    $stmt->execute([
        $permissionName
    ]);
}


if ($stmt->fetch()) {

    $_SESSION['error'] =
        'A permission with this name already exists.';

    header(
        'Location: '
        . ($permissionId > 0
            ? 'edit.php?id=' . $permissionId
            : 'create.php')
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| INSERT OR UPDATE
|--------------------------------------------------------------------------
*/

try {

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    if ($permissionId > 0) {

        $stmt = $pdo->prepare("
            UPDATE permissions

            SET
                permission_name = ?,
                permission_key = ?,
                description = ?,
                module = ?,
                status = ?,
                updated_at = CURRENT_TIMESTAMP

            WHERE permission_id = ?
        ");


        $stmt->execute([

            $permissionName,

            $permissionKey,

            $description !== ''
                ? $description
                : null,

            $module,

            $status,

            $permissionId

        ]);


        $_SESSION['success'] =
            'Permission updated successfully.';


    /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

    } else {

        $stmt = $pdo->prepare("
            INSERT INTO permissions
            (
                permission_name,
                permission_key,
                description,
                module,
                status
            )

            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?
            )
        ");


        $stmt->execute([

            $permissionName,

            $permissionKey,

            $description !== ''
                ? $description
                : null,

            $module,

            $status

        ]);


        $_SESSION['success'] =
            'Permission created successfully.';
    }


    header('Location: index.php');

    exit;


} catch (PDOException $e) {

    error_log(
        'Permission save error: '
        . $e->getMessage()
    );


    $_SESSION['error'] =
        'Failed to save permission. Please try again.';


    header(
        'Location: '
        . ($permissionId > 0
            ? 'edit.php?id=' . $permissionId
            : 'create.php')
    );

    exit;
}