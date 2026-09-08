<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

if($_SERVER['REQUEST_METHOD']!='POST'){
    header("Location:index.php");
    exit;
}

$user_id=$_SESSION['user_id'];

$clanId = (int)($_SESSION['clan_id'] ?? 1);

$contribution_id=(int)$_POST['contribution_id'];
$payment_method_id=(int)$_POST['payment_method_id'];
$reference_number=trim($_POST['reference_number']);
$amount=(float)$_POST['amount'];

/*
|--------------------------------------------------------------------------
| Contribution Details
|--------------------------------------------------------------------------
*/

$stmt=$pdo->prepare("
SELECT *
FROM event_contributions
WHERE contribution_id=?
LIMIT 1
");

$stmt->execute([$contribution_id]);

$contribution=$stmt->fetch(PDO::FETCH_ASSOC);

if(!$contribution){
    die(__('contribution_not_found'));
}

/*
|--------------------------------------------------------------------------
| Total Already Paid
|--------------------------------------------------------------------------
*/

$stmt=$pdo->prepare("
SELECT COALESCE(SUM(amount),0)
FROM contribution_payments
WHERE contribution_id=?
AND member_user_id=?
AND payment_status IN('pending','approved')
");

$stmt->execute([
$contribution_id,
$user_id
]);

$totalPaid=(float)$stmt->fetchColumn();

$newTotal=$totalPaid+$amount;

if($newTotal>$contribution['required_amount']){
    die(__('amount_exceeds_required_contribution'));
}

/*
|--------------------------------------------------------------------------
| Upload Proof
|--------------------------------------------------------------------------
*/

$proof=NULL;

if(!empty($_FILES['payment_proof']['name'])){

    $ext=strtolower(pathinfo($_FILES['payment_proof']['name'],PATHINFO_EXTENSION));

    $allowed=['jpg','jpeg','png','pdf'];

    if(!in_array($ext,$allowed)){
        die(__('invalid_file'));
    }

    $proof=time().'_'.uniqid().'.'.$ext;

    move_uploaded_file(

        $_FILES['payment_proof']['tmp_name'],

        "../../uploads/payment_proofs/".$proof

    );

}

/*
|--------------------------------------------------------------------------
| Save Payment
|--------------------------------------------------------------------------
*/

try {
    $stmt=$pdo->prepare("
INSERT INTO contribution_payments(

clan_id,
contribution_id,
member_user_id,
payment_method_id,
amount,
reference_number,
payment_proof,
payment_status,
treasurer_status,
chairperson_status,
payment_date

)

VALUES(

?,?,?,?,?,?,?,?,?,?,NOW()

)
");

$stmt->execute([

$clanId,
$contribution_id,
$user_id,
$payment_method_id,
$amount,
$reference_number,
$proof,
'pending',
'Pending',
'Pending'

]);

header("Location:history.php?success=1");

exit;
} catch (PDOException $e) {
    die(__('payment_save_failed_prefix') . $e->getMessage());
}