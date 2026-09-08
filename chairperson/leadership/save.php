<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

if($_SERVER['REQUEST_METHOD']!='POST'){
    header("Location:index.php");
    exit;
}

$database=new Database();
$pdo=$database->connect();

$clanId=getCurrentClanId();

$user_id = (int)$_POST['user_id'];
$role_id = (int)$_POST['role_id'];
$username = trim($_POST['username']);
$password = trim($_POST['password']);

if($password!=""){

    $password_hash=password_hash($password,PASSWORD_DEFAULT);

    $stmt=$pdo->prepare("
        UPDATE users
        SET
            role_id=?,
            username=?,
            password_hash=?
        WHERE user_id=?
        AND clan_id=?
    ");

    $stmt->execute([
        $role_id,
        $username,
        $password_hash,
        $user_id,
        $clanId
    ]);

}else{

    $stmt=$pdo->prepare("
        UPDATE users
        SET
            role_id=?,
            username=?
        WHERE user_id=?
        AND clan_id=?
    ");

    $stmt->execute([
        $role_id,
        $username,
        $user_id,
        $clanId
    ]);

}

header("Location:index.php?success=1");
exit;