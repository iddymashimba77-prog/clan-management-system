<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');

$targetAmount = (float)($_POST['target_amount'] ?? 0);
$minimumAmount = (float)($_POST['minimum_amount'] ?? 0);

$allowPartial =
    isset($_POST['allow_partial_payment'])
    ? (int)$_POST['allow_partial_payment']
    : 1;

$startDate = !empty($_POST['start_date'])
    ? $_POST['start_date']
    : null;

$endDate = !empty($_POST['end_date'])
    ? $_POST['end_date']
    : null;

$status = $_POST['status'] ?? 'active';

$createdBy = $_SESSION['user_id'] ?? null;


if ($title === '' || $targetAmount <= 0) {

    header(
        'Location: create.php?error=invalid_data'
    );

    exit;
}


if ($minimumAmount < 0) {
    $minimumAmount = 0;
}


if ($minimumAmount > $targetAmount) {

    header(
        'Location: create.php?error=minimum_exceeds_target'
    );

    exit;
}


if (!in_array($status, ['active', 'closed'], true)) {
    $status = 'active';
}


if (
    $startDate &&
    $endDate &&
    $endDate < $startDate
) {

    header(
        'Location: create.php?error=invalid_dates'
    );

    exit;
}


$database = new Database();
$pdo = $database->connect();


$imageName = null;


try {

    /*
    |--------------------------------------------------------------------------
    | IMAGE UPLOAD
    |--------------------------------------------------------------------------
    */

    if (
        isset($_FILES['contribution_image']) &&
        $_FILES['contribution_image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if (
            $_FILES['contribution_image']['error']
            !== UPLOAD_ERR_OK
        ) {

            throw new Exception(
                'Image upload failed.'
            );
        }


        $allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        $fileType =
            mime_content_type(
                $_FILES['contribution_image']['tmp_name']
            );


        if (!in_array($fileType, $allowedTypes, true)) {

            throw new Exception(
                'Invalid image type.'
            );
        }


        $uploadDirectory =
            '../../uploads/contributions/';


        if (!is_dir($uploadDirectory)) {

            mkdir(
                $uploadDirectory,
                0775,
                true
            );
        }


        $extension =
            strtolower(
                pathinfo(
                    $_FILES['contribution_image']['name'],
                    PATHINFO_EXTENSION
                )
            );


        $imageName =
            'contribution_' .
            bin2hex(random_bytes(16)) .
            '.' .
            $extension;


        $destination =
            $uploadDirectory .
            $imageName;


        if (
            !move_uploaded_file(
                $_FILES['contribution_image']['tmp_name'],
                $destination
            )
        ) {

            throw new Exception(
                'Failed to save contribution image.'
            );
        }

    }


    /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO contributions
        (
            title,
            description,
            contribution_image,
            target_amount,
            allow_partial_payment,
            minimum_amount,
            current_amount,
            progress,
            total_paid_members,
            total_pending_members,
            start_date,
            end_date,
            status,
            created_by_user_id
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            0.00,
            0.00,
            0,
            0,
            ?,
            ?,
            ?,
            ?
        )
    ");


    $stmt->execute([
        $title,
        $description !== '' ? $description : null,
        $imageName,
        $targetAmount,
        $allowPartial,
        $minimumAmount,
        $startDate,
        $endDate,
        $status,
        $createdBy
    ]);


    $contributionId =
        (int)$pdo->lastInsertId();


    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOG
    |--------------------------------------------------------------------------
    */

    $log = $pdo->prepare("
        INSERT INTO activity_logs
        (
            user_id,
            action,
            table_name,
            record_id,
            description,
            ip_address
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ");


    $log->execute([
        $createdBy,
        'CONTRIBUTION_CREATED',
        'contributions',
        $contributionId,
        "Created contribution: {$title}",
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);


    header(
        'Location: index.php?success=created'
    );

    exit;


} catch (Exception $e) {

    if (
        $imageName &&
        file_exists(
            '../../uploads/contributions/' .
            $imageName
        )
    ) {

        unlink(
            '../../uploads/contributions/' .
            $imageName
        );
    }


    header(
        'Location: create.php?error=' .
        urlencode($e->getMessage())
    );

    exit;
}