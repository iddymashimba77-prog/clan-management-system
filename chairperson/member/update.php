<?php

session_start();

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$database = new Database();
$pdo = $database->connect();

$clanId = getCurrentClanId();

if (!function_exists('valueOrNull')) {
    function valueOrNull($value)
    {
        $value = trim((string)$value);
        return $value === '' ? null : $value;
    }
}

/*
|--------------------------------------------------------------------------
| Receive Form Data
|--------------------------------------------------------------------------
*/

$user_id = (int) ($_POST['user_id'] ?? 0);

if ($user_id <= 0) {
    $_SESSION['error'] = __('invalid_member');
    header("Location: index.php");
    exit;
}

$first_name = ucwords(strtolower(trim($_POST['first_name'] ?? '')));
$middle_name = trim($_POST['middle_name'] ?? '');
$middle_name = $middle_name !== '' ? ucwords(strtolower($middle_name)) : null;
$last_name = ucwords(strtolower(trim($_POST['last_name'] ?? '')));

$gender = trim($_POST['gender'] ?? '');
$date_of_birth = valueOrNull($_POST['date_of_birth'] ?? '');

$phone = valueOrNull($_POST['phone'] ?? '');
$email = valueOrNull($_POST['email'] ?? '');
$national_id = valueOrNull($_POST['national_id'] ?? '');
$username = trim($_POST['username'] ?? '');
$username = $username !== '' ? $username : null;

$birth_place = valueOrNull($_POST['birth_place'] ?? '');
$current_residence = valueOrNull($_POST['current_residence'] ?? '');
$occupation = valueOrNull($_POST['occupation'] ?? '');

$marital_status = trim($_POST['marital_status'] ?? '');
if (!in_array($marital_status, ['Single','Married','Divorced','Widowed'], true)) {
    $marital_status = 'Single';
}

$family_member_type = $_POST['family_member_type'] ?? 'blood';
if (!in_array($family_member_type, ['blood','marriage'], true)) {
    $family_member_type = 'blood';
}

$role_id = (int) ($_POST['role_id'] ?? ROLE_MEMBER);

$account_status = $_POST['account_status'] ?? 'active';
if (!in_array($account_status, ['active','inactive'], true)) {
    $account_status = 'active';
}

$is_deceased = (int)($_POST['is_deceased'] ?? 0);
$is_deceased = $is_deceased === 1 ? 1 : 0;

$death_date = valueOrNull($_POST['death_date'] ?? '');
$death_place = valueOrNull($_POST['death_place'] ?? '');

if (!$is_deceased) {
    $death_date = null;
    $death_place = null;
}

$father_user_id = !empty($_POST['father_user_id']) ? $_POST['father_user_id'] : null;
$mother_user_id = !empty($_POST['mother_user_id']) ? $_POST['mother_user_id'] : null;
$spouse_user_id = !empty($_POST['spouse_user_id']) ? $_POST['spouse_user_id'] : null;

if ((int)$father_user_id === $user_id) { $father_user_id = null; }
if ((int)$mother_user_id === $user_id) { $mother_user_id = null; }
if ((int)$spouse_user_id === $user_id) { $spouse_user_id = null; }

$biography = valueOrNull($_POST['biography'] ?? '');


/*
|--------------------------------------------------------------------------
| Verify member exists & load old row
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM users
    WHERE user_id = ?
    LIMIT 1
");

$stmt->execute([$user_id]);

$oldMember = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$oldMember) {
    $_SESSION['error'] = __('member_not_found');
    header("Location: index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Prevent editing an Administrator (unless current user is an Administrator)
|--------------------------------------------------------------------------
*/

if (
    (int)$oldMember['role_id'] === ROLE_ADMINISTRATOR &&
    !hasRole('Administrator')
) {
    $_SESSION['error'] = __('cannot_edit_administrator');
    header("Location: index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Unique username (same clan)
|--------------------------------------------------------------------------
*/

if ($username !== null && $clanId !== null) {

    $stmt = $pdo->prepare("
        SELECT user_id
        FROM users
        WHERE username = ?
          AND user_id != ?
          AND clan_id = ?
        LIMIT 1
    ");

    $stmt->execute([$username, $user_id, $clanId]);

    if ($stmt->fetch()) {
        $_SESSION['error'] = __('username_already_exists');
        header("Location: index.php");
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| Image upload
|--------------------------------------------------------------------------
*/

function updateImage($file, $directory, $oldFile = null)
{
    if (
        !isset($file) ||
        $file['error'] === UPLOAD_ERR_NO_FILE
    ) {
        return $oldFile;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Image upload failed.');
    }

    $mime = mime_content_type($file['tmp_name']);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($allowed[$mime])) {
        throw new Exception('Only JPG, PNG and WEBP images are allowed.');
    }

    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    $filename = 'member_' . bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $destination = rtrim($directory, '/') . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception('Failed to save uploaded image.');
    }

    if (
        $oldFile &&
        file_exists(rtrim($directory, '/') . '/' . $oldFile)
    ) {
        unlink(rtrim($directory, '/') . '/' . $oldFile);
    }

    return $filename;
}

$profilePicture = updateImage(
    $_FILES['profile_picture'] ?? null,
    '../../uploads/profile_pictures',
    $oldMember['profile_picture'] ?? null
);

$coverPhoto = updateImage(
    $_FILES['cover_photo'] ?? null,
    '../../uploads/cover_photos',
    $oldMember['cover_photo'] ?? null
);


/*
|--------------------------------------------------------------------------
| Update Member
|--------------------------------------------------------------------------
*/

$sql = "
UPDATE users
SET
    first_name = ?,
    middle_name = ?,
    last_name = ?,
    gender = ?,
    date_of_birth = ?,
    phone = ?,
    email = ?,
    national_id = ?,
    username = ?,
    profile_picture = ?,
    cover_photo = ?,
    birth_place = ?,
    current_residence = ?,
    occupation = ?,
    marital_status = ?,
    family_member_type = ?,
    is_deceased = ?,
    death_date = ?,
    death_place = ?,
    role_id = ?,
    account_status = ?,
    father_user_id = ?,
    mother_user_id = ?,
    spouse_user_id = ?,
    biography = ?,
    updated_at = NOW()
WHERE user_id = ?
";

$stmt = $pdo->prepare($sql);

$stmt->execute([

    $first_name,
    $middle_name,
    $last_name,

    $gender,
    $date_of_birth,

    $phone,
    $email,
    $national_id,
    $username,

    $profilePicture,
    $coverPhoto,

    $birth_place,
    $current_residence,
    $occupation,

    $marital_status,
    $family_member_type,

    $is_deceased,
    $death_date,
    $death_place,

    $role_id,
    $account_status,

    $father_user_id,
    $mother_user_id,
    $spouse_user_id,

    $biography,

    $user_id
]);


/*
|--------------------------------------------------------------------------
| Reciprocal spouse relationship
|--------------------------------------------------------------------------
*/

if (!empty($oldMember['spouse_user_id']) && (int)$oldMember['spouse_user_id'] !== (int)$spouse_user_id) {

    $stmt = $pdo->prepare("
        UPDATE users
        SET spouse_user_id = NULL
        WHERE user_id = ?
    ");

    $stmt->execute([(int)$oldMember['spouse_user_id']]);
}

if ($spouse_user_id) {

    $stmt = $pdo->prepare("
        UPDATE users
        SET spouse_user_id = ?
        WHERE user_id = ?
          AND is_deleted = 0
    ");

    $stmt->execute([$user_id, (int)$spouse_user_id]);
}


$_SESSION['success'] = __('member_updated_successfully');

header("Location: index.php");
exit;
