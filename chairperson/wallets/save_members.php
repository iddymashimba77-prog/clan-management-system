<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database=new Database();
$pdo=$database->connect();

$clanId = (int)($_SESSION['clan_id'] ?? 1);

$wallet_id=(int)$_POST['wallet_id'];

if(isset($_POST['for_all'])){

$stmt=$pdo->query("
SELECT user_id
FROM users
WHERE account_status='active'
");

$members=$stmt->fetchAll(PDO::FETCH_COLUMN);

}else{

$members=$_POST['members'] ?? [];

}

$insert=$pdo->prepare("
INSERT IGNORE INTO family_wallet_members(

clan_id,
wallet_id,
user_id

)

VALUES(

?,?,?

)
");

$notify=$pdo->prepare("
INSERT INTO notifications(

clan_id,
user_id,
title,
notification_type,
message

)

VALUES(

?,?,?,?,?

)
");

foreach($members as $member){

$insert->execute([$clanId,$wallet_id,$member]);

$notify->execute([

$clanId,
$member,

__('wallet_assignment_notification'),

"wallet",

__('added_to_family_wallet')

]);

}

header("Location:members.php?id=".$wallet_id);
exit;
