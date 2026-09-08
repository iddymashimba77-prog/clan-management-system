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

    $filename = $_GET['file'] ?? '';

    $filePath = getBackupPath($filename);


    if (!unlink($filePath)) {

        throw new RuntimeException(
            'Unable to delete backup file.'
        );
    }


    $_SESSION['success'] =
        'Backup deleted successfully.';


} catch (Throwable $e) {

    $_SESSION['error'] =
        $e->getMessage();
}


header('Location: backup.php');

exit;