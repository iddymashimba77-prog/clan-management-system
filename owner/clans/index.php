<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/maindb.php';

requireOwnerLogin();

$ownerDatabase = new OwnerDatabase();
$pdo = $ownerDatabase->connect();

$clanDatabase = new OwnerClanDatabase();
$clanPdo = $clanDatabase->connect();

$totalClans = (int)$clanPdo->query("
    SELECT COUNT(*) FROM clans
")->fetchColumn();

$activeClans = (int)$clanPdo->query("
    SELECT COUNT(*) FROM clans
    WHERE activation_status = 'active'
")->fetchColumn();

$suspendedClans = (int)$clanPdo->query("
    SELECT COUNT(*) FROM clans
    WHERE activation_status = 'suspended'
")->fetchColumn();

$pendingClans = (int)$clanPdo->query("
    SELECT COUNT(*) FROM clans
    WHERE activation_status = 'pending'
")->fetchColumn();

$stmt = $clanPdo->query("
    SELECT
        clan_id,
        clan_name,
        clan_code,
        contact_email,
        activation_status,
        subscription_status,
        created_at
    FROM clans
    ORDER BY clan_id DESC
");

$clans = $stmt->fetchAll();

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

<title>Clans - Owner Panel</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

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

    box-shadow:
        0 2px 8px rgba(0,0,0,.12);
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
}

.btn:hover {
    opacity: .9;
}

.btn.green {
    background: #16a34a;
}

.btn.dark {
    background: #374151;
}

.btn.red {
    background: #dc2626;
}

.btn.orange {
    background: #f59e0b;
}

.container {
    max-width: 1450px;

    margin: 30px auto;

    padding: 0 22px;
}

.page-header {
    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 25px;
}

.page-header h2 {
    margin: 0 0 6px;

    font-size: 25px;
}

.page-header p {
    margin: 0;

    color: #6b7280;
}

.cards {
    display: grid;

    grid-template-columns:
        repeat(auto-fit, minmax(190px, 1fr));

    gap: 18px;

    margin-bottom: 30px;
}

.card-stat {
    background: white;

    border-radius: 10px;

    padding: 22px;

    box-shadow:
        0 2px 10px rgba(0,0,0,.07);

    border-left: 4px solid #2563eb;
}

.card-stat.green {
    border-left-color: #16a34a;
}

.card-stat.orange {
    border-left-color: #f59e0b;
}

.card-stat.red {
    border-left-color: #dc2626;
}

.card-stat.gray {
    border-left-color: #6b7280;
}

.card-title {
    color: #6b7280;

    font-size: 13px;

    margin-bottom: 9px;
}

.number {
    font-size: 30px;

    font-weight: bold;
}

.table-container {
    background: white;

    border-radius: 10px;

    padding: 25px;

    box-shadow:
        0 2px 10px rgba(0,0,0,.07);

    overflow-x: auto;
}

table {
    width: 100%;

    border-collapse: collapse;

    min-width: 900px;
}

th,
td {
    padding: 13px;

    border-bottom:
        1px solid #e5e7eb;

    text-align: left;

    font-size: 13px;
}

th {
    background: #f9fafb;

    color: #374151;

    font-weight: 600;
}

tr:hover {
    background: #f9fafb;
}

.status {
    display: inline-block;

    padding: 5px 9px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: bold;
}

.pending {
    background: #fef3c7;
    color: #92400e;
}

.active {
    background: #dcfce7;
    color: #166534;
}

.suspended {
    background: #e0e7ff;
    color: #3730a3;
}

.revoked {
    background: #fee2e2;
    color: #991b1b;
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

.alert-success {
    background: #dcfce7;
    color: #166534;
    padding: 12px 18px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 14px;
}

@media (max-width: 700px) {

    .header {
        flex-wrap: wrap;
        gap: 8px;
        padding: 15px;
    }

    .header-right {
        flex-wrap: wrap;
        width: 100%;
    }

    .owner-name {
        display: none;
    }

    .container {
        padding: 0 14px;
    }

    .page-header {
        flex-direction: column;

        align-items: flex-start;

        gap: 15px;
    }

    .cards {
        grid-template-columns: 1fr 1fr;
    }

}

@media (max-width: 575.98px) {

    .cards {
        grid-template-columns: 1fr;
    }

    .table-container {
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

        <a
            href="../index.php"
            class="btn dark"
        >
            Dashboard
        </a>

        <a
            href="../logout.php"
            class="btn red"
        >
            Logout
        </a>

    </div>

</header>


<main class="container">

    <?php if (!empty($_GET['success'])): ?>

        <div class="alert-success">
            <?= htmlspecialchars($_GET['success']) ?>
        </div>

    <?php endif; ?>

    <div class="page-header">

        <div>

            <h2>
                Clan Management
            </h2>

            <p>
                Manage all clans (tenants) in the system.
            </p>

        </div>

        <a
            href="create.php"
            class="btn green"
        >
            + Create Clan
        </a>

    </div>


    <section class="cards">

        <div class="card-stat">

            <div class="card-title">
                Total Clans
            </div>

            <div class="number">
                <?= $totalClans ?>
            </div>

        </div>

        <div class="card-stat green">

            <div class="card-title">
                Active Clans
            </div>

            <div class="number">
                <?= $activeClans ?>
            </div>

        </div>

        <div class="card-stat orange">

            <div class="card-title">
                Pending Clans
            </div>

            <div class="number">
                <?= $pendingClans ?>
            </div>

        </div>

        <div class="card-stat red">

            <div class="card-title">
                Suspended Clans
            </div>

            <div class="number">
                <?= $suspendedClans ?>
            </div>

        </div>

    </section>


    <div class="table-container">

        <table>

            <thead>

                <tr>

                    <th>ID</th>

                    <th>Clan Name</th>

                    <th>Clan Code</th>

                    <th>Contact Email</th>

                    <th>Activation</th>

                    <th>Subscription</th>

                    <th>Created</th>

                    <th>Actions</th>

                </tr>

            </thead>

            <tbody>

            <?php if (!$clans): ?>

                <tr>

                    <td colspan="8">

                        No clans found.

                    </td>

                </tr>

            <?php else: ?>

                <?php foreach ($clans as $clan): ?>

                    <tr>

                        <td>
                            <?= (int)$clan['clan_id'] ?>
                        </td>

                        <td>

                            <strong>

                                <?= htmlspecialchars(
                                    $clan['clan_name']
                                ) ?>

                            </strong>

                        </td>

                        <td>

                            <?= htmlspecialchars(
                                $clan['clan_code']
                            ) ?>

                        </td>

                        <td>

                            <?= htmlspecialchars(
                                $clan['contact_email']
                                ?? '-'
                            ) ?>

                        </td>

                        <td>

                            <?php
                            $actStatus = $clan['activation_status'];
                            ?>

                            <span
                                class="status <?= htmlspecialchars($actStatus) ?>"
                            >

                                <?= htmlspecialchars(
                                    ucfirst($actStatus)
                                ) ?>

                            </span>

                        </td>

                        <td>

                            <?php
                            $subStatus = $clan['subscription_status'];
                            ?>

                            <span
                                class="status <?= htmlspecialchars($subStatus) ?>"
                            >

                                <?= htmlspecialchars(
                                    ucfirst($subStatus)
                                ) ?>

                            </span>

                        </td>

                        <td>

                            <?= htmlspecialchars(
                                $clan['created_at']
                            ) ?>

                        </td>

                        <td>

                            <a
                                href="view.php?id=<?= (int)$clan['clan_id'] ?>"
                                class="btn"
                            >
                                View
                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</main>

</body>

</html>
