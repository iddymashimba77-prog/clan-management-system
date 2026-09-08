<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';
require_once '../../config/language.php';
require_once '../../config/security.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    securityError('Invalid backup request.');
}

if (!verify_csrf()) {
    securityError('Invalid security token. Please try again.');
}

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    securityError('Session expired. Please login again.');
}


/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

$database = new Database();
$pdo = $database->connect();


/*
|--------------------------------------------------------------------------
| CHECK ADMINISTRATOR
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        u.user_id,
        u.password_hash,
        r.role_name
    FROM users u
    LEFT JOIN roles r
        ON r.role_id = u.role_id
    WHERE u.user_id = ?
      AND u.is_deleted = 0
    LIMIT 1
");

$stmt->execute([$user_id]);

$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (
    !$admin ||
    strtolower(trim($admin['role_name'] ?? '')) !== 'administrator'
) {
    securityError('Administrator access required.');
}


/*
|--------------------------------------------------------------------------
| VERIFY ADMIN PASSWORD
|--------------------------------------------------------------------------
*/

$adminPassword = $_POST['admin_password'] ?? '';

if (
    !verifyAdminPassword(
        $pdo,
        $user_id,
        $adminPassword
    )
) {
    securityError('Incorrect administrator password.');
}


/*
|--------------------------------------------------------------------------
| GET DATABASE INFORMATION FROM Database CLASS
|--------------------------------------------------------------------------
*/

$dbHost     = $database->getHost();
$dbName     = $database->getDatabaseName();
$dbUser     = $database->getUsername();
$dbPassword = $database->getPassword();


/*
|--------------------------------------------------------------------------
| MYSQLDUMP
|--------------------------------------------------------------------------
|
| XAMPP for Linux
|
*/

$dumpBinary = '/opt/lampp/bin/mysqldump';

if (!file_exists($dumpBinary)) {
    securityError(
        'mysqldump was not found at /opt/lampp/bin/mysqldump.'
    );
}


/*
|--------------------------------------------------------------------------
| BACKUP DIRECTORY
|--------------------------------------------------------------------------
*/

$backupDir = __DIR__ . '/backups';

if (!is_dir($backupDir)) {

    if (!mkdir($backupDir, 0750, true)) {
        securityError('Unable to create backup directory.');
    }
}


/*
|--------------------------------------------------------------------------
| CHECK DIRECTORY WRITABILITY
|--------------------------------------------------------------------------
*/

if (!is_writable($backupDir)) {
    securityError(
        'Backup directory is not writable.'
    );
}


/*
|--------------------------------------------------------------------------
| TEMP MYSQL CONFIG
|--------------------------------------------------------------------------
|
| We do NOT put the password directly into the shell command.
|
*/

$tempConfig = tempnam(
    sys_get_temp_dir(),
    'clan_mysql_'
);

if ($tempConfig === false) {
    securityError(
        'Unable to create temporary MySQL configuration.'
    );
}


$configContent =
    "[client]\n" .
    "host=" . $dbHost . "\n" .
    "user=" . $dbUser . "\n" .
    "password=" . $dbPassword . "\n";


if (
    file_put_contents(
        $tempConfig,
        $configContent
    ) === false
) {

    @unlink($tempConfig);

    securityError(
        'Unable to create temporary MySQL configuration.'
    );
}


chmod($tempConfig, 0600);


/*
|--------------------------------------------------------------------------
| BACKUP FILE NAME
|--------------------------------------------------------------------------
*/

$fileName =
    'clan_backup_' .
    date('Y-m-d_H-i-s') .
    '.sql';

$filePath =
    $backupDir .
    DIRECTORY_SEPARATOR .
    $fileName;


/*
|--------------------------------------------------------------------------
| MYSQLDUMP COMMAND
|--------------------------------------------------------------------------
*/

$command =
    escapeshellarg($dumpBinary) .
    ' --defaults-extra-file=' .
    escapeshellarg($tempConfig) .
    ' --single-transaction' .
    ' --routines' .
    ' --triggers' .
    ' --events' .
    ' ' .
    escapeshellarg($dbName) .
    ' > ' .
    escapeshellarg($filePath) .
    ' 2>&1';


$output = [];
$returnCode = 0;


/*
|--------------------------------------------------------------------------
| EXECUTE BACKUP
|--------------------------------------------------------------------------
*/

exec(
    $command,
    $output,
    $returnCode
);


/*
|--------------------------------------------------------------------------
| REMOVE TEMP CONFIG
|--------------------------------------------------------------------------
*/

@unlink($tempConfig);


/*
|--------------------------------------------------------------------------
| VALIDATE BACKUP
|--------------------------------------------------------------------------
*/

if (
    $returnCode !== 0 ||
    !file_exists($filePath) ||
    filesize($filePath) < 100
) {

    @unlink($filePath);

    securityError(
        'Database backup failed.'
    );
}


/*
|--------------------------------------------------------------------------
| ACTIVITY LOG
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("
        INSERT INTO activity_logs
        (
            user_id,
            action,
            table_name,
            record_id,
            description,
            ip_address
        )
        VALUES
        (
            ?,
            ?,
            ?,
            NULL,
            ?,
            ?
        )
    ");

    $stmt->execute([
        $user_id,
        'DATABASE_BACKUP',
        'clan_settings',
        'Database backup created: ' . $fileName,
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);

} catch (Throwable $e) {

    /*
     * Do not cancel a successful backup
     * because activity logging failed.
     */
}


/*
|--------------------------------------------------------------------------
| DOWNLOAD
|--------------------------------------------------------------------------
*/

header('Content-Type: application/sql');

header(
    'Content-Disposition: attachment; filename="' .
    basename($fileName) .
    '"'
);

header(
    'Content-Length: ' .
    filesize($filePath)
);

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

readfile($filePath);


/*
|--------------------------------------------------------------------------
| DELETE TEMPORARY BACKUP AFTER DOWNLOAD
|--------------------------------------------------------------------------
*/

@unlink($filePath);

exit;