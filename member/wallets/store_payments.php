<?php


require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();

$database = new Database();
$pdo = $database->connect();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Input
|--------------------------------------------------------------------------
*/

$wallet_id = (int)($_POST['wallet_id'] ?? 0);

$amount = (float)($_POST['amount'] ?? 0);

$payment_month_raw =
    trim($_POST['payment_month'] ?? '');

$reference_number =
    trim($_POST['reference_number'] ?? '');

$user_id =
    (int)$_SESSION['user_id'];

$clanId =
    (int)($_SESSION['clan_id'] ?? 1);


/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

if ($wallet_id <= 0) {
    die(__('invalid_wallet'));
}

if ($amount <= 0) {
    die(__('invalid_payment_amount'));
}

if (!preg_match('/^\d{4}-\d{2}$/', $payment_month_raw)) {
    die(__('invalid_payment_month'));
}

$payment_month =
    $payment_month_raw . '-01';

if ($reference_number === '') {
    die(__('reference_number_required'));
}


/*
|--------------------------------------------------------------------------
| Payment Proof
|--------------------------------------------------------------------------
*/

if (
    !isset($_FILES['payment_proof']) ||
    $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK
) {
    die(__('payment_proof_required'));
}


/*
|--------------------------------------------------------------------------
| Verify Wallet Membership
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        fw.wallet_id,
        fw.wallet_name,
        fw.monthly_amount,
        fw.bank_name,
        fw.account_name,
        fw.account_number

    FROM family_wallets fw

    INNER JOIN family_wallet_members fwm
        ON fw.wallet_id = fwm.wallet_id

    WHERE fw.wallet_id = ?
    AND fwm.user_id = ?
    AND fw.status = 'active'

    LIMIT 1
");

$stmt->execute([
    $wallet_id,
    $user_id
]);

$wallet = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$wallet) {
    die(__('not_assigned_to_wallet'));
}


/*
|--------------------------------------------------------------------------
| Prevent Duplicate Pending Payment
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT payment_id

    FROM family_wallet_payments

    WHERE wallet_id = ?
    AND member_user_id = ?
    AND payment_month = ?

    AND payment_status = 'pending'

    LIMIT 1
");

$stmt->execute([
    $wallet_id,
    $user_id,
    $payment_month
]);

if ($stmt->fetch()) {
    die(
        __('pending_payment_exists')
    );
}


/*
|--------------------------------------------------------------------------
| Upload Directory
|--------------------------------------------------------------------------
*/

$uploadDir =
    "/opt/lampp/htdocs/Clan_Management_System/uploads/payment_proofs/";

if (!is_dir($uploadDir)) {

    if (!mkdir($uploadDir, 0777, true)) {
        die(__('unable_to_create_upload_dir'));
    }
}

if (!is_writable($uploadDir)) {
    die(__('payment_upload_dir_not_writable'));
}


/*
|--------------------------------------------------------------------------
| Validate File
|--------------------------------------------------------------------------
*/

$originalName =
    $_FILES['payment_proof']['name'];

$tmpName =
    $_FILES['payment_proof']['tmp_name'];

$fileError =
    $_FILES['payment_proof']['error'];

$fileSize =
    $_FILES['payment_proof']['size'];

$extension =
    strtolower(
        pathinfo(
            $originalName,
            PATHINFO_EXTENSION
        )
    );

$allowed = [
    'jpg',
    'jpeg',
    'png',
    'pdf'
];

if (!in_array($extension, $allowed, true)) {
    die(__('only_jpg_jpeg_png_pdf'));
}

if ($fileSize > 10 * 1024 * 1024) {
    die(__('payment_proof_too_large'));
}


/*
|--------------------------------------------------------------------------
| Generate File Name
|--------------------------------------------------------------------------
*/

$fileName =
    uniqid('wallet_payment_', true)
    . '.'
    . $extension;

$destination =
    $uploadDir . $fileName;


/*
|--------------------------------------------------------------------------
| Move Upload
|--------------------------------------------------------------------------
*/

if (!move_uploaded_file(
    $tmpName,
    $destination
)) {

    die(__('failed_upload_payment_proof'));
}


/*
|--------------------------------------------------------------------------
| Database Transaction
|--------------------------------------------------------------------------
*/

try {

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | Insert Payment
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO family_wallet_payments
        (
            clan_id,
            wallet_id,
            member_user_id,
            amount,
            payment_month,
            reference_number,
            payment_proof,
            payment_status,
            secretary_status,
            treasurer_status,
            chairperson_status
        )

        VALUES
        (
            ?, ?, ?, ?, ?, ?, ?,
            'pending',
            'Pending',
            'Pending',
            'Pending'
        )
    ");

    $stmt->execute([
        $clanId,
        $wallet_id,
        $user_id,
        $amount,
        $payment_month,
        $reference_number,
        $fileName
    ]);

    $payment_id =
        $pdo->lastInsertId();


    /*
    |--------------------------------------------------------------------------
    | Get Member Name
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            CONCAT(
                first_name,
                ' ',
                COALESCE(last_name,'')
            )

        FROM users

        WHERE user_id = ?

        LIMIT 1
    ");

    $stmt->execute([
        $user_id
    ]);

    $memberName =
        trim($stmt->fetchColumn());


    /*
    |--------------------------------------------------------------------------
    | Notify Secretary
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->query("
        SELECT
            u.user_id

        FROM users u

        INNER JOIN roles r
            ON u.role_id = r.role_id

        WHERE r.role_name = 'Secretary'

        AND u.account_status = 'active'
    ");

    $secretaries =
        $stmt->fetchAll(PDO::FETCH_COLUMN);


    $notify = $pdo->prepare("
        INSERT INTO notifications
        (
            clan_id,
            user_id,
            title,
            notification_type,
            message
        )

        VALUES
        (?, ?, ?, ?, ?)
    ");


    foreach ($secretaries as $secretaryId) {

        $notify->execute([

            $clanId,
            $secretaryId,

            __('new_wallet_payment'),

            "wallet",

            $memberName
            . " "
            . __('wallet_payment_submitted')
            . " "
            . number_format($amount, 2)
            . " "
            . __('for_wallet_prefix')
            . " \""
            . $wallet['wallet_name']
            . "\". "
            . __('wallet_payment_review')

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Commit
    |--------------------------------------------------------------------------
    */

    $pdo->commit();


    /*
    |--------------------------------------------------------------------------
    | Redirect to correct index based on role
    |--------------------------------------------------------------------------
    */

    $roles = $_SESSION['roles'] ?? ['member'];

    if (in_array('Chairperson', $roles)) {
        header("Location: ../../chairperson/my_wallets/index.php?payment=success");
    } elseif (in_array('Treasurer', $roles)) {
        header("Location: ../../treasurer/my_wallets/index.php?payment=success");
    } elseif (in_array('Secretary', $roles)) {
        header("Location: ../../secretary/my_wallets/index.php?payment=success");
    } else {
        header("Location: index.php?payment=success");
    }

    exit;


} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Uploaded File
    |--------------------------------------------------------------------------
    */

    if (
        isset($destination) &&
        file_exists($destination)
    ) {
        unlink($destination);
    }


    die(
        __('payment_error')
        . ": "
        . htmlspecialchars(
            $e->getMessage()
        )
    );
}