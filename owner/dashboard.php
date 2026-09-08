<?php

declare(strict_types=1);

require_once __DIR__ . '/config/bootstrap.php';

requireOwnerLogin();

$database = new OwnerDatabase();
$pdo = $database->connect();

/*
|--------------------------------------------------------------------------
| Owner Information
|--------------------------------------------------------------------------
*/

$ownerName =
    $_SESSION['owner_name']
    ?? $_SESSION['owner_username']
    ?? 'Owner';

/*
|--------------------------------------------------------------------------
| Installation Statistics
|--------------------------------------------------------------------------
*/

$totalInstallations = (int) $pdo
    ->query("
        SELECT COUNT(*)
        FROM owner_installations
    ")
    ->fetchColumn();

/*
|--------------------------------------------------------------------------
| Activation Statistics
|--------------------------------------------------------------------------
*/

$totalActivations = (int) $pdo
    ->query("
        SELECT COUNT(*)
        FROM owner_activations
    ")
    ->fetchColumn();

$activeActivations = (int) $pdo
    ->query("
        SELECT COUNT(*)
        FROM owner_activations
        WHERE status = 'generated'
          AND expires_at > NOW()
    ")
    ->fetchColumn();

$usedActivations = (int) $pdo
    ->query("
        SELECT COUNT(*)
        FROM owner_activations
        WHERE status = 'used'
    ")
    ->fetchColumn();

$revokedActivations = (int) $pdo
    ->query("
        SELECT COUNT(*)
        FROM owner_activations
        WHERE status = 'revoked'
    ")
    ->fetchColumn();

$expiredActivations = (int) $pdo
    ->query("
        SELECT COUNT(*)
        FROM owner_activations
        WHERE status = 'expired'
           OR (
                status = 'generated'
                AND expires_at <= NOW()
           )
    ")
    ->fetchColumn();

/*
|--------------------------------------------------------------------------
| Recent Activations
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        oa.activation_id,
        oa.installation_id,
        oa.token_label,
        oa.domain_name,
        oa.status,
        oa.expires_at,
        oa.used_at,
        oa.created_at
    FROM owner_activations oa
    ORDER BY oa.activation_id DESC
    LIMIT 20
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

    <title>
        <?= htmlspecialchars(OWNER_APP_NAME) ?>
    </title>
    
    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #222;
        }

        .header {
            background: #111827;
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            margin: 0;
            font-size: 22px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .logout {
            color: white;
            text-decoration: none;
            background: #dc2626;
            padding: 9px 15px;
            border-radius: 6px;
        }

        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .welcome {
            margin-bottom: 25px;
        }

        .welcome h2 {
            margin-bottom: 5px;
        }

        .cards {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(180px, 1fr));

            gap: 18px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 22px;
            box-shadow:
                0 2px 8px rgba(0,0,0,.08);
        }

        .card h3 {
            margin: 0 0 10px;
            font-size: 14px;
            color: #6b7280;
        }

        .number {
            font-size: 30px;
            font-weight: bold;
        }

        .actions {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow:
                0 2px 8px rgba(0,0,0,.08);
        }

        .actions h2 {
            margin-top: 0;
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
        }

        .btn.secondary {
            background: #4b5563;
        }

        .table-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow:
                0 2px 8px rgba(0,0,0,.08);

            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 13px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            white-space: nowrap;
        }

        th {
            background: #f9fafb;
        }

        .status {
            padding: 5px 9px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .generated {
            background: #dcfce7;
            color: #166534;
        }

        .used {
            background: #dbeafe;
            color: #1e40af;
        }

        .revoked {
            background: #fee2e2;
            color: #991b1b;
        }

        .expired {
            background: #fef3c7;
            color: #92400e;
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
                gap: 10px;
            }
            .container {
                margin: 18px auto;
                padding: 0 14px;
            }
            .table-container,
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }

    </style>

</head>

<body>

<header class="header">

    <h1>
        OWNER CONTROL PANEL
    </h1>

    <div class="header-right">

        <span>
            <?= htmlspecialchars($ownerName) ?>
        </span>

        <a
            class="logout"
            href="login.php?logout=1"
        >
            Logout
        </a>

    </div>

</header>

<main class="container">

    <div class="welcome">

        <h2>
            Developer / Owner Dashboard
        </h2>

        <p>
            Manage Clan Management System installations,
            activations and licenses from this panel.
        </p>

    </div>


    <!-- STATISTICS -->

    <section class="cards">

        <div class="card">

            <h3>
                Total Installations
            </h3>

            <div class="number">
                <?= $totalInstallations ?>
            </div>

        </div>


        <div class="card">

            <h3>
                Total Activations
            </h3>

            <div class="number">
                <?= $totalActivations ?>
            </div>

        </div>


        <div class="card">

            <h3>
                Active Activations
            </h3>

            <div class="number">
                <?= $activeActivations ?>
            </div>

        </div>


        <div class="card">

            <h3>
                Used Activations
            </h3>

            <div class="number">
                <?= $usedActivations ?>
            </div>

        </div>


        <div class="card">

            <h3>
                Revoked
            </h3>

            <div class="number">
                <?= $revokedActivations ?>
            </div>

        </div>


        <div class="card">

            <h3>
                Expired
            </h3>

            <div class="number">
                <?= $expiredActivations ?>
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
                href="activations/generate.php"
                class="btn"
            >
                + Create New Activation
            </a>

            <a
                href="activations/index.php"
                class="btn secondary"
            >
                View Activations
            </a>

            <a
                href="installations/index.php"
                class="btn secondary"
            >
                View Installations
            </a>

        </div>

    </section>


    <!-- RECENT ACTIVATIONS -->

    <section class="table-container">

        <h2>
            Recent Activations
        </h2>

        <table>

            <thead>

                <tr>

                    <th>ID</th>

                    <th>Installation</th>

                    <th>Label</th>

                    <th>Domain</th>

                    <th>Status</th>

                    <th>Expires</th>

                    <th>Used</th>

                    <th>Created</th>

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

                    $status =
                        $activation['status'];

                    if (
                        $status === 'generated' &&
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
                            <?= (int)$activation['installation_id'] ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $activation['token_label']
                                ?? '-'
                            ) ?>
                        </td>

                        <td>

                            <?php

                            $clanUrl = clanAccessUrl(
                                $activation['domain_name'] ?? '-'
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
                                $activation['used_at']
                                ?? '-'
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $activation['created_at']
                            ) ?>
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