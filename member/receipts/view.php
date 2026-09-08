<?php

ini_set('display_startup_errors', 1);

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/language.php';
require_once '../../config/auth.php';

requireLogin();
requireMember();

$database = new Database();
$pdo = $database->connect();

$payment_id = (int)($_GET['id'] ?? 0);

if ($payment_id <= 0) {
    die(__('invalid_payment_id'));
}

try {

    $stmt = $pdo->prepare("
    SELECT

        cp.*,

        u.member_code,

        CONCAT(
            u.first_name,
            ' ',
            COALESCE(u.middle_name, ''),
            ' ',
            u.last_name
        ) AS member_name,

        ec.title AS contribution_title,

        e.title AS event_name,

        e.event_date,

        cs.clan_name

    FROM contribution_payments cp

    INNER JOIN users u
        ON cp.member_user_id = u.user_id

    INNER JOIN event_contributions ec
        ON cp.contribution_id = ec.contribution_id

    INNER JOIN events e
        ON ec.event_id = e.event_id

    LEFT JOIN clan_settings cs
        ON cs.setting_id = 1

    WHERE cp.payment_id = ?

    AND cp.member_user_id = ?

    AND cp.payment_status = 'approved'

    LIMIT 1
");

    $stmt->execute([
        $payment_id,
        $_SESSION['user_id']
    ]);

    $receipt = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$receipt) {
        die(__('receipt_not_found_or_not_approved'));
    }

} catch (PDOException $e) {

    die(
        "<h3>" . __('database_error') . "</h3>" .
        "<pre>" .
        htmlspecialchars($e->getMessage()) .
        "</pre>"
    );
}


define('USER_ROLE', 'Member');

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebars/member.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid" style="max-width: 1200px; margin: 0 auto;">

<div class="card shadow-sm">

<div class="card-body">

<div class="text-center">

<h2>
<?= htmlspecialchars(
    $receipt['clan_name'] ?? __('family_relation')
) ?>
</h2>

<h4><?= __('official_contribution_receipt') ?></h4>

<hr>

</div>


<table class="table table-bordered">

<tr>
<th><?= __('receipt_no') ?></th>
<td>
<?= htmlspecialchars(
    $receipt['payment_id']
) ?>
</td>
</tr>


<tr>
<th><?= __('reference_no') ?></th>
<td>
<?= htmlspecialchars(
    $receipt['reference_number'] ?? 'N/A'
) ?>
</td>
</tr>


<tr>
<th><?= __('member') ?></th>
<td>
<?= htmlspecialchars(
    $receipt['member_name']
) ?>
</td>
</tr>


<tr>
<th><?= __('member_code') ?></th>
<td>
<?= htmlspecialchars(
    $receipt['member_code']
) ?>
</td>
</tr>


<tr>
<th><?= __('event') ?></th>
<td>
<?= htmlspecialchars(
    $receipt['event_name']
) ?>
</td>
</tr>


<tr>
<th><?= __('contribution') ?></th>
<td>
<?= htmlspecialchars(
    $receipt['contribution_title']
) ?>
</td>
</tr>


<tr>
<th><?= __('amount_paid') ?></th>
<td>

<strong>

TZS

<?= number_format(
    (float)$receipt['amount'],
    2
) ?>

</strong>

</td>
</tr>


<tr>
<th><?= __('payment_date') ?></th>
<td>

<?= !empty($receipt['payment_date'])
    ? htmlspecialchars(
        $receipt['payment_date']
    )
    : 'N/A'
?>

</td>
</tr>


<tr>
<th><?= __('status') ?></th>
<td>

<span class="badge badge-success">

<?= __('approved') ?>

</span>

</td>
</tr>

</table>


<div class="mt-3">

<a
href="print.php?id=<?= $receipt['payment_id'] ?>"
target="_blank"
class="btn btn-success"
>

<i class="fas fa-print mr-1"></i>

<?= __('print_receipt') ?>

</a>


<a
href="index.php"
class="btn btn-secondary"
>

<?= __('back') ?>

</a>

</div>

</div>

</div>

</div>

</section>


<?php

require_once '../../includes/dashboard/footer.php';

?>