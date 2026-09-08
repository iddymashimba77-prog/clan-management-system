<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireAdministrator();

$database = new Database();
$pdo = $database->connect();

$clanId = getCurrentClanId();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

function postValue($key, $default = null)
{
    return isset($_POST[$key])
        ? trim($_POST[$key])
        : $default;
}

$firstName = postValue('first_name');
$middleName = postValue('middle_name');
$lastName = postValue('last_name');
$gender = postValue('gender');

if ($firstName !== null && $firstName !== '') {
    $firstName = ucwords(strtolower($firstName));
}

if ($middleName !== null && $middleName !== '') {
    $middleName = ucwords(strtolower($middleName));
}

if ($lastName !== null && $lastName !== '') {
    $lastName = ucwords(strtolower($lastName));
}

if ($firstName === '' || $lastName === '' || $gender === '') {
    die('Required member information is missing.');
}

$middleName = $middleName !== '' ? $middleName : null;

$dateOfBirth = postValue('date_of_birth');
$dateOfBirth = $dateOfBirth !== '' ? $dateOfBirth : null;

$phone = postValue('phone');
$email = postValue('email');
$username = postValue('username');
$nationalId = postValue('national_id');
$birthPlace = postValue('birth_place');
$currentResidence = postValue('current_residence');
$occupation = postValue('occupation');

$maritalStatus = postValue(
    'marital_status',
    'Single'
);

$familyMemberType = postValue(
    'family_member_type',
    'blood'
);

$accountStatus = postValue(
    'account_status',
    'active'
);

$roleId = (int)($_POST['role_id'] ?? 4);

$fatherId = !empty($_POST['father_user_id'])
    ? (int)$_POST['father_user_id']
    : null;

$motherId = !empty($_POST['mother_user_id'])
    ? (int)$_POST['mother_user_id']
    : null;

$spouseId = !empty($_POST['spouse_user_id'])
    ? (int)$_POST['spouse_user_id']
    : null;

$generationId = !empty($_POST['generation_id'])
    ? (int)$_POST['generation_id']
    : null;

$biography = postValue('biography');

$isDeceased = isset($_POST['is_deceased']) ? 1 : 0;

$password = $_POST['password'] ?? '';

if ($password === '') {
    die('Password is required.');
}

$passwordHash = password_hash(
    $password,
    PASSWORD_DEFAULT
);


/*
|--------------------------------------------------------------------------
| Generate member code
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT member_code
    FROM users
    WHERE member_code LIKE 'CLAN-%'
      AND clan_id = ?
    ORDER BY user_id DESC
    LIMIT 1
");
$stmt->execute([$clanId]);
$lastCode = $stmt->fetchColumn();

if ($lastCode && preg_match('/CLAN-(\d+)/', $lastCode, $matches)) {
    $nextNumber = ((int)$matches[1]) + 1;
} else {
    $nextNumber = 1;
}

$memberCode = 'CLAN-' . str_pad(
    $nextNumber,
    7,
    '0',
    STR_PAD_LEFT
);


/*
|--------------------------------------------------------------------------
| Username uniqueness
|--------------------------------------------------------------------------
*/

if ($username !== '') {

    $check = $pdo->prepare("
        SELECT user_id
        FROM users
        WHERE username = ?
          AND clan_id = ?
        LIMIT 1
    ");

    $check->execute([$username, $clanId]);

    if ($check->fetch()) {
        die('Username already exists.');
    }
}

if ($email !== '') {

    $check = $pdo->prepare("
        SELECT user_id
        FROM users
        WHERE email = ?
          AND clan_id = ?
        LIMIT 1
    ");

    $check->execute([$email, $clanId]);

    if ($check->fetch()) {
        die('Email already exists.');
    }
}


/*
|--------------------------------------------------------------------------
| Upload helper
|--------------------------------------------------------------------------
*/

function uploadImage($file, $directory)
{
    if (
        !isset($file) ||
        $file['error'] === UPLOAD_ERR_NO_FILE
    ) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Image upload failed.');
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    $mime = mime_content_type(
        $file['tmp_name']
    );

    if (!isset($allowed[$mime])) {
        throw new Exception(
            'Only JPG, PNG and WEBP images are allowed.'
        );
    }

    if (!is_dir($directory)) {
        mkdir(
            $directory,
            0775,
            true
        );
    }

    $filename =
        'member_' .
        bin2hex(random_bytes(16)) .
        '.' .
        $allowed[$mime];

    $destination =
        rtrim($directory, '/') .
        '/' .
        $filename;

    if (!move_uploaded_file(
        $file['tmp_name'],
        $destination
    )) {
        throw new Exception(
            'Failed to save uploaded image.'
        );
    }

    return $filename;
}


try {

    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | Images
    |--------------------------------------------------------------------------
    */

    $profilePicture = uploadImage(
        $_FILES['profile_picture'] ?? null,
        '../../uploads/profile_pictures'
    );

    $coverPhoto = uploadImage(
        $_FILES['cover_photo'] ?? null,
        '../../uploads/cover_photos'
    );


    /*
    |--------------------------------------------------------------------------
    | Insert member
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO users (
            member_code,
            first_name,
            middle_name,
            last_name,
            gender,
            date_of_birth,
            phone,
            national_id,
            email,
            username,
            password_hash,
            profile_picture,
            cover_photo,
            birth_place,
            current_residence,
            occupation,
            marital_status,
            biography,
            family_member_type,
            is_deceased,
            role_id,
            created_by_user_id,
            account_status,
            father_user_id,
            mother_user_id,
            spouse_user_id,
            clan_id
        )
        VALUES (
            :member_code,
            :first_name,
            :middle_name,
            :last_name,
            :gender,
            :date_of_birth,
            :phone,
            :national_id,
            :email,
            :username,
            :password_hash,
            :profile_picture,
            :cover_photo,
            :birth_place,
            :current_residence,
            :occupation,
            :marital_status,
            :biography,
            :family_member_type,
            :is_deceased,
            :role_id,
            :created_by_user_id,
            :account_status,
            :father_user_id,
            :mother_user_id,
            :spouse_user_id,
            :clan_id
        )
    ");

    $stmt->execute([
        ':member_code'       => $memberCode,
        ':first_name'        => $firstName,
        ':middle_name'       => $middleName,
        ':last_name'         => $lastName,
        ':gender'            => $gender,
        ':date_of_birth'     => $dateOfBirth,
        ':phone'             => $phone !== '' ? $phone : null,
        ':national_id'       => $nationalId !== '' ? $nationalId : null,
        ':email'             => $email !== '' ? $email : null,
        ':username'          => $username !== '' ? $username : null,
        ':password_hash'     => $passwordHash,
        ':profile_picture'   => $profilePicture,
        ':cover_photo'       => $coverPhoto,
        ':birth_place'       => $birthPlace !== '' ? $birthPlace : null,
        ':current_residence' => $currentResidence !== '' ? $currentResidence : null,
        ':occupation'        => $occupation !== '' ? $occupation : null,
        ':marital_status'    => $maritalStatus,
        ':biography'         => $biography !== '' ? $biography : null,
        ':family_member_type'=> $familyMemberType,
        ':is_deceased'       => $isDeceased,
        ':role_id'           => $roleId,
        ':created_by_user_id'=> $_SESSION['user_id'] ?? null,
        ':account_status'    => $accountStatus,
        ':father_user_id'    => $fatherId,
        ':mother_user_id'    => $motherId,
        ':spouse_user_id'    => $spouseId,
        ':clan_id'           => $clanId
    ]);

    $memberId = (int)$pdo->lastInsertId();


    /*
    |--------------------------------------------------------------------------
    | Generation
    |--------------------------------------------------------------------------
    */

    if ($generationId) {

        $stmt = $pdo->prepare("
            INSERT INTO member_generations (
                user_id,
                generation_id,
                clan_id
            )
            VALUES (?, ?, ?)
        ");

        $stmt->execute([
            $memberId,
            $generationId,
            $clanId
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Reciprocal spouse relationship
    |--------------------------------------------------------------------------
    */

    if ($spouseId) {

        $stmt = $pdo->prepare("
            UPDATE users
            SET spouse_user_id = ?
            WHERE user_id = ?
              AND is_deleted = 0
              AND clan_id = ?
        ");

        $stmt->execute([
            $memberId,
            $spouseId,
            $clanId
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Activity Log
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO activity_logs (
            user_id,
            action,
            table_name,
            record_id,
            description,
            ip_address,
            clan_id
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $_SESSION['user_id'] ?? null,
        'Created Member',
        'users',
        $memberId,
        "Created member {$memberCode} - {$firstName} {$lastName}",
        $_SERVER['REMOTE_ADDR'] ?? null,
        $clanId
    ]);


    $pdo->commit();

    header(
        'Location: index.php?success=' .
        urlencode(
            'Member created successfully.'
        )
    );

    exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die(
        'Failed to create member: ' .
        htmlspecialchars($e->getMessage())
    );
}