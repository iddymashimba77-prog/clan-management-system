<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

requireOwnerLogin();

$database = new OwnerDatabase();
$pdo = $database->connect();

$stmt = $pdo->query("
    SELECT
        i.*,

        (
            SELECT COUNT(*)
            FROM owner_activations a
            WHERE a.installation_id = i.installation_id
        ) AS activation_count,

        (
            SELECT a.status
            FROM owner_activations a
            WHERE a.installation_id = i.installation_id
            ORDER BY a.activation_id DESC
            LIMIT 1
        ) AS latest_activation_status

    FROM owner_installations i

    ORDER BY i.installation_id DESC
");

$installations = $stmt->fetchAll();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Installations - Owner Panel</title>

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

.btn.dark {
    background: #374151;
}

.btn.red {
    background: #dc2626;
}

.btn.green {
    background: #16a34a;
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

.card {
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

    min-width: 1050px;
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

    .container {
        padding: 0 14px;
    }

    .page-header {
        flex-direction: column;

        align-items: flex-start;

        gap: 15px;
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

    <div class="page-header">

        <div>

            <h2>
                Client Installations
            </h2>

            <p>
                Monitor all Clan Management System installations.
            </p>

        </div>

    </div>


    <div class="card">

        <table>

            <thead>

                <tr>

                    <th>Installation Code</th>

                    <th>Client</th>

                    <th>Email</th>

                    <th>Domain</th>

                    <th>Status</th>

                    <th>Activations</th>

                    <th>Latest Activation</th>

                    <th>Installed</th>

                    <th>Action</th>

                </tr>

            </thead>

            <tbody>

            <?php if (!$installations): ?>

                <tr>

                    <td colspan="9">

                        No installations found.

                    </td>

                </tr>

            <?php else: ?>

                <?php foreach ($installations as $installation): ?>

                    <tr>

                        <td>

                            <strong>

                                <?= htmlspecialchars(
                                    $installation['installation_code']
                                ) ?>

                            </strong>

                        </td>

                        <td>

                            <?= htmlspecialchars(
                                $installation['client_name']
                                ?? '-'
                            ) ?>

                        </td>

                        <td>

                            <?= htmlspecialchars(
                                $installation['client_email']
                                ?? '-'
                            ) ?>

                        </td>

                        <td>

                            <?php

                            $clanUrl = clanAccessUrl(
                                $installation['domain_name']
                                ?? '-'
                            );

                            ?>

                            <a
                                href="<?= htmlspecialchars($clanUrl) ?>"
                                target="_blank"
                                rel="noopener"
                                style="color:#2563eb;"
                            >
                                <?= htmlspecialchars($clanUrl) ?>
                            </a>

                        </td>

                        <td>

                            <?php

                            $status =
                                $installation['installation_status'];

                            ?>

                            <span
                                class="status <?= htmlspecialchars($status) ?>"
                            >

                                <?= htmlspecialchars(
                                    ucfirst($status)
                                ) ?>

                            </span>

                        </td>

                        <td>

                            <?= (int)$installation['activation_count'] ?>

                        </td>

                        <td>

                            <?= htmlspecialchars(
                                $installation['latest_activation_status']
                                ?? 'None'
                            ) ?>

                        </td>

                        <td>

                            <?= htmlspecialchars(
                                $installation['installed_at']
                                ?? '-'
                            ) ?>

                        </td>

                        <td>

                            <a
                                href="view.php?id=<?= (int)$installation['installation_id'] ?>"
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