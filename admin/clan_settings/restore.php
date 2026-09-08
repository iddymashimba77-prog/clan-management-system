<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';
require_once '../../config/language.php';
require_once '../../config/security.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    securityError('Invalid restore request.');
}

if (!verify_csrf()) {
    securityError(
        'Invalid security token. Please try again.'
    );
}

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    securityError(
        'Session expired. Please login again.'
    );
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
    securityError(
        'Administrator access required.'
    );
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
    securityError(
        'Incorrect administrator password.'
    );
}


/*
|--------------------------------------------------------------------------
| RESTORE CONFIRMATION
|--------------------------------------------------------------------------
*/

$confirmation =
    trim(
        $_POST['restore_confirmation'] ?? ''
    );


if ($confirmation !== 'RESTORE') {

    securityError(
        'You must type RESTORE to confirm database restoration.'
    );
}


/*
|--------------------------------------------------------------------------
| CHECK UPLOADED FILE
|--------------------------------------------------------------------------
*/

if (
    !isset($_FILES['backup_file'])
) {

    securityError(
        'Please select a backup file.'
    );
}


$file = $_FILES['backup_file'];


if (
    $file['error'] !== UPLOAD_ERR_OK
) {

    securityError(
        'Backup file upload failed.'
    );
}


/*
|--------------------------------------------------------------------------
| FILE SIZE LIMIT
|--------------------------------------------------------------------------
|
| Maximum: 100 MB
|
*/

$maxSize = 100 * 1024 * 1024;

if (
    $file['size'] <= 0 ||
    $file['size'] > $maxSize
) {

    securityError(
        'Backup file is empty or exceeds the 100 MB limit.'
    );
}


/*
|--------------------------------------------------------------------------
| VERIFY UPLOADED FILE
|--------------------------------------------------------------------------
*/

if (!is_uploaded_file($file['tmp_name'])) {

    securityError(
        'Invalid uploaded backup file.'
    );
}


/*
|--------------------------------------------------------------------------
| FILE NAME
|--------------------------------------------------------------------------
*/

$originalName =
    basename(
        $file['name']
    );

$lowerName =
    strtolower($originalName);


/*
|--------------------------------------------------------------------------
| ALLOWED EXTENSIONS
|--------------------------------------------------------------------------
*/

$isSql =
    str_ends_with(
        $lowerName,
        '.sql'
    );

$isGzip =
    str_ends_with(
        $lowerName,
        '.sql.gz'
    );


if (!$isSql && !$isGzip) {

    securityError(
        'Only .sql and .sql.gz backup files are allowed.'
    );
}


/*
|--------------------------------------------------------------------------
| RESTORE FILE
|--------------------------------------------------------------------------
*/

$restoreFile =
    $file['tmp_name'];

$temporaryRestoreFile = false;


/*
|--------------------------------------------------------------------------
| VALIDATE / EXTRACT GZIP
|--------------------------------------------------------------------------
*/

if ($isGzip) {

    $gz = @gzopen(
        $file['tmp_name'],
        'rb'
    );

    if (!$gz) {

        securityError(
            'The GZIP backup file is corrupted or invalid.'
        );
    }


    $restoreFile =
        tempnam(
            sys_get_temp_dir(),
            'clan_restore_'
        );


    if ($restoreFile === false) {

        gzclose($gz);

        securityError(
            'Unable to create temporary restore file.'
        );
    }


    $output =
        fopen(
            $restoreFile,
            'wb'
        );


    if (!$output) {

        gzclose($gz);

        @unlink($restoreFile);

        securityError(
            'Unable to extract backup file.'
        );
    }


    while (!gzeof($gz)) {

        $chunk =
            gzread(
                $gz,
                8192
            );

        if ($chunk === false) {

            gzclose($gz);
            fclose($output);

            @unlink($restoreFile);

            securityError(
                'Failed while reading GZIP backup.'
            );
        }


        fwrite(
            $output,
            $chunk
        );
    }


    gzclose($gz);
    fclose($output);

    $temporaryRestoreFile = true;
}


/*
|--------------------------------------------------------------------------
| CHECK EXTRACTED FILE SIZE
|--------------------------------------------------------------------------
*/

if (
    !file_exists($restoreFile) ||
    filesize($restoreFile) < 100
) {

    if ($temporaryRestoreFile) {
        @unlink($restoreFile);
    }

    securityError(
        'The backup file is empty or invalid.'
    );
}


/*
|--------------------------------------------------------------------------
| BASIC SQL VALIDATION
|--------------------------------------------------------------------------
|
| We check that the file actually looks like
| a database dump before executing it.
|
*/

$handle =
    fopen(
        $restoreFile,
        'rb'
    );


if (!$handle) {

    if ($temporaryRestoreFile) {
        @unlink($restoreFile);
    }

    securityError(
        'Unable to read backup file.'
    );
}


$sqlSample = '';

while (
    !feof($handle) &&
    strlen($sqlSample) < 500000
) {

    $chunk =
        fread(
            $handle,
            8192
        );

    if ($chunk === false) {
        break;
    }

    $sqlSample .= $chunk;
}

fclose($handle);


/*
|--------------------------------------------------------------------------
| SQL CONTENT CHECK
|--------------------------------------------------------------------------
*/

$hasSqlStructure =
    stripos($sqlSample, 'CREATE TABLE') !== false ||
    stripos($sqlSample, 'INSERT INTO') !== false ||
    stripos($sqlSample, 'CREATE DATABASE') !== false ||
    stripos($sqlSample, 'DROP TABLE') !== false;


if (!$hasSqlStructure) {

    if ($temporaryRestoreFile) {
        @unlink($restoreFile);
    }

    securityError(
        'The selected file does not appear to be a valid SQL database backup.'
    );
}


/*
|--------------------------------------------------------------------------
| DATABASE INFORMATION
|--------------------------------------------------------------------------
*/

$dbHost =
    $database->getHost();

$dbName =
    $database->getDatabaseName();

$dbUser =
    $database->getUsername();

$dbPassword =
    $database->getPassword();


/*
|--------------------------------------------------------------------------
| MYSQL BINARIES
|--------------------------------------------------------------------------
*/

$mysqlBinary =
    '/opt/lampp/bin/mysql';

$dumpBinary =
    '/opt/lampp/bin/mysqldump';


if (!file_exists($mysqlBinary)) {

    if ($temporaryRestoreFile) {
        @unlink($restoreFile);
    }

    securityError(
        'MySQL client was not found at /opt/lampp/bin/mysql.'
    );
}


if (!file_exists($dumpBinary)) {

    if ($temporaryRestoreFile) {
        @unlink($restoreFile);
    }

    securityError(
        'mysqldump was not found at /opt/lampp/bin/mysqldump.'
    );
}


/*
|--------------------------------------------------------------------------
| TEMP MYSQL CONFIG
|--------------------------------------------------------------------------
*/

$tempConfig =
    tempnam(
        sys_get_temp_dir(),
        'clan_mysql_'
    );


if ($tempConfig === false) {

    if ($temporaryRestoreFile) {
        @unlink($restoreFile);
    }

    securityError(
        'Unable to create temporary MySQL configuration.'
    );
}


$configContent =
    "[client]\n" .
    "host=" . $dbHost . "\n" .
    "user=" . $dbUser . "\n" .
    "password=" . $dbPassword . "\n";


file_put_contents(
    $tempConfig,
    $configContent
);

chmod(
    $tempConfig,
    0600
);


/*
|--------------------------------------------------------------------------
| SAFETY BACKUP
|--------------------------------------------------------------------------
|
| VERY IMPORTANT:
|
| Before replacing the current database,
| create a temporary safety backup.
|
*/

$safetyFile =
    sys_get_temp_dir() .
    DIRECTORY_SEPARATOR .
    'clan_safety_backup_' .
    date('Y-m-d_H-i-s') .
    '.sql';


$safetyCommand =
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
    escapeshellarg($safetyFile) .
    ' 2>&1';


$safetyOutput = [];
$safetyCode = 0;


exec(
    $safetyCommand,
    $safetyOutput,
    $safetyCode
);


/*
|--------------------------------------------------------------------------
| SAFETY BACKUP VALIDATION
|--------------------------------------------------------------------------
*/

if (
    $safetyCode !== 0 ||
    !file_exists($safetyFile) ||
    filesize($safetyFile) < 100
) {

    @unlink($tempConfig);

    if ($temporaryRestoreFile) {
        @unlink($restoreFile);
    }

    securityError(
        'Safety backup failed. Database restoration was cancelled.'
    );
}


/*
|--------------------------------------------------------------------------
| RESTORE DATABASE
|--------------------------------------------------------------------------
*/

$restoreCommand =
    escapeshellarg($mysqlBinary) .
    ' --defaults-extra-file=' .
    escapeshellarg($tempConfig) .
    ' ' .
    escapeshellarg($dbName) .
    ' < ' .
    escapeshellarg($restoreFile) .
    ' 2>&1';


$restoreOutput = [];
$restoreCode = 0;


exec(
    $restoreCommand,
    $restoreOutput,
    $restoreCode
);


/*
|--------------------------------------------------------------------------
| CLEAN TEMP CONFIG
|--------------------------------------------------------------------------
*/

@unlink($tempConfig);


/*
|--------------------------------------------------------------------------
| CLEAN EXTRACTED FILE
|--------------------------------------------------------------------------
*/

if ($temporaryRestoreFile) {
    @unlink($restoreFile);
}


/*
|--------------------------------------------------------------------------
| RESTORE FAILED
|--------------------------------------------------------------------------
*/

if ($restoreCode !== 0) {

    /*
     * IMPORTANT:
     *
     * We intentionally keep $safetyFile
     * so the administrator can recover
     * the previous database manually.
     */

    securityError(
        'Database restore failed. A safety backup was created at: ' .
        $safetyFile
    );
}


/*
|--------------------------------------------------------------------------
| RESTORE SUCCESS
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
        'DATABASE_RESTORE',
        'clan_settings',
        'Database restored from backup: ' . $originalName,
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);

} catch (Throwable $e) {

    /*
     * Database restore already succeeded.
     * Do not report restore as failed just
     * because logging failed.
     */
}


/*
|--------------------------------------------------------------------------
| REMOVE SAFETY BACKUP AFTER SUCCESS
|--------------------------------------------------------------------------
|
| If you want extra protection, you can keep
| this safety backup instead of deleting it.
|
*/

@unlink($safetyFile);


/*
|--------------------------------------------------------------------------
| SUCCESS MESSAGE
|--------------------------------------------------------------------------
*/

securitySuccess(
    'Database restored successfully.'
);


/*
|--------------------------------------------------------------------------
| REDIRECT
|--------------------------------------------------------------------------
*/

header(
    'Location: index.php'
);

exit;