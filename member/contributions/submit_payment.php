<?php


require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireClanUser();

if($_SERVER['REQUEST_METHOD']!='POST'){
    header("Location:index.php");
    exit;
}

$database = new Database();
$pdo = $database->connect();
$clanId = getCurrentClanId();

$contribution_id = (int)$_POST['contribution_id'];
$member_id = $_SESSION['user_id'];
$payment_method_id = (int)$_POST['payment_method_id'];
$amount = (float)$_POST['amount'];
$reference = trim($_POST['reference_number']);

$receipt = "";

if(
    isset($_FILES['payment_proof']) &&
    $_FILES['payment_proof']['error']==0
){

    $ext = strtolower(pathinfo($_FILES['payment_proof']['name'],PATHINFO_EXTENSION));

    $receipt = time()."_".uniqid().".".$ext;

    move_uploaded_file(

        $_FILES['payment_proof']['tmp_name'],

        "../../assets/uploads/contribution_receipts/".$receipt

    );

}

$stmt = $pdo->prepare("

INSERT INTO contribution_payments(

contribution_id,
member_user_id,
payment_method_id,
amount,
reference_number,
receipt,
payment_status,
treasurer_status,
chairperson_status,
clan_id,
payment_date

)

VALUES(

?,?,?,?,?,
?,
'pending',
'Pending',
'Pending',
?,
NOW()

)

");


$stmt->execute([

$contribution_id,
$member_id,
$payment_method_id,
$amount,
$reference,
$receipt,
$clanId

]);

header("Location:history.php?success=1");
exit;