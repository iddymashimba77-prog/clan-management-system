<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database = new Database();
$pdo = $database->connect();

$clanId = (int)($_SESSION['clan_id'] ?? 1);

if($_SERVER['REQUEST_METHOD']!='POST'){
    header("Location:index.php");
    exit;
}

$wallet_name      = trim($_POST['wallet_name']);
$purpose          = trim($_POST['purpose']);
$description      = trim($_POST['description']);

$monthly_amount   = (float)$_POST['monthly_amount'];
$target_amount    = (float)$_POST['target_amount'];

$start_date       = $_POST['start_date'];
$end_date         = $_POST['end_date'];

$bank_name        = trim($_POST['bank_name']);
$account_name     = trim($_POST['account_name']);
$account_number   = trim($_POST['account_number']);

$visibility       = $_POST['visibility'];

$created_by       = $_SESSION['user_id'];



/*
|--------------------------------------------------------------------------
| Calculate Months
|--------------------------------------------------------------------------
*/

$s = new DateTime($start_date);
$e = new DateTime($end_date);

$months =
(($e->format("Y")-$s->format("Y"))*12)
+
($e->format("m")-$s->format("m"))
+
1;



/*
|--------------------------------------------------------------------------
| Number of Members
|--------------------------------------------------------------------------
*/

if($visibility=='all'){

    $stmt=$pdo->query("
    SELECT COUNT(*)
    FROM users
    WHERE account_status='active'
    ");

    $membersCount=$stmt->fetchColumn();

}else{

    $membersCount=count($_POST['members']);

}

$expected_amount=
$monthly_amount*
$membersCount*
$months;



/*
|--------------------------------------------------------------------------
| Save Wallet
|--------------------------------------------------------------------------
*/

$stmt=$pdo->prepare("
INSERT INTO family_wallets(

clan_id,
wallet_name,
description,
purpose,

target_amount,
monthly_amount,

start_date,
end_date,

bank_name,
account_name,
account_number,

visibility,
expected_amount,

created_by_user_id

)

VALUES(

?,?,?,?,?,?,
?,?,?,?,?,
?,?,?

)
");

$stmt->execute([

$clanId,
$wallet_name,
$description,
$purpose,

$target_amount,
$monthly_amount,

$start_date,
$end_date,

$bank_name,
$account_name,
$account_number,

$visibility,
$expected_amount,

$created_by

]);

$wallet_id=$pdo->lastInsertId();



/*
|--------------------------------------------------------------------------
| Add Members
|--------------------------------------------------------------------------
*/

if($visibility=='all'){

    $users=$pdo->query("
    SELECT user_id
    FROM users
    WHERE account_status='active'
    ");

    while($u=$users->fetch(PDO::FETCH_ASSOC)){

        $member=$pdo->prepare("
        INSERT INTO family_wallet_members(
        clan_id,
        wallet_id,
        user_id
        )
        VALUES(?,?,?)
        ");

        $member->execute([

        $clanId,
        $wallet_id,

        $u['user_id']

        ]);



        /*
        Balance
        */

        $bal=$pdo->prepare("
        INSERT INTO family_wallet_balances(
        clan_id,
        wallet_id,
        user_id
        )
        VALUES(?,?,?)
        ");

        $bal->execute([

        $clanId,
        $wallet_id,

        $u['user_id']

        ]);



        /*
        Notification
        */

        $notify=$pdo->prepare("
        INSERT INTO notifications(

        clan_id,
        user_id,
        title,
        notification_type,
        message

        )

        VALUES(?,?,?,?,?)

        ");

        $notify->execute([

        $clanId,
        $u['user_id'],

        __('new_family_wallet'),

        "wallet",

        __('added_to_wallet_prefix').$wallet_name

        ]);

    }

}else{

foreach($_POST['members'] as $user_id){

$member=$pdo->prepare("
INSERT INTO family_wallet_members(
clan_id,
wallet_id,
user_id
)
VALUES(?,?,?)
");

$member->execute([

$clanId,
$wallet_id,

$user_id

]);


$bal=$pdo->prepare("
INSERT INTO family_wallet_balances(
clan_id,
wallet_id,
user_id
)
VALUES(?,?,?)
");

$bal->execute([

$clanId,
$wallet_id,

$user_id

]);


$notify=$pdo->prepare("
INSERT INTO notifications(

clan_id,
user_id,
title,
notification_type,
message

)

VALUES(?,?,?,?,?)

");

$notify->execute([

$clanId,
$user_id,

__('new_family_wallet'),

"wallet",

__('added_to_wallet_prefix').$wallet_name

]);

}

}

header("Location:index.php?success=1");
exit;