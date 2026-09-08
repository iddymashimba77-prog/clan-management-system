<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/constants.php';

$token =
    trim(
        (string)($_GET['token'] ?? '')
    );

$error = '';

if ($token === '') {

    $error =
        'No activation token was provided.';
}

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
        Clan Management System Activation
    </title>

    <style>

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        .activation-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .activation-card {
            width: 100%;
            max-width: 520px;
            background: #fff;
            padding: 35px;
            border-radius: 14px;
            box-shadow: 0 10px 35px rgba(0,0,0,.08);
        }

        h1 {
            margin-top: 0;
        }

        p {
            color: #555;
            line-height: 1.6;
        }

        label {
            display: block;
            margin-top: 18px;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        button {
            width: 100%;
            margin-top: 25px;
            padding: 13px;
            border: 0;
            border-radius: 8px;
            background: #1769aa;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #12578d;
        }

        .error {
            padding: 12px;
            background: #ffe5e5;
            color: #a00000;
            border-radius: 8px;
        }

        .security {
            margin-top: 20px;
            font-size: 13px;
            color: #777;
        }

    </style>

</head>

<body>

<div class="activation-wrapper">

    <div class="activation-card">

        <h1>
            Clan Management System
        </h1>

        <h2>
            Installation Activation
        </h2>

        <p>
            Enter the client information below to activate
            this installation.
        </p>

        <?php if ($error !== ''): ?>

            <div class="error">
                <?= htmlspecialchars(
                    $error,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </div>

        <?php else: ?>

            <form
                method="POST"
                action="process.php"
            >

                <input
                    type="hidden"
                    name="token"
                    value="<?= htmlspecialchars(
                        $token,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >

                <label>
                    Client / Organization Name
                </label>

                <input
                    type="text"
                    name="client_name"
                    required
                    maxlength="200"
                    placeholder="Enter organization name"
                >

                <label>
                    Client Email
                </label>

                <input
                    type="email"
                    name="client_email"
                    maxlength="255"
                    placeholder="client@example.com"
                >

                <button type="submit">
                    Activate Installation
                </button>

            </form>

            <div class="security">

                🔐 This activation token is one-time use
                and is bound to this installation domain.

            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>