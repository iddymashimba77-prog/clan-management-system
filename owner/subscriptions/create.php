<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/maindb.php';

requireOwnerLogin();

$clanDatabase = new OwnerClanDatabase();
$clanPdo = $clanDatabase->connect();

$clans = $clanPdo->query("
    SELECT clan_id, clan_name, clan_code, subscription_status
    FROM clans
    ORDER BY clan_name ASC
")->fetchAll();

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

<title>New Subscription - Owner Panel</title>

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

.btn.dark {
    background: #374151;
}

.btn.red {
    background: #dc2626;
}

.container {
    max-width: 860px;
    margin: 30px auto;
    padding: 0 22px;
}

.page-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 25px;
}

.page-header h2 {
    margin: 0;
    font-size: 25px;
}

.form-card {
    background: white;
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,.07);
}

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 7px;
}

input,
select {
    width: 100%;
    padding: 11px 13px;
    border: 1px solid #d1d5db;
    border-radius: 7px;
    font-size: 14px;
    background: white;
}

input:focus,
select:focus {
    outline: none;
    border-color: #2563eb;
}

.hint {
    font-size: 12px;
    color: #6b7280;
    margin-top: 6px;
}

.row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 26px;
}

@media (max-width: 767.98px) {

    .header {
        flex-wrap: wrap;
        gap: 10px;
        padding: 14px 16px;
    }

    .header-right {
        flex-wrap: wrap;
        width: 100%;
    }

    .container {
        margin: 16px auto;
        padding: 0 12px;
    }

    .row {
        grid-template-columns: 1fr;
        gap: 14px;
    }

    .form-card {
        padding: 20px 16px;
    }

    .form-actions {
        flex-direction: column;
        gap: 12px;
        align-items: stretch;
    }

    .form-actions .btn {
        text-align: center;
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

    <div class="page-header">

        <a href="index.php" class="btn dark">← Back</a>

        <h2>
            New Subscription
        </h2>

    </div>


    <div class="form-card">

        <form action="store.php" method="POST">

            <?= ownerCsrfField() ?>

            <div class="form-group">

                <label>Clan</label>

                <select name="clan_id" required>

                    <option value="">
                        -- Select Clan --
                    </option>

                    <?php foreach ($clans as $clan): ?>

                        <option value="<?= (int)$clan['clan_id'] ?>">
                            <?= htmlspecialchars(
                                $clan['clan_name']
                            ) ?>
                            (<?= htmlspecialchars($clan['clan_code']) ?>)
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="form-group">

                <label>Plan Name</label>

                <input
                    type="text"
                    name="plan_name"
                    placeholder="e.g. Basic, Premium, Enterprise"
                    required
                >

            </div>


            <div class="row">

                <div class="form-group">

                    <label>Subscription Status</label>

                    <select name="subscription_status">

                        <option value="trial">Trial</option>

                        <option value="active" selected>Active</option>

                        <option value="expired">Expired</option>

                        <option value="suspended">Suspended</option>

                        <option value="cancelled">Cancelled</option>

                    </select>

                </div>

                <div class="form-group">

                    <label>Max Members</label>

                    <input
                        type="number"
                        name="max_members"
                        min="0"
                        value="0"
                    >

                    <div class="hint">
                        0 = unlimited
                    </div>

                </div>

            </div>


            <div class="row">

                <div class="form-group">

                    <label>Subscription Expires At</label>

                    <input
                        type="date"
                        name="subscription_expires_at"
                    >

                </div>

                <div class="form-group">

                    <label>Activation Status</label>

                    <select name="activation_status">

                        <option value="pending">Pending</option>

                        <option value="active" selected>Active</option>

                        <option value="suspended">Suspended</option>

                        <option value="revoked">Revoked</option>

                    </select>

                </div>

            </div>


            <div class="form-actions">

                <a
                    href="index.php"
                    class="btn dark"
                >
                    Cancel
                </a>

                <button type="submit" class="btn green">
                    Create Subscription
                </button>

            </div>

        </form>

    </div>

</main>

</body>

</html>
