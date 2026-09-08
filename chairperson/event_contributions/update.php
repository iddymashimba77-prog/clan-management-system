<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireChairperson();

$database = new Database();
$pdo = $database->connect();

$contribution_id = (int)$_POST['contribution_id'];

$event_id = (int)$_POST['event_id'];
$title = trim($_POST['title']);
$description = trim($_POST['description']);
$required_amount = $_POST['required_amount'];
$deadline = $_POST['deadline'];
$status = $_POST['status'];

$allow_partial_payment = isset($_POST['allow_partial_payment']) ? 1 : 0;

$stmt = $pdo->prepare("
UPDATE event_contributions

SET

event_id=?,
title=?,
description=?,
required_amount=?,
allow_partial_payment=?,
deadline=?,
status=?

WHERE contribution_id=?
");

$stmt->execute([

$event_id,
$title,
$description,
$required_amount,
$allow_partial_payment,
$deadline,
$status,
$contribution_id

]);

header("Location:index.php?updated=1");
exit;