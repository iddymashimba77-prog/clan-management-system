<?php
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$payment_id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
SELECT
cp.*,
u.member_code,
CONCAT(u.first_name,' ',u.last_name) AS member_name,
e.title AS event_name,
ec.title AS contribution_title,
pm.method_name
FROM contribution_payments cp
INNER JOIN users u ON cp.member_user_id=u.user_id
INNER JOIN payment_methods pm ON cp.payment_method_id=pm.payment_method_id
INNER JOIN event_contributions ec ON cp.contribution_id=ec.contribution_id
INNER JOIN events e ON ec.event_id=e.event_id
WHERE cp.payment_id=?
LIMIT 1
");

$stmt->execute([$payment_id]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$payment){
    die(__('payment_not_found'));
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= __('payment_receipt') ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; box-sizing: border-box; }
        .receipt { max-width: 600px; width: 100%; box-sizing: border-box; margin: 0 auto; border: 1px solid #ddd; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .row { display: flex; flex-wrap: wrap; gap: 6px 10px; justify-content: space-between; margin: 10px 0; }
        .label { font-weight: bold; white-space: nowrap; }
        .value { word-break: break-word; text-align: right; }
        .amount { font-size: 24px; font-weight: bold; color: #28a745; }
        .status { padding: 5px 10px; border-radius: 4px; }
        .status.approved { background: #d4edda; color: #155724; }
        .status.pending { background: #fff3cd; color: #856404; }
        .status.rejected { background: #f8d7da; color: #721c24; }
        @media (max-width: 480px) {
            body { padding: 8px; }
            .receipt { padding: 14px; border: none; }
            .amount { font-size: 20px; }
        }
        @media print {
            body { padding: 0; }
            .receipt { border: 1px solid #ddd; box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h2><?= __('payment_receipt') ?></h2>
            <p><?= __('receipt_number_prefix') ?> <?= $payment['payment_id'] ?></p>
        </div>
        
        <div class="row">
            <span class="label"><?= __('member') ?>:</span>
            <span class="value"><?= htmlspecialchars($payment['member_name']) ?> (<?= $payment['member_code'] ?>)</span>
        </div>
        
        <div class="row">
            <span class="label"><?= __('event') ?>:</span>
            <span class="value"><?= htmlspecialchars($payment['event_name']) ?></span>
        </div>
        
        <div class="row">
            <span class="label"><?= __('contribution') ?>:</span>
            <span class="value"><?= htmlspecialchars($payment['contribution_title']) ?></span>
        </div>
        
        <div class="row">
            <span class="label"><?= __('payment_method') ?>:</span>
            <span class="value"><?= htmlspecialchars($payment['method_name']) ?></span>
        </div>
        
        <div class="row">
            <span class="label"><?= __('reference') ?>:</span>
            <span class="value"><?= htmlspecialchars($payment['reference_number']) ?></span>
        </div>
        
        <div class="row">
            <span class="label"><?= __('date') ?>:</span>
            <span class="value"><?= date('Y-m-d H:i', strtotime($payment['payment_date'])) ?></span>
        </div>
        
        <hr>
        
        <div class="row">
            <span class="label"><?= __('amount') ?>:</span>
            <span class="value amount">TZS <?= number_format($payment['amount'], 2) ?></span>
        </div>
        
        <div class="row">
            <span class="label"><?= __('status') ?>:</span>
            <span class="value status <?= $payment['payment_status'] ?>"><?= ucfirst($payment['payment_status']) ?></span>
        </div>
        
        <div style="margin-top: 30px; text-align: center; color: #666;">
            <p><?= __('thank_you_for_contribution') ?></p>
        </div>
    </div>
    
    <script>
        window.print();
    </script>
</body>
</html>
