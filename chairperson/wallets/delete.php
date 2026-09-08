<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database=new Database();
$pdo=$database->connect();

$wallet_id=(int)($_GET['id'] ?? 0);

$stmt=$pdo->prepare("
UPDATE family_wallets
SET wallet_status='Inactive'
WHERE wallet_id=?
");

$stmt->execute([$wallet_id]);

header("Location:index.php?deleted=1");
exit;