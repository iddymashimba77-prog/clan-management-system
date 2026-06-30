<?php

session_start();

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$database = new Database();
$pdo = $database->connect();

/*
|--------------------------------------------------------------------------
| Receive Form Data
|--------------------------------------------------------------------------
*/

$user_id = (int) ($_POST['user_id'] ?? 0);

$first_name = trim($_POST['first_name'] ?? '');
$middle_name = trim($_POST['middle_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');

$gender = trim($_POST['gender'] ?? '');
$date_of_birth = $_POST['date_of_birth'] ?? null;

$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');

$birth_place = trim($_POST['birth_place'] ?? '');
$current_residence = trim($_POST['current_residence'] ?? '');

$marital_status = trim($_POST['marital_status'] ?? '');

$father_user_id = !empty($_POST['father_user_id']) ? $_POST['father_user_id'] : null;
$mother_user_id = !empty($_POST['mother_user_id']) ? $_POST['mother_user_id'] : null;
$spouse_user_id = !empty($_POST['spouse_user_id']) ? $_POST['spouse_user_id'] : null;

$biography = trim($_POST['biography'] ?? '');

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

birth_place = ?,
current_residence = ?,

marital_status = ?,

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

$birth_place,
$current_residence,

$marital_status,

$father_user_id,
$mother_user_id,
$spouse_user_id,

$biography,

$user_id

]);

$_SESSION['success'] = "Member updated successfully.";

header("Location: index.php");
exit;