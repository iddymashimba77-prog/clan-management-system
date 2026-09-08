<?php
declare(strict_types=1);

session_start();

$error = $_SESSION['reset_error'] ?? '';

unset($_SESSION['reset_error']);
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Reset Clan System</title>

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <style>

        body {
            background: #f4f6f9;
        }

        .reset-wrapper {

            min-height: 100vh;

            display: flex;

            justify-content: center;

            align-items: center;

            padding: 30px;
        }

        .reset-card {

            width: 100%;

            max-width: 650px;

            border: 0;

            border-radius: 12px;

            box-shadow:
                0 5px 25px rgba(0,0,0,.10);
        }

    </style>

</head>

<body>

<div class="reset-wrapper">

    <div class="card reset-card">

        <div class="card-header bg-danger text-white">

            <h4 class="mb-0">

                <i class="fas fa-exclamation-triangle mr-2"></i>

                Reset Clan Data

            </h4>

        </div>


        <div class="card-body">


            <?php if ($error): ?>

                <div class="alert alert-danger">

                    <?= htmlspecialchars($error) ?>

                </div>

            <?php endif; ?>


            <div class="alert alert-danger">

                <strong>WARNING</strong>

                <p class="mb-0 mt-2">

                    This operation permanently removes
                    clan/member data.

                </p>

            </div>


            <h5>Data that will be removed</h5>

            <ul>

                <li>Members</li>

                <li>Family relationships</li>

                <li>Contributions</li>

                <li>Contribution payments</li>

                <li>Wallets</li>

                <li>Wallet payments</li>

                <li>Events</li>

                <li>Messages</li>

                <li>Gallery/media</li>

                <li>Income and expenses</li>

                <li>Notifications</li>

            </ul>


            <div class="alert alert-success">

                <strong>System data will remain:</strong>

                <ul class="mb-0">

                    <li>Roles</li>

                    <li>Permissions</li>

                    <li>Role permissions</li>

                    <li>Relationship types</li>

                </ul>

            </div>


            <form
                method="POST"
                action="process.php"
            >

                <div class="form-group">

                    <label>

                        Administrator Username

                    </label>

                    <input
                        type="text"
                        name="admin_username"
                        class="form-control"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>

                        Administrator Password

                    </label>

                    <input
                        type="password"
                        name="admin_password"
                        class="form-control"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>

                        Type
                        <strong>RESET CLAN</strong>
                        to continue

                    </label>

                    <input
                        type="text"
                        name="confirmation"
                        class="form-control"
                        autocomplete="off"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="btn btn-danger btn-lg btn-block"
                >

                    <i class="fas fa-trash-alt mr-2"></i>

                    Reset Clan Data

                </button>

            </form>

        </div>

    </div>

</div>

</body>

</html>