<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database=new Database();
$pdo=$database->connect();

$id=(int)$_GET['id'];

$stmt=$pdo->prepare("
SELECT wallet_id
FROM family_wallet_members
WHERE id=?
");

$stmt->execute([$id]);

$row=$stmt->fetch(PDO::FETCH_ASSOC);

$pdo->prepare("
DELETE
FROM family_wallet_members
WHERE id=?
")->execute([$id]);

header("Location:members.php?id=".$row['wallet_id']);
exit;