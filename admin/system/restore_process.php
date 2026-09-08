<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';
require_once '../../config/language.php';
require_once '../../config/backup.php';

requireLogin();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        throw new RuntimeException(
            'Invalid request.'
        );
    }


    if (empty($_POST['confirm_restore'])) {

        throw new RuntimeException(
            'You must confirm the restore operation.'
        );
    }


    $database = new Database();

    $pdo = $database->connect();


    /*
    |--------------------------------------------------------------------------
    | Determine source
    |--------------------------------------------------------------------------
    */

    $sqlFilePath = null;


    /*
    |--------------------------------------------------------------------------
    | Existing server backup
    |--------------------------------------------------------------------------
    */

    if (
        !empty($_POST['backup_file'])
    ) {

        $filename =
            $_POST['backup_file'];

        $sqlFilePath =
            getBackupPath($filename);
    }


    /*
    |--------------------------------------------------------------------------
    | Uploaded SQL file
    |--------------------------------------------------------------------------
    */

    elseif (
        isset($_FILES['sql_file']) &&
        $_FILES['sql_file']['error'] === UPLOAD_ERR_OK
    ) {

        $uploadedFile =
            $_FILES['sql_file'];


        $extension =
            strtolower(
                pathinfo(
                    $uploadedFile['name'],
                    PATHINFO_EXTENSION
                )
            );


        if ($extension !== 'sql') {

            throw new RuntimeException(
                'Only SQL files are allowed.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Store temporary uploaded file
        |--------------------------------------------------------------------------
        */

        $temporaryDirectory =
            getBackupDirectory();


        $temporaryFilename =
            'restore_' .
            bin2hex(random_bytes(8)) .
            '.sql';


        $sqlFilePath =
            $temporaryDirectory .
            DIRECTORY_SEPARATOR .
            $temporaryFilename;


        if (
            !move_uploaded_file(
                $uploadedFile['tmp_name'],
                $sqlFilePath
            )
        ) {

            throw new RuntimeException(
                'Unable to upload SQL file.'
            );
        }
    }


    if (
        !$sqlFilePath ||
        !is_file($sqlFilePath)
    ) {

        throw new RuntimeException(
            'Please select a backup file.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Database config
    |--------------------------------------------------------------------------
    */

    $config =
        getBackupDatabaseConfig($pdo);


    $databaseName =
        $config['database'];


    $mysql =
        getMysqlPath();


    $host =
        defined('DB_HOST')
            ? DB_HOST
            : '127.0.0.1';


    $username =
        defined('DB_USER')
            ? DB_USER
            : 'root';


    $password =
        defined('DB_PASS')
            ? DB_PASS
            : '';


    $port =
        defined('DB_PORT')
            ? DB_PORT
            : '3306';


    /*
    |--------------------------------------------------------------------------
    | Build mysql command
    |--------------------------------------------------------------------------
    */

    $command =
        escapeshellcmd($mysql) .
        ' --host=' .
        escapeshellarg($host) .
        ' --port=' .
        escapeshellarg($port) .
        ' --user=' .
        escapeshellarg($username);


    if ($password !== '') {

        $command .=
            ' --password=' .
            escapeshellarg($password);
    }


    $command .=
        ' ' .
        escapeshellarg($databaseName) .
        ' < ' .
        escapeshellarg($sqlFilePath) .
        ' 2>&1';


    /*
    |--------------------------------------------------------------------------
    | Execute restore
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
    | Delete temporary uploaded file
    |--------------------------------------------------------------------------
    */

    if (
        isset($_FILES['sql_file']) &&
        !empty($temporaryFilename) &&
        is_file($sqlFilePath)
    ) {

        @unlink($sqlFilePath);
    }


    if ($exitCode !== 0) {

        throw new RuntimeException(
            'Database restore failed. ' .
            implode("\n", $output)
        );
    }


    $_SESSION['success'] =
        'Database restored successfully.';


} catch (Throwable $e) {

    $_SESSION['error'] =
        $e->getMessage();
}


header('Location: restore.php');

exit;