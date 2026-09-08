<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireSecretary();

$database = new Database();
$pdo = $database->connect();

$payment_id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
UPDATE contribution_payments
SET
secretary_status='Forwarded',
treasurer_status='Pending'
WHERE payment_id=?
");

$stmt->execute([$payment_id]);

$stmt=$pdo->query("
SELECT user_id
FROM users
WHERE role_id=3
");

$treasurer=$stmt->fetch(PDO::FETCH_ASSOC);

$notify=$pdo->prepare("
INSERT INTO notifications
(
user_id,
title,
notification_type,
message
)
VALUES
(?,?,?,?)
");

$notify->execute([

$treasurer['user_id'],

__('payment_forwarded'),

"payment",

__('contribution_payment_waiting_approval')

]);

header("Location:index.php?success=1");
exit;

