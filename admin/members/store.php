<?php


ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../config/database.php';
require_once '../../config/constants.php';
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

    $lastNumber = (int) str_replace('CLAN-', '', $lastMember['member_code']);

    $nextNumber = $lastNumber + 1;

} else {

    $nextNumber = 1;

}

$memberCode = 'CLAN-' . str_pad($nextNumber, 7, '0', STR_PAD_LEFT);

/*
|--------------------------------------------------------------------------
| Receive Form Data
|--------------------------------------------------------------------------
*/

$first_name = trim($_POST['first_name'] ?? '');
$middle_name = trim($_POST['middle_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$gender = trim($_POST['gender'] ?? '');
$date_of_birth = $_POST['date_of_birth'] ?? null;
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$birth_place = trim($_POST['birth_place'] ?? '');
$marital_status = $_POST['marital_status'] ?? 'Single';
$current_residence = trim($_POST['current_residence'] ?? '');
$biography = trim($_POST['biography'] ?? '');
$father_user_id = !empty($_POST['father_user_id'])
    ? $_POST['father_user_id']
    : null;

$mother_user_id = !empty($_POST['mother_user_id'])
    ? $_POST['mother_user_id']
    : null;

$spouse_user_id = !empty($_POST['spouse_user_id'])
    ? $_POST['spouse_user_id']
    : null;

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
    birth_place,
    marital_status,
    current_residence,
    biography,
    role_id,
    account_status,
    created_by_user_id,
    father_user_id,
    mother_user_id,
    spouse_user_id
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
    :birth_place,
    :marital_status,
    :current_residence,
    :biography,
    :role_id,
    :account_status,
    :created_by_user_id,
    :father_user_id,
    :mother_user_id,
    :spouse_user_id
)
";

$stmt = $pdo->prepare($sql);

$stmt->execute([

    ':member_code' => $memberCode,
    ':first_name' => $first_name,
    ':middle_name' => $middle_name,
    ':last_name' => $last_name,
    ':gender' => $gender,
    ':date_of_birth' => $date_of_birth,
    ':phone' => $phone,
    ':email' => $email,
    ':birth_place' => $birth_place,
    ':marital_status' => $marital_status,
    ':current_residence' => $current_residence,
    ':biography' => $biography,

    // Member wa kawaida
    ':role_id' => ROLE_MEMBER,

    ':account_status' => 'active',

    ':created_by_user_id' => $_SESSION['user_id'],
    ':father_user_id' => $father_user_id,
    ':mother_user_id' => $mother_user_id,
    ':spouse_user_id' => $spouse_user_id

]);

echo "Member Registered Successfully";

