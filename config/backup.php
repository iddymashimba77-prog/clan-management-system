<?php

/**
 * =========================================================
 * CLAN MANAGEMENT SYSTEM
 * BACKUP / RESTORE CONFIGURATION
 * =========================================================
 */

if (!defined('CLAN_BACKUP_CONFIG_LOADED')) {
    define('CLAN_BACKUP_CONFIG_LOADED', true);
}


/*
|--------------------------------------------------------------------------
| Database configuration
|--------------------------------------------------------------------------
*/

function getBackupDatabaseConfig(PDO $pdo): array
{
    $config = $pdo->query("
        SELECT
            DATABASE() AS database_name
    ")->fetch(PDO::FETCH_ASSOC);

    if (!$config || empty($config['database_name'])) {
        throw new RuntimeException(
            'Unable to determine database name.'
        );
    }

    $databaseName = $config['database_name'];

    /*
    |--------------------------------------------------------------------------
    | Read connection information from PDO when possible.
    |--------------------------------------------------------------------------
    */

    return [
        'database' => $databaseName
    ];
}


/*
|--------------------------------------------------------------------------
| Backup directory
|--------------------------------------------------------------------------
*/

function getBackupDirectory(): string
{
    $directory = dirname(__DIR__) . '/storage/backups';

    if (!is_dir($directory)) {

        if (!mkdir($directory, 0775, true)) {

            throw new RuntimeException(
                'Unable to create backup directory.'
            );
        }
    }

    if (!is_writable($directory)) {

        throw new RuntimeException(
            'Backup directory is not writable.'
        );
    }

    return realpath($directory);
}


/*
|--------------------------------------------------------------------------
| Find mysqldump
|--------------------------------------------------------------------------
*/

function getMysqldumpPath(): string
{
    $possiblePaths = [

        '/opt/lampp/bin/mysqldump',

        '/usr/bin/mysqldump',

        '/usr/local/bin/mysqldump'

    ];

    foreach ($possiblePaths as $path) {

        if (is_file($path) && is_executable($path)) {

            return $path;
        }
    }

    throw new RuntimeException(
        'mysqldump was not found on this server.'
    );
}


/*
|--------------------------------------------------------------------------
| Find mysql client
|--------------------------------------------------------------------------
*/

function getMysqlPath(): string
{
    $possiblePaths = [

        '/opt/lampp/bin/mysql',

        '/usr/bin/mysql',

        '/usr/local/bin/mysql'

    ];

    foreach ($possiblePaths as $path) {

        if (is_file($path) && is_executable($path)) {

            return $path;
        }
    }

    throw new RuntimeException(
        'mysql client was not found on this server.'
    );
}


/*
|--------------------------------------------------------------------------
| Create database backup
|--------------------------------------------------------------------------
*/

function createDatabaseBackup(PDO $pdo): array
{
    $config = getBackupDatabaseConfig($pdo);

    $backupDirectory = getBackupDirectory();

    $mysqldump = getMysqldumpPath();

    $databaseName = $config['database'];

    /*
    |--------------------------------------------------------------------------
    | Generate unique filename
    |--------------------------------------------------------------------------
    */

    $filename =
        'clan_backup_' .
        date('Y-m-d_H-i-s') .
        '_' .
        bin2hex(random_bytes(4)) .
        '.sql';

    $filePath =
        $backupDirectory .
        DIRECTORY_SEPARATOR .
        $filename;


    /*
    |--------------------------------------------------------------------------
    | Get database credentials from application config
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | The current Database class should provide these values.
    |
    */

    $host = defined('DB_HOST')
        ? DB_HOST
        : '127.0.0.1';

    $username = defined('DB_USER')
        ? DB_USER
        : 'root';

    $password = defined('DB_PASS')
        ? DB_PASS
        : '';

    $port = defined('DB_PORT')
        ? DB_PORT
        : '3306';


    /*
    |--------------------------------------------------------------------------
    | Build command
    |--------------------------------------------------------------------------
    */

    $command =
        escapeshellcmd($mysqldump) .
        ' --host=' . escapeshellarg($host) .
        ' --port=' . escapeshellarg($port) .
        ' --user=' . escapeshellarg($username);


    /*
    |--------------------------------------------------------------------------
    | Password
    |--------------------------------------------------------------------------
    */

    if ($password !== '') {

        $command .=
            ' --password=' .
            escapeshellarg($password);
    }


    $command .=
        ' --single-transaction' .
        ' --routines' .
        ' --triggers' .
        ' --events' .
        ' --add-drop-table' .
        ' ' .
        escapeshellarg($databaseName) .
        ' > ' .
        escapeshellarg($filePath) .
        ' 2>&1';


    /*
    |--------------------------------------------------------------------------
    | Execute
    |--------------------------------------------------------------------------
    */

    $output = [];

    $exitCode = 0;

    exec(
        $command,
        $output,
        $exitCode
    );


    /*
    |--------------------------------------------------------------------------
    | Validate
    |--------------------------------------------------------------------------
    */

    if (
        $exitCode !== 0 ||
        !is_file($filePath) ||
        filesize($filePath) === 0
    ) {

        if (is_file($filePath)) {
            @unlink($filePath);
        }

        throw new RuntimeException(
            'Unable to create backup file. ' .
            implode("\n", $output)
        );
    }


    return [

        'filename' => $filename,

        'path' => $filePath,

        'size' => filesize($filePath),

        'created_at' => date('Y-m-d H:i:s')

    ];
}


/*
|--------------------------------------------------------------------------
| Validate backup filename
|--------------------------------------------------------------------------
*/

function validateBackupFilename(string $filename): string
{
    $filename = basename($filename);

    if (
        $filename === '' ||
        !preg_match(
            '/^clan_backup_[0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{2}-[0-9]{2}-[0-9]{2}_[a-f0-9]+\.sql$/',
            $filename
        )
    ) {

        throw new InvalidArgumentException(
            'Invalid backup filename.'
        );
    }

    return $filename;
}


/*
|--------------------------------------------------------------------------
| Get backup path
|--------------------------------------------------------------------------
*/

function getBackupPath(string $filename): string
{
    $filename = validateBackupFilename($filename);

    $directory = getBackupDirectory();

    $path =
        $directory .
        DIRECTORY_SEPARATOR .
        $filename;

    if (!is_file($path)) {

        throw new RuntimeException(
            'Backup file not found.'
        );
    }

    return $path;
}


/*
|--------------------------------------------------------------------------
| List backups
|--------------------------------------------------------------------------
*/

function listDatabaseBackups(): array
{
    $directory = getBackupDirectory();

    $files = glob(
        $directory .
        DIRECTORY_SEPARATOR .
        'clan_backup_*.sql'
    );

    if (!$files) {
        return [];
    }

    usort(
        $files,
        function ($a, $b) {

            return filemtime($b)
                <=> filemtime($a);
        }
    );


    $backups = [];

    foreach ($files as $file) {

        if (!is_file($file)) {
            continue;
        }

        $backups[] = [

            'filename' => basename($file),

            'size' => filesize($file),

            'created_at' =>
                date(
                    'Y-m-d H:i:s',
                    filemtime($file)
                )

        ];
    }

    return $backups;
}