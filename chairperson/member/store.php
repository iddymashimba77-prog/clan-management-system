<?php


require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: create.php");
    exit;
}

$database = new Database();
$pdo = $database->connect();


/*
|--------------------------------------------------------------------------
| Generate Member Code
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT member_code
    FROM users
    ORDER BY user_id DESC
    LIMIT 1
");

$lastMember = $stmt->fetch(PDO::FETCH_ASSOC);

if ($lastMember && !empty($lastMember['member_code'])) {

    $lastNumber = (int) str_replace(
        'CLAN-',
        '',
        $lastMember['member_code']
    );

    $nextNumber = $lastNumber + 1;

} else {

    $nextNumber = 1;

}

$memberCode =
    'CLAN-' .
    str_pad(
        $nextNumber,
        7,
        '0',
        STR_PAD_LEFT
    );


/*
|--------------------------------------------------------------------------
| Receive Form Data
|--------------------------------------------------------------------------
*/

$first_name = ucwords(
    strtolower(trim($_POST['first_name'] ?? ''))
);

$middle_name = ucwords(
    strtolower(trim($_POST['middle_name'] ?? ''))
);

$last_name = ucwords(
    strtolower(trim($_POST['last_name'] ?? ''))
);

$gender = trim($_POST['gender'] ?? '');

$date_of_birth =
    $_POST['date_of_birth'] ?? null;

$phone =
    trim($_POST['phone'] ?? '');

$email =
    trim($_POST['email'] ?? '');

$birth_place =
    trim($_POST['birth_place'] ?? '');

$marital_status =
    $_POST['marital_status'] ?? 'Single';

$current_residence =
    trim($_POST['current_residence'] ?? '');

$biography =
    trim($_POST['biography'] ?? '');

$father_user_id =
    !empty($_POST['father_user_id'])
        ? (int) $_POST['father_user_id']
        : null;

$mother_user_id =
    !empty($_POST['mother_user_id'])
        ? (int) $_POST['mother_user_id']
        : null;

$spouse_user_id =
    !empty($_POST['spouse_user_id'])
        ? (int) $_POST['spouse_user_id']
        : null;

$role_id =
    (int) ($_POST['role_id'] ?? ROLE_MEMBER);

$username =
    trim($_POST['username'] ?? '');

$password =
    (string) ($_POST['password'] ?? '');

$password_hash =
    $password !== ''
        ? password_hash(
            $password,
            PASSWORD_DEFAULT
        )
        : null;

$is_deceased =
    isset($_POST['is_deceased']) ? 1 : 0;

$clan_id =
    getCurrentClanId();


/*
|--------------------------------------------------------------------------
| Check Username Uniqueness
|--------------------------------------------------------------------------
*/

if ($username !== '' && $clan_id !== null) {

    $dupStmt = $pdo->prepare("
        SELECT user_id
        FROM users
        WHERE username = ?
          AND clan_id = ?
        LIMIT 1
    ");

    $dupStmt->execute([
        $username,
        $clan_id
    ]);

    if ($dupStmt->fetch()) {

        die(__('username_already_exists'));
    }
}


/*
|--------------------------------------------------------------------------
| PROFILE PICTURE UPLOAD
|--------------------------------------------------------------------------
*/

$profilePicturePath = null;

if (
    isset($_FILES['profile_picture']) &&
    $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE
) {

    if (
        $_FILES['profile_picture']['error']
        !== UPLOAD_ERR_OK
    ) {
        die(__('profile_picture_upload_failed'));
    }


    /*
    |--------------------------------------------------------------------------
    | Maximum file size = 2MB
    |--------------------------------------------------------------------------
    */

    if (
        $_FILES['profile_picture']['size']
        > 2 * 1024 * 1024
    ) {
        die(__('profile_picture_too_large'));
    }


    /*
    |--------------------------------------------------------------------------
    | Check real image type
    |--------------------------------------------------------------------------
    */

    $finfo = new finfo(FILEINFO_MIME_TYPE);

    $mimeType = $finfo->file(
        $_FILES['profile_picture']['tmp_name']
    );


    $allowedTypes = [

        'image/jpeg' => 'jpg',

        'image/png' => 'png',

        'image/webp' => 'webp'

    ];


    if (!isset($allowedTypes[$mimeType])) {

        die(__('invalid_image_format'));
    }


    $extension =
        $allowedTypes[$mimeType];


    /*
    |--------------------------------------------------------------------------
    | Upload Directory
    |--------------------------------------------------------------------------
    */

    $uploadDirectory =
        '../../uploads/profile_pictures/';


    if (!is_dir($uploadDirectory)) {

        if (!mkdir(
            $uploadDirectory,
            0755,
            true
        )) {

            die(__('create_profile_dir_failed'));
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Unique File Name
    |--------------------------------------------------------------------------
    */

    $fileName =
        'member_' .
        bin2hex(random_bytes(16)) .
        '.' .
        $extension;


    $destination =
        $uploadDirectory .
        $fileName;


    /*
    |--------------------------------------------------------------------------
    | Move Uploaded File
    |--------------------------------------------------------------------------
    */

    if (
        !move_uploaded_file(
            $_FILES['profile_picture']['tmp_name'],
            $destination
        )
    ) {

        die(__('save_profile_picture_failed'));
    }


    /*
    |--------------------------------------------------------------------------
    | Path saved in database
    |--------------------------------------------------------------------------
    */

    $profilePicturePath =
        'uploads/profile_pictures/' .
        $fileName;
}


/*
|--------------------------------------------------------------------------
| Insert Member
|--------------------------------------------------------------------------
*/

$sql = "
INSERT INTO users
(
    member_code,
    first_name,
    middle_name,
    last_name,
    gender,
    date_of_birth,
    phone,
    email,
    username,
    password_hash,
    profile_picture,
    birth_place,
    marital_status,
    current_residence,
    biography,
    role_id,
    account_status,
    is_deceased,
    created_by_user_id,
    father_user_id,
    mother_user_id,
    spouse_user_id,
    clan_id
)
VALUES
(
    :member_code,
    :first_name,
    :middle_name,
    :last_name,
    :gender,
    :date_of_birth,
    :phone,
    :email,
    :username,
    :password_hash,
    :profile_picture,
    :birth_place,
    :marital_status,
    :current_residence,
    :biography,
    :role_id,
    :account_status,
    :is_deceased,
    :created_by_user_id,
    :father_user_id,
    :mother_user_id,
    :spouse_user_id,
    :clan_id
)
";

$stmt = $pdo->prepare($sql);

try {

    $stmt->execute([

        ':member_code' =>
            $memberCode,

        ':first_name' =>
            $first_name,

        ':middle_name' =>
            $middle_name !== ''
                ? $middle_name
                : null,

        ':last_name' =>
            $last_name,

        ':gender' =>
            $gender,

        ':date_of_birth' =>
            $date_of_birth ?: null,

        ':phone' =>
            $phone !== ''
                ? $phone
                : null,

        ':email' =>
            $email !== ''
                ? $email
                : null,

        ':username' =>
            $username !== ''
                ? $username
                : null,

        ':password_hash' =>
            $password_hash,

        ':profile_picture' =>
            $profilePicturePath,

        ':birth_place' =>
            $birth_place !== ''
                ? $birth_place
                : null,

        ':marital_status' =>
            $marital_status,

        ':current_residence' =>
            $current_residence !== ''
                ? $current_residence
                : null,

        ':biography' =>
            $biography !== ''
                ? $biography
                : null,

        ':role_id' =>
            $role_id,

        ':account_status' =>
            'active',

        ':is_deceased' =>
            $is_deceased,

        ':created_by_user_id' =>
            $_SESSION['user_id'],

        ':father_user_id' =>
            $father_user_id,

        ':mother_user_id' =>
            $mother_user_id,

        ':spouse_user_id' =>
            $spouse_user_id,

        ':clan_id' =>
            $clan_id

    ]);


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    header(
        "Location: index.php?success=member_added"
    );

    exit;


} catch (PDOException $e) {

    /*
    |--------------------------------------------------------------------------
    | Delete uploaded image if database insert fails
    |--------------------------------------------------------------------------
    */

    if (
        !empty($profilePicturePath)
    ) {

        $fileToDelete =
            '../../' .
            ltrim(
                $profilePicturePath,
                '/'
            );

        if (
            file_exists($fileToDelete)
        ) {
            unlink($fileToDelete);
        }
    }


    die(
        __('database_error_prefix') .
        htmlspecialchars(
            $e->getMessage()
        )
    );
}