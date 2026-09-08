<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database=new Database();
$pdo=$database->connect();

$stmt=$pdo->prepare("
UPDATE family_wallets
SET

wallet_name=?,
goal=?,
monthly_amount=?,
year_target=?,
bank_name=?,
account_name=?,
account_number=?

WHERE wallet_id=?
");

$stmt->execute([

$_POST['wallet_name'],
$_POST['goal'],
$_POST['monthly_amount'],
$_POST['year_target'],
$_POST['bank_name'],
$_POST['account_name'],
$_POST['account_number'],
$_POST['wallet_id']

]);

header("Location:index.php?updated=1");
exit;