<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/maindb.php';

requireOwnerLogin();

$id =
    (int)($_GET['id'] ?? 0);

if ($id <= 0) {

    exit('Invalid clan.');
}

$clanDatabase = new OwnerClanDatabase();
$pdo = $clanDatabase->connect();

$stmt = $pdo->prepare("
    SELECT *
    FROM clans
    WHERE clan_id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$clan = $stmt->fetch();

if (!$clan) {

    http_response_code(404);

    exit('Clan not found.');
}

$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM users
    WHERE clan_id = ?
");

$stmt->execute([$id]);

$memberCount = (int)$stmt->fetchColumn();

$ownerName =
    $_SESSION['owner_name']
    ?? $_SESSION['owner_username']
    ?? 'Owner';

?>
<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Subscription Details - Owner Panel</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    background: #f4f6f9;
    color: #1f2937;
}

.header {
    background: #111827;
    color: white;
    padding: 18px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0,0,0,.12);
}

.header h1 {
    margin: 0;
    font-size: 21px;
}

.header-right {
    display: flex;
    gap: 12px;
    align-items: center;
}

.owner-name {
    font-size: 14px;
    color: #d1d5db;
}

.btn {
    display: inline-block;
    padding: 10px 15px;
    border-radius: 6px;
    text-decoration: none;
    color: white;
    background: #2563eb;
    font-size: 13px;
    border: none;
    cursor: pointer;
}

.btn:hover {
    opacity: .9;
}

.btn.green {
    background: #16a34a;
}

.btn.orange {
    background: #f59e0b;
}

.btn.dark {
    background: #374151;
}

.btn.red {
    background: #dc2626;
}

.container {
    max-width: 900px;
    margin: 30px auto;
    padding: 0 22px;
}

.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 25px;
}

.page-header h2 {
    margin: 0;
    font-size: 25px;
}

.card {
    background: white;
    border-radius: 10px;
    padding: 28px;
    box-shadow: 0 2px 10px rgba(0,0,0,.07);
    margin-bottom: 22px;
}

.card h4 {
    margin: 0 0 18px;
    font-size: 17px;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table td {
    padding: 11px 0;
    border-bottom: 1px solid #f1f3f5;
    font-size: 14px;
}

.table td:first-child {
    width: 220px;
    color: #6b7280;
}

.status {
    display: inline-block;
    padding: 5px 9px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: bold;
}

.active {
    background: #dcfce7;
    color: #166534;
}

.trial {
    background: #dbeafe;
    color: #1e40af;
}

.expired {
    background: #fef3c7;
    color: #92400e;
}

.cancelled {
    background: #fee2e2;
    color: #991b1b;
}

.suspended {
    background: #e0e7ff;
    color: #3730a3;
}

.pending,
.revoked {
    background: #fee2e2;
    color: #991b1b;
}

.actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 20px;
    align-items: center;
}

.usage-bar {
    height: 12px;
    background: #f1f3f5;
    border-radius: 6px;
    overflow: hidden;
    width: 100%;
    margin-top: 10px;
}

.usage-fill {
    height: 100%;
    background: #2563eb;
    border-radius: 6px;
}

@media (max-width: 767.98px) {
    .header {
        flex-wrap: wrap;
        gap: 8px;
        padding: 14px 16px;
    }
    .header-right {
        flex-wrap: wrap;
        width: 100%;
    }
    .container {
        margin: 16px auto;
        padding: 0 14px;
    }
    .page-header {
        flex-wrap: wrap;
        gap: 10px;
    }
    .table-container,
    table {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
}

</style>

</head>

<body>

<header class="header">

    <h1>
        🛡️ OWNER CONTROL PANEL
    </h1>

    <div class="header-right">

        <span class="owner-name">
            <?= htmlspecialchars($ownerName) ?>
        </span>

        <a href="../index.php" class="btn dark">Dashboard</a>

        <a href="../logout.php" class="btn red">Logout</a>

    </div>

</header>


<main class="container">

    <div class="page-header">

        <a href="index.php" class="btn dark">← Back</a>

        <h2>
            Subscription Details
        </h2>

    </div>


    <div class="card">

        <h4>Subscription Information</h4>

        <div class="table-responsive">

        <table class="table">

            <tr>
                <td>Clan ID</td>
                <td><?= (int)$clan['clan_id'] ?></td>
            </tr>

            <tr>
                <td>Clan Code</td>
                <td><?= htmlspecialchars($clan['clan_code']) ?></td>
            </tr>

            <tr>
                <td>Clan Name</td>
                <td><strong><?= htmlspecialchars($clan['clan_name']) ?></strong></td>
            </tr>

            <tr>
                <td>Plan ID</td>
                <td><?= (int)($clan['plan_id'] ?? 0) ?></td>
            </tr>

            <tr>
                <td>Plan Name</td>
                <td><?= htmlspecialchars($clan['plan_name'] ?? '—') ?></td>
            </tr>

            <tr>
                <td>Subscription Status</td>
                <td>

                    <?php
                    $sub = $clan['subscription_status'];
                    ?>

                    <span class="status <?= htmlspecialchars($sub) ?>">
                        <?= htmlspecialchars(ucfirst($sub)) ?>
                    </span>

                </td>
            </tr>

            <tr>
                <td>Activation Status</td>
                <td>

                    <?php
                    $act = $clan['activation_status'];
                    ?>

                    <span class="status <?= htmlspecialchars($act) ?>">
                        <?= htmlspecialchars(ucfirst($act)) ?>
                    </span>

                </td>
            </tr>

            <tr>
                <td>Subscription Expires At</td>
                <td><?= htmlspecialchars($clan['subscription_expires_at'] ?? '—') ?></td>
            </tr>

            <tr>
                <td>Max Members</td>
                <td>

                    <?= $max = (int)($clan['max_members'] ?? 0) ?>

                    <?php if ($max > 0): ?>

                        <div class="usage-bar">

                            <?php
                            $usage = min(100, round(($memberCount / $max) * 100));
                            ?>

                            <div
                                class="usage-fill"
                                style="width: <?= $usage ?>%;"
                            ></div>

                        </div>

                        <small class="text-muted">
                            <?= $memberCount ?> / <?= $max ?> members used (<?= $usage ?>%)
                        </small>

                    <?php else: ?>

                        <small class="text-muted">
                            <?= $memberCount ?> members (unlimited)
                        </small>

                    <?php endif; ?>

                </td>
            </tr>

            <tr>
                <td>Started At</td>
                <td><?= htmlspecialchars($clan['started_at'] ?? '—') ?></td>
            </tr>

            <tr>
                <td>Activated At</td>
                <td><?= htmlspecialchars($clan['activated_at'] ?? '—') ?></td>
            </tr>

        </table>

        </div>
        <!-- /.table-responsive -->

        <div class="actions">

            <a
                href="edit.php?id=<?= (int)$clan['clan_id'] ?>"
                class="btn orange"
            >
                Edit Subscription
            </a>

            <a
                href="../clans/view.php?id=<?= (int)$clan['clan_id'] ?>"
                class="btn green"
            >
                View Clan
            </a>

        </div>

        <?php if ($clan['subscription_status'] !== 'active'): ?>

            <form
                action="status.php"
                method="POST"
                class="actions"
            >

                <?= ownerCsrfField() ?>

                <input
                    type="hidden"
                    name="clan_id"
                    value="<?= (int)$clan['clan_id'] ?>"
                >

                <input
                    type="hidden"
                    name="subscription_status"
                    value="active"
                >

                <button
                    type="submit"
                    class="btn green"
                    onclick="return confirm('Renew this subscription to Active?');"
                >
                    Renew (Active)
                </button>

            </form>

        <?php endif; ?>

        <?php if (
            $clan['subscription_status'] !== 'expired' &&
            $clan['subscription_status'] !== 'cancelled'
        ): ?>

            <form
                action="status.php"
                method="POST"
                class="actions"
            >

                <?= ownerCsrfField() ?>

                <input
                    type="hidden"
                    name="clan_id"
                    value="<?= (int)$clan['clan_id'] ?>"
                >

                <input
                    type="hidden"
                    name="subscription_status"
                    value="expired"
                >

                <button
                    type="submit"
                    class="btn red"
                    onclick="return confirm('Expire this subscription?');"
                >
                    Expire
                </button>

            </form>

            <form
                action="status.php"
                method="POST"
                class="actions"
            >

                <?= ownerCsrfField() ?>

                <input
                    type="hidden"
                    name="clan_id"
                    value="<?= (int)$clan['clan_id'] ?>"
                >

                <input
                    type="hidden"
                    name="subscription_status"
                    value="cancelled"
                >

                <button
                    type="submit"
                    class="btn red"
                    onclick="return confirm('Cancel this subscription?');"
                >
                    Cancel
                </button>

            </form>

        <?php endif; ?>

    </div>

</main>

</body>

</html>
