<?php


require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/language.php';
require_once '../config/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$database = new Database();
$pdo = $database->connect();

$clanId = getCurrentClanId();

$user_id = (int)$_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| Input
|--------------------------------------------------------------------------
*/

$first_name =
    ucwords(
        strtolower(
            trim($_POST['first_name'] ?? '')
        )
    );

$middle_name =
    ucwords(
        strtolower(
            trim($_POST['middle_name'] ?? '')
        )
    );

$last_name =
    ucwords(
        strtolower(
            trim($_POST['last_name'] ?? '')
        )
    );

$username =
    trim($_POST['username'] ?? '');

$email =
    trim($_POST['email'] ?? '');

$phone =
    trim($_POST['phone'] ?? '');

$current_residence =
    trim($_POST['current_residence'] ?? '');

$biography =
    trim($_POST['biography'] ?? '');


/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

if (
    $first_name === '' ||
    $last_name === '' ||
    $username === ''
) {

    header(
        "Location: index.php?error="
        . urlencode(
            __('update_error_required_fields')
        )
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| Check Username
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT user_id

    FROM users

    WHERE username = ?

    AND user_id != ?

    AND clan_id = ?

    LIMIT 1
");

$stmt->execute([
    $username,
    $user_id,
    $clanId
]);

if ($stmt->fetch()) {

    header(
        "Location: index.php?error="
        . urlencode(
            __('update_error_username_taken')
        )
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| Check Email
|--------------------------------------------------------------------------
*/

if ($email !== '') {

    $stmt = $pdo->prepare("
        SELECT user_id

        FROM users

        WHERE email = ?

        AND user_id != ?

        AND clan_id = ?

        LIMIT 1
    ");

    $stmt->execute([
        $email,
        $user_id,
        $clanId
    ]);

    if ($stmt->fetch()) {

        header(
            "Location: index.php?error="
        . urlencode(
            __('update_error_email_taken')
        )
        );

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| Get Current Profile Picture
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT profile_picture_self

    FROM users

    WHERE user_id = ?

      AND clan_id = ?

    LIMIT 1
");

$stmt->execute([
    $user_id,
    $clanId
]);

$oldPicture =
    $stmt->fetchColumn();

$newPicture =
    $oldPicture;


/*
|--------------------------------------------------------------------------
| Upload Profile Picture
|--------------------------------------------------------------------------
*/

if (
    isset($_FILES['profile_picture']) &&
    $_FILES['profile_picture']['error']
    === UPLOAD_ERR_OK
) {

    $file = $_FILES['profile_picture'];

    if ($file['size'] > 5 * 1024 * 1024) {

        header(
            "Location: index.php?error="
        . urlencode(
            __('update_error_photo_size')
        )
        );

        exit;
    }


    $extension =
        strtolower(
            pathinfo(
                $file['name'],
                PATHINFO_EXTENSION
            )
        );


    $allowed = [
        'jpg',
        'jpeg',
        'png',
        'webp'
    ];


    if (
        !in_array(
            $extension,
            $allowed,
            true
        )
    ) {

        header(
            "Location: index.php?error="
        . urlencode(
            __('update_error_photo_format')
        )
        );

        exit;
    }


    $uploadDir =
        "/opt/lampp/htdocs/Clan_Management_System/uploads/profiles/";


    if (!is_dir($uploadDir)) {

        mkdir(
            $uploadDir,
            0777,
            true
        );
    }


    if (!is_writable($uploadDir)) {

        die(
            __('update_upload_dir_not_writable')
        );
    }


    $newPicture =
        'profile_'
        . $user_id
        . '_'
        . time()
        . '.'
        . $extension;


    $destination =
        $uploadDir . $newPicture;


    if (
        !move_uploaded_file(
            $file['tmp_name'],
            $destination
        )
    ) {

        die(
            __('update_upload_failed')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Old Picture
    |--------------------------------------------------------------------------
    */

    if (
        !empty($oldPicture)
    ) {

        $oldFile =
            $uploadDir . $oldPicture;

        if (
            file_exists($oldFile)
        ) {

            unlink($oldFile);
        }
    }
}


/*
|--------------------------------------------------------------------------
| Update User
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("
        UPDATE users

        SET
            first_name = ?,
            middle_name = ?,
            last_name = ?,
            username = ?,
            email = ?,
            phone = ?,
            current_residence = ?,
            biography = ?,
            profile_picture_self = ?

        WHERE user_id = ?

          AND clan_id = ?
    ");

    $stmt->execute([

        $first_name,

        $middle_name !== ''
            ? $middle_name
            : null,

        $last_name,

        $username,

        $email !== ''
            ? $email
            : null,

        $phone !== ''
            ? $phone
            : null,

        $current_residence !== ''
            ? $current_residence
            : null,

        $biography !== ''
            ? $biography
            : null,

        $newPicture,

        $user_id,

        $clanId
    ]);


    /*
    |--------------------------------------------------------------------------
    | Update Session
    |--------------------------------------------------------------------------
    */

    $_SESSION['username'] = $username;
$_SESSION['profile_picture'] = $newPicture;

    $_SESSION['full_name'] =
        trim(
            $first_name . ' ' .
            $last_name
        );


    header(
        "Location: index.php?success="
        . urlencode(
            __('profile_updated_successfully')
        )
    );

    exit;


} catch (Throwable $e) {

    if (
        isset($newPicture) &&
        $newPicture !== $oldPicture
    ) {

        $file =
            "/opt/lampp/htdocs/Clan_Management_System/uploads/profiles/"
            . $newPicture;

        if (
            file_exists($file)
        ) {

            unlink($file);
        }
    }


    die(
        __('profile_update_error')
        . ": "
        . htmlspecialchars(
            $e->getMessage()
        )
    );
}