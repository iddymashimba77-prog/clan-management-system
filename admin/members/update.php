<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

require_once '../../config/language.php';
requireAdministrator();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int)($_POST['user_id'] ?? 0);

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$database = new Database();
$pdo = $database->connect();

$clanId = getCurrentClanId();

function valueOrNull($value)
{
    $value = trim((string)$value);

    return $value === '' ? null : $value;
}

$firstName = ucwords(strtolower(trim($_POST['first_name'] ?? '')));
$middleName = $_POST['middle_name'] ?? '';
$middleName = $middleName !== '' ? ucwords(strtolower($middleName)) : null;
$lastName = ucwords(strtolower(trim($_POST['last_name'] ?? '')));
$gender = $_POST['gender'] ?? '';

if (
    $firstName === '' ||
    $lastName === '' ||
    !in_array($gender, ['Male', 'Female'], true)
) {
    die('Invalid member information.');
}

$dateOfBirth = valueOrNull($_POST['date_of_birth'] ?? '');
$phone = valueOrNull($_POST['phone'] ?? '');
$nationalId = valueOrNull($_POST['national_id'] ?? '');
$email = valueOrNull($_POST['email'] ?? '');
$username = valueOrNull($_POST['username'] ?? '');

$birthPlace = valueOrNull($_POST['birth_place'] ?? '');
$currentResidence = valueOrNull(
    $_POST['current_residence'] ?? ''
);

$occupation = valueOrNull(
    $_POST['occupation'] ?? ''
);

$maritalStatus = $_POST['marital_status'] ?? 'Single';

$allowedMarital = [
    'Single',
    'Married',
    'Divorced',
    'Widowed'
];

if (!in_array($maritalStatus, $allowedMarital, true)) {
    $maritalStatus = 'Single';
}

$familyMemberType =
    $_POST['family_member_type'] ?? 'blood';

if (!in_array(
    $familyMemberType,
    ['blood', 'marriage'],
    true
)) {
    $familyMemberType = 'blood';
}

$roleId = (int)($_POST['role_id'] ?? 4);

$accountStatus =
    $_POST['account_status'] ?? 'active';

if (!in_array(
    $accountStatus,
    ['active', 'inactive'],
    true
)) {
    $accountStatus = 'active';
}

$isDeceased =
    (int)($_POST['is_deceased'] ?? 0);

$isDeceased = $isDeceased === 1 ? 1 : 0;

$deathDate = valueOrNull(
    $_POST['death_date'] ?? ''
);

$deathPlace = valueOrNull(
    $_POST['death_place'] ?? ''
);

if (!$isDeceased) {
    $deathDate = null;
    $deathPlace = null;
}

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

$biography = valueOrNull(
    $_POST['biography'] ?? ''
);


/*
|--------------------------------------------------------------------------
| Prevent self relationships
|--------------------------------------------------------------------------
*/

if ($fatherId === $id) {
    $fatherId = null;
}

if ($motherId === $id) {
    $motherId = null;
}

if ($spouseId === $id) {
    $spouseId = null;
}


/*
|--------------------------------------------------------------------------
| Verify member exists
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM users
    WHERE user_id = ?
      AND clan_id = ?
    LIMIT 1
");

$stmt->execute([$id, $clanId]);

$oldMember = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$oldMember) {
    die('Member not found.');
}


/*
|--------------------------------------------------------------------------
| Unique username
|--------------------------------------------------------------------------
*/

if ($username !== null) {

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
        $id,
        $clanId
    ]);

    if ($stmt->fetch()) {
        die('Username already exists.');
    }
}


/*
|--------------------------------------------------------------------------
| Unique email
|--------------------------------------------------------------------------
*/

if ($email !== null) {

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
        $id,
        $clanId
    ]);

    if ($stmt->fetch()) {
        die('Email already exists.');
    }
}


/*
|--------------------------------------------------------------------------
| Image upload
|--------------------------------------------------------------------------
*/

function updateImage(
    $file,
    $directory,
    $oldFile = null
) {

    if (
        !isset($file) ||
        $file['error'] === UPLOAD_ERR_NO_FILE
    ) {
        return $oldFile;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception(
            'Image upload failed.'
        );
    }

    $mime = mime_content_type(
        $file['tmp_name']
    );

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

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

    /*
    |--------------------------------------------------------------------------
    | Delete previous image
    |--------------------------------------------------------------------------
    */

    if (
        $oldFile &&
        file_exists(
            rtrim($directory, '/') .
            '/' .
            $oldFile
        )
    ) {

        unlink(
            rtrim($directory, '/') .
            '/' .
            $oldFile
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
    | Update users
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE users
        SET
            first_name = :first_name,
            middle_name = :middle_name,
            last_name = :last_name,
            gender = :gender,
            date_of_birth = :date_of_birth,
            phone = :phone,
            national_id = :national_id,
            email = :email,
            username = :username,
            profile_picture = :profile_picture,
            cover_photo = :cover_photo,
            birth_place = :birth_place,
            current_residence = :current_residence,
            occupation = :occupation,
            marital_status = :marital_status,
            biography = :biography,
            family_member_type = :family_member_type,
            is_deceased = :is_deceased,
            death_date = :death_date,
            death_place = :death_place,
            role_id = :role_id,
            account_status = :account_status,
            father_user_id = :father_user_id,
            mother_user_id = :mother_user_id,
            spouse_user_id = :spouse_user_id
        WHERE user_id = :user_id
          AND clan_id = :clan_id
        LIMIT 1
    ");

    $stmt->execute([

        ':first_name' =>
            $firstName,

        ':middle_name' =>
            $middleName,

        ':last_name' =>
            $lastName,

        ':gender' =>
            $gender,

        ':date_of_birth' =>
            $dateOfBirth,

        ':phone' =>
            $phone,

        ':national_id' =>
            $nationalId,

        ':email' =>
            $email,

        ':username' =>
            $username,

        ':profile_picture' =>
            $profilePicture,

        ':cover_photo' =>
            $coverPhoto,

        ':birth_place' =>
            $birthPlace,

        ':current_residence' =>
            $currentResidence,

        ':occupation' =>
            $occupation,

        ':marital_status' =>
            $maritalStatus,

        ':biography' =>
            $biography,

        ':family_member_type' =>
            $familyMemberType,

        ':is_deceased' =>
            $isDeceased,

        ':death_date' =>
            $deathDate,

        ':death_place' =>
            $deathPlace,

        ':role_id' =>
            $roleId,

        ':account_status' =>
            $accountStatus,

        ':father_user_id' =>
            $fatherId,

        ':mother_user_id' =>
            $motherId,

        ':spouse_user_id' =>
            $spouseId,

        ':user_id' =>
            $id,

        ':clan_id' =>
            $clanId
    ]);


    /*
    |--------------------------------------------------------------------------
    | Reciprocal spouse relationship
    |--------------------------------------------------------------------------
    */

    if (!empty($oldMember['spouse_user_id'])) {

        $oldSpouseId =
            (int)$oldMember['spouse_user_id'];

        if (
            $oldSpouseId !== $spouseId
        ) {

            $stmt = $pdo->prepare("
                UPDATE users
                SET spouse_user_id = NULL
                WHERE user_id = ?
                  AND clan_id = ?
            ");

            $stmt->execute([
                $oldSpouseId,
                $clanId
            ]);
        }
    }

    if ($spouseId) {

        $stmt = $pdo->prepare("
            UPDATE users
            SET spouse_user_id = ?
            WHERE user_id = ?
              AND is_deleted = 0
              AND clan_id = ?
        ");

        $stmt->execute([
            $id,
            $spouseId,
            $clanId
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Generation
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        DELETE FROM member_generations
        WHERE user_id = ?
          AND clan_id = ?
    ");

    $stmt->execute([$id, $clanId]);

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
            $id,
            $generationId,
            $clanId
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Activity log
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

        'Updated Member',

        'users',

        $id,

        "Updated member {$oldMember['member_code']} - {$firstName} {$lastName}",

        $_SERVER['REMOTE_ADDR'] ?? null,

        $clanId
    ]);


    $pdo->commit();


    header(
        'Location: view.php?id=' .
        $id .
        '&success=' .
        urlencode(
            'Member updated successfully.'
        )
    );

    exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die(
        'Failed to update member: ' .
        htmlspecialchars($e->getMessage())
    );
}