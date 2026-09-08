<?php

declare(strict_types=1);

require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/maindb.php';

requireOwnerLogin();

$database = new OwnerDatabase();
$pdo = $database->connect();

$clanDatabase = new OwnerClanDatabase();
$clanPdo = $clanDatabase->connect();

$ownerName =
    $_SESSION['owner_name']
    ?? $_SESSION['owner_username']
    ?? 'Owner';

/*
|--------------------------------------------------------------------------
| Installation Statistics
|--------------------------------------------------------------------------
*/

$totalInstallations = (int)$pdo->query("
    SELECT COUNT(*)
    FROM owner_installations
")->fetchColumn();

$activeInstallations = (int)$pdo->query("
    SELECT COUNT(*)
    FROM owner_installations
    WHERE installation_status = 'active'
")->fetchColumn();

$pendingInstallations = (int)$pdo->query("
    SELECT COUNT(*)
    FROM owner_installations
    WHERE installation_status = 'pending'
")->fetchColumn();

/*
|--------------------------------------------------------------------------
| Activation Statistics
|--------------------------------------------------------------------------
*/

$totalActivations = (int)$pdo->query("
    SELECT COUNT(*)
    FROM owner_activations
")->fetchColumn();

$activeActivations = (int)$pdo->query("
    SELECT COUNT(*)
    FROM owner_activations
    WHERE status = 'generated'
      AND expires_at > NOW()
")->fetchColumn();

$usedActivations = (int)$pdo->query("
    SELECT COUNT(*)
    FROM owner_activations
    WHERE status = 'used'
")->fetchColumn();

$revokedActivations = (int)$pdo->query("
    SELECT COUNT(*)
    FROM owner_activations
    WHERE status = 'revoked'
")->fetchColumn();

$expiredActivations = (int)$pdo->query("
    SELECT COUNT(*)
    FROM owner_activations
    WHERE status = 'expired'
       OR (
            status = 'generated'
            AND expires_at <= NOW()
       )
")->fetchColumn();


/*
|--------------------------------------------------------------------------
| Clan (Tenant) Statistics
|--------------------------------------------------------------------------
*/

$totalClans = (int)$clanPdo->query("
    SELECT COUNT(*)
    FROM clans
")->fetchColumn();

$activeClans = (int)$clanPdo->query("
    SELECT COUNT(*)
    FROM clans
    WHERE activation_status = 'active'
")->fetchColumn();

$suspendedClans = (int)$clanPdo->query("
    SELECT COUNT(*)
    FROM clans
    WHERE activation_status = 'suspended'
")->fetchColumn();

$pendingClans = (int)$clanPdo->query("
    SELECT COUNT(*)
    FROM clans
    WHERE activation_status = 'pending'
")->fetchColumn();

/*
|--------------------------------------------------------------------------
| Recent Activations
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        a.activation_id,
        a.installation_id,
        a.token_label,
        a.domain_name,
        a.status,
        a.expires_at,
        a.used_at,
        a.created_at,
        i.installation_code,
        i.client_name
    FROM owner_activations a
    LEFT JOIN owner_installations i
        ON i.installation_id = a.installation_id
    ORDER BY a.activation_id DESC
    LIMIT 10
");

$activations = $stmt->fetchAll();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Owner Control Panel</title>

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

/* HEADER */

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

.header-title {
    display: flex;
    align-items: center;
    gap: 12px;
}

.header-title i {
    font-size: 24px;
}

.header-title h1 {
    margin: 0;
    font-size: 21px;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 18px;
}

.owner-name {
    font-size: 14px;
    color: #d1d5db;
}

.logout {
    text-decoration: none;
    color: white;

    background: #dc2626;

    padding: 9px 15px;

    border-radius: 6px;

    font-size: 14px;
}

.logout:hover {
    background: #b91c1c;
}

/* CONTAINER */

.container {
    max-width: 1450px;

    margin: 30px auto;

    padding: 0 22px;
}

/* WELCOME */

.welcome {
    margin-bottom: 25px;
}

.welcome h2 {
    margin: 0 0 7px;

    font-size: 26px;
}

.welcome p {
    margin: 0;

    color: #6b7280;
}

/* STAT CARDS */

.cards {
    display: grid;

    grid-template-columns:
        repeat(auto-fit, minmax(190px, 1fr));

    gap: 18px;

    margin-bottom: 30px;
}

.card {
    background: white;

    border-radius: 10px;

    padding: 22px;

    box-shadow:
        0 2px 10px rgba(0,0,0,.07);

    border-left: 4px solid #2563eb;
}

.card.green {
    border-left-color: #16a34a;
}

.card.orange {
    border-left-color: #f59e0b;
}

.card.red {
    border-left-color: #dc2626;
}

.card.gray {
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

/* ACTION SECTION */

.actions {
    background: white;

    padding: 25px;

    border-radius: 10px;

    margin-bottom: 30px;

    box-shadow:
        0 2px 10px rgba(0,0,0,.07);
}

.actions h2 {
    margin: 0 0 18px;

    font-size: 20px;
}

.action-buttons {
    display: flex;

    flex-wrap: wrap;

    gap: 12px;
}

.btn {
    display: inline-block;

    padding: 11px 17px;

    border-radius: 6px;

    text-decoration: none;

    color: white;

    background: #2563eb;

    font-size: 14px;

    transition: .2s;
}

.btn:hover {
    opacity: .9;
    transform: translateY(-1px);
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

/* TABLE */

.table-container {
    background: white;

    border-radius: 10px;

    padding: 25px;

    box-shadow:
        0 2px 10px rgba(0,0,0,.07);

    overflow-x: auto;
}

.table-header {
    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 18px;
}

.table-header h2 {
    margin: 0;

    font-size: 20px;
}

table {
    width: 100%;

    border-collapse: collapse;

    min-width: 850px;
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

/* STATUS */

.status {
    display: inline-block;

    padding: 5px 9px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: bold;
}

.status.generated {
    background: #dcfce7;
    color: #166534;
}

.status.used {
    background: #dbeafe;
    color: #1e40af;
}

.status.revoked {
    background: #fee2e2;
    color: #991b1b;
}

.status.expired {
    background: #fef3c7;
    color: #92400e;
}

/* MOBILE */

@media (max-width: 700px) {

    .header {
        padding: 15px;
    }

    .owner-name {
        display: none;
    }

    .container {
        padding: 0 14px;
    }

    .welcome h2 {
        font-size: 22px;
    }

    .actions {
        padding: 18px;
    }

    .action-buttons {
        flex-direction: column;
    }

    .btn {
        text-align: center;
    }

}

</style>

</head>

<body>

<header class="header">

    <div class="header-title">

        <span>🛡️</span>

        <h1>
            OWNER CONTROL PANEL
        </h1>

    </div>

    <div class="header-right">

        <span class="owner-name">
            <?= htmlspecialchars($ownerName) ?>
        </span>

        <a
            href="logout.php"
            class="logout"
        >
            Logout
        </a>

    </div>

</header>


<main class="container">

    <section class="welcome">

        <h2>
            Developer / Owner Dashboard
        </h2>

        <p>
            Manage Clan Management System installations,
            activation licenses and client deployments.
        </p>

    </section>


    <!-- STATISTICS -->

    <section class="cards">

        <div class="card">

            <div class="card-title">
                Total Installations
            </div>

            <div class="number">
                <?= $totalInstallations ?>
            </div>

        </div>


        <div class="card green">

            <div class="card-title">
                Active Installations
            </div>

            <div class="number">
                <?= $activeInstallations ?>
            </div>

        </div>


        <div class="card orange">

            <div class="card-title">
                Pending Installations
            </div>

            <div class="number">
                <?= $pendingInstallations ?>
            </div>

        </div>


        <div class="card">

            <div class="card-title">
                Total Activations
            </div>

            <div class="number">
                <?= $totalActivations ?>
            </div>

        </div>


        <div class="card green">

            <div class="card-title">
                Active Activations
            </div>

            <div class="number">
                <?= $activeActivations ?>
            </div>

        </div>


        <div class="card">

            <div class="card-title">
                Used Activations
            </div>

            <div class="number">
                <?= $usedActivations ?>
            </div>

        </div>


        <div class="card red">

            <div class="card-title">
                Revoked Activations
            </div>

            <div class="number">
                <?= $revokedActivations ?>
            </div>

        </div>


        <div class="card orange">

            <div class="card-title">
                Expired Activations
            </div>

            <div class="number">
                <?= $expiredActivations ?>
            </div>

        </div>

        <div class="card">

            <div class="card-title">
                Total Clans
            </div>

            <div class="number">
                <?= $totalClans ?>
            </div>

        </div>


        <div class="card green">

            <div class="card-title">
                Active Clans
            </div>

            <div class="number">
                <?= $activeClans ?>
            </div>

        </div>


        <div class="card orange">

            <div class="card-title">
                Pending Clans
            </div>

            <div class="number">
                <?= $pendingClans ?>
            </div>

        </div>


        <div class="card red">

            <div class="card-title">
                Suspended Clans
            </div>

            <div class="number">
                <?= $suspendedClans ?>
            </div>

        </div>

    </section>


    <!-- ACTIONS -->

    <section class="actions">

        <h2>
            Activation Management
        </h2>

        <div class="action-buttons">

            <a
                href="activations/create.php"
                class="btn green"
            >
                + Create New Activation
            </a>

            <a
                href="activations/index.php"
                class="btn"
            >
                View Activations
            </a>

            <a
                href="installations/index.php"
                class="btn dark"
            >
                View Installations
            </a>

        </div>

    </section>



    <!-- CLAN MANAGEMENT -->

    <section class="actions">

        <h2>
            Clan Management
        </h2>

        <div class="action-buttons">

            <a
                href="clans/create.php"
                class="btn green"
            >
                + Create New Clan
            </a>

            <a
                href="clans/index.php"
                class="btn"
            >
                Manage Clans / Tenants
            </a>

        </div>

    </section>


    <!-- SUBSCRIPTION MANAGEMENT -->

    <section class="actions">

        <h2>
            Subscription Management
        </h2>

        <div class="action-buttons">

            <a
                href="subscriptions/create.php"
                class="btn green"
            >
                + New Subscription
            </a>

            <a
                href="subscriptions/index.php"
                class="btn"
            >
                Manage Subscriptions / Licenses
            </a>

        </div>

    </section>

    <!-- RECENT ACTIVATIONS -->

    <section class="table-container">

        <div class="table-header">

            <h2>
                Recent Activations
            </h2>

            <a
                href="activations/index.php"
                class="btn"
            >
                View All
            </a>

        </div>


        <table>

            <thead>

                <tr>

                    <th>ID</th>

                    <th>Installation</th>

                    <th>Client</th>

                    <th>Domain</th>

                    <th>Status</th>

                    <th>Expires</th>

                    <th>Created</th>

                    <th>Action</th>

                </tr>

            </thead>

            <tbody>

            <?php if (!$activations): ?>

                <tr>

                    <td colspan="8">

                        No activations found.

                    </td>

                </tr>

            <?php else: ?>

                <?php foreach ($activations as $activation): ?>

                    <?php

                    $status = $activation['status'];

                    if (
                        $status === 'generated'
                        &&
                        strtotime(
                            $activation['expires_at']
                        ) <= time()
                    ) {

                        $status = 'expired';

                    }

                    ?>

                    <tr>

                        <td>
                            <?= (int)$activation['activation_id'] ?>
                        </td>

                        <td>

                            <strong>

                                <?= htmlspecialchars(
                                    $activation['installation_code']
                                    ?? '-'
                                ) ?>

                            </strong>

                        </td>

                        <td>

                            <?= htmlspecialchars(
                                $activation['client_name']
                                ?? '-'
                            ) ?>

                        </td>

                        <td>

                            <?= htmlspecialchars(
                                $activation['domain_name']
                                ?? '-'
                            ) ?>

                        </td>

                        <td>

                            <span
                                class="status <?= htmlspecialchars($status) ?>"
                            >

                                <?= htmlspecialchars(
                                    ucfirst($status)
                                ) ?>

                            </span>

                        </td>

                        <td>

                            <?= htmlspecialchars(
                                $activation['expires_at']
                            ) ?>

                        </td>

                        <td>

                            <?= htmlspecialchars(
                                $activation['created_at']
                            ) ?>

                        </td>

                        <td>

                            <a
                                href="activations/view.php?id=<?= (int)$activation['activation_id'] ?>"
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

    </section>

</main>

</body>

</html>