<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die('Invalid payment ID.');
}

$user_id = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $remarks = trim($_POST['remarks'] ?? '');

    try {

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            SELECT *
            FROM family_wallet_payments
            WHERE payment_id = ?
            FOR UPDATE
        ");

        $stmt->execute([$id]);

        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$payment) {
            throw new Exception('Payment not found.');
        }

        if ($payment['payment_status'] === 'approved') {
            throw new Exception(
                'An approved payment cannot be rejected.'
            );
        }

        $stmt = $pdo->prepare("
            UPDATE family_wallet_payments

            SET
                payment_status = 'rejected',
                treasurer_status = 'Rejected',
                chairperson_status = 'Rejected',
                remarks = ?,
                approved_by_chairperson = ?,
                chairperson_date = NOW()

            WHERE payment_id = ?
        ");

        $stmt->execute([
            $remarks,
            $user_id,
            $id
        ]);

        $stmt = $pdo->prepare("
            INSERT INTO activity_logs
            (
                user_id,
                action,
                table_name,
                record_id,
                description,
                ip_address
            )

            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $user_id,
            'Reject Wallet Payment',
            'family_wallet_payments',
            $id,
            'Wallet payment rejected by administrator.',
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);

        $pdo->commit();

        header(
            'Location: payment_view.php?id=' .
            $id .
            '&success=rejected'
        );

        exit;

    } catch (Exception $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        die(
            'Unable to reject payment: ' .
            htmlspecialchars($e->getMessage())
        );
    }
}

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebar.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">

<div class="card clan-page-card">

<div class="card-header">

<h3 class="card-title text-danger">

<i class="fas fa-times-circle mr-2"></i>

Reject Wallet Payment

</h3>

</div>

<div class="card-body">

<form method="POST">

<div class="form-group">

<label><?= __('reason_for_rejection') ?></label>

<textarea
name="remarks"
class="form-control"
rows="5"
required
placeholder="Enter reason for rejecting this payment..."
></textarea>

</div>

<button
type="submit"
class="btn btn-danger"
onclick="return confirm('Reject this payment?')">

<i class="fas fa-times"></i>

Reject Payment

</button>

<a
href="payment_view.php?id=<?= $id ?>"
class="btn btn-secondary">

Cancel

</a>

</form>

</div>

</div>

</div>

</section>

</div>

<?php

require_once '../../includes/dashboard/footer.php';

?>