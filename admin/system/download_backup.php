<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';
require_once '../../config/language.php';
require_once '../../config/backup.php';

requireLogin();

try {

    $filename = $_GET['file'] ?? '';

    $filePath = getBackupPath($filename);


    header(
        'Content-Type: application/sql'
    );

    header(
        'Content-Disposition: attachment; filename="' .
        basename($filename) .
        '"'
    );

    header(
        'Content-Length: ' .
        filesize($filePath)
    );

    header(
        'Cache-Control: no-cache, no-store, must-revalidate'
    );

    readfile($filePath);

    exit;

} catch (Throwable $e) {

    http_response_code(404);

    exit(
        htmlspecialchars(
            $e->getMessage()
        )
    );
}