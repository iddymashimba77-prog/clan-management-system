<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

$database = new Database();
$pdo = $database->connect();


/*
|--------------------------------------------------------------------------
| FIND LOGO COLUMN
|--------------------------------------------------------------------------
*/

$columns = $pdo
    ->query("SHOW COLUMNS FROM clan_settings")
    ->fetchAll(PDO::FETCH_ASSOC);

$logoColumn = null;

foreach ($columns as $column) {

    if (
        stripos($column['Field'], 'logo') !== false
    ) {

        $logoColumn = $column['Field'];
        break;
    }
}

if (!$logoColumn) {

    die('Logo column not found.');
}


/*
|--------------------------------------------------------------------------
| GET CURRENT LOGO
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT `$logoColumn`
    FROM clan_settings
    LIMIT 1
");

$logo = $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| DELETE DATABASE VALUE
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    UPDATE clan_settings
    SET `$logoColumn` = NULL
    LIMIT 1
");

$stmt->execute();


/*
|--------------------------------------------------------------------------
| DELETE FILE
|--------------------------------------------------------------------------
*/

if (!empty($logo)) {

    $path =
        '../../uploads/clan/' .
        basename($logo);

    if (is_file($path)) {
        unlink($path);
    }
}


/*
|--------------------------------------------------------------------------
| LOG
|--------------------------------------------------------------------------
*/

$log = $pdo->prepare("
    INSERT INTO activity_logs
    (
        user_id,
        action,
        table_name,
        description,
        ip_address
    )
    VALUES (?, ?, ?, ?, ?)
");

$log->execute([
    $_SESSION['user_id'] ?? null,
    'DELETE_CLAN_LOGO',
    'clan_settings',
    'Administrator deleted clan logo.',
    $_SERVER['REMOTE_ADDR'] ?? null
]);


header('Location: index.php?success=logo_deleted');
exit;