<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database = new Database();
$pdo = $database->connect();

$method_name   = trim($_POST['method_name']);
$provider_name = trim($_POST['provider_name']);
$phone_number  = trim($_POST['phone_number']);
$account_name  = trim($_POST['account_name']);
$account_number= trim($_POST['account_number']);

$clanId = (int)($_SESSION['clan_id'] ?? 1);

$stmt = $pdo->prepare("
INSERT INTO payment_methods(

clan_id,
method_name,
provider_name,
phone_number,
account_name,
account_number,
status,
created_by_user_id

)

VALUES(

?,?,?,?,?,?,'active',?

)
");

$stmt->execute([

$clanId,
$method_name,
$provider_name,
$phone_number,
$account_name,
$account_number,
$_SESSION['user_id']

]);

header("Location:create.php?method_added=1");
exit;