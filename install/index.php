<?php
declare(strict_types=1);

session_start();

/*
|--------------------------------------------------------------------------
| INSTALLATION LOCK
|--------------------------------------------------------------------------
*/

$lockFile = dirname(__DIR__) . '/storage/installed.lock';

if (is_file($lockFile)) {
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Installation Locked</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet"
              href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
        <link rel="stylesheet"
              href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    </head>

    <body class="hold-transition login-page">

    <div class="login-box">

        <div class="card card-outline card-primary">

            <div class="card-header text-center">
                <h3>
                    <i class="fas fa-shield-alt text-primary"></i>
                    Clan Management System
                </h3>
            </div>

            <div class="card-body text-center">

                <i class="fas fa-lock fa-3x text-danger mb-3"></i>

                <h4>Installation Locked</h4>

                <p class="text-muted">
                    This system has already been installed.
                </p>

                <p class="text-muted">
                    Please use the main system login page.
                </p>

                <a href="../index.php"
                   class="btn btn-primary">
                    <i class="fas fa-home mr-1"></i>
                    Go to Home
                </a>

            </div>

        </div>

    </div>

    </body>
    </html>
    <?php
    exit;
}

$error = $_SESSION['install_error'] ?? '';
unset($_SESSION['install_error']);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Clan Management System Installation</title>

    <style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    min-height: 100vh;
    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background:
        linear-gradient(
            135deg,
            #f4f7fb,
            #e9eef5
        );

    color: #212529;
}

.install-container {
    width: 100%;
    max-width: 850px;
    margin: 50px auto;
    padding: 20px;
}

.install-card {
    background: #ffffff;
    border-radius: 14px;
    box-shadow:
        0 10px 35px rgba(0, 0, 0, 0.10);

    overflow: hidden;
}

.install-header {
    background: #1f4e79;
    color: #ffffff;
    padding: 30px;
    text-align: center;
}

.install-header h1 {
    margin: 0 0 8px;
    font-size: 30px;
    font-weight: 700;
}

.install-header h2 {
    margin: 0;
    font-size: 19px;
    font-weight: 400;
    opacity: 0.95;
}

.install-header p {
    margin: 15px 0 0;
    font-size: 14px;
    opacity: 0.9;
}

.install-body {
    padding: 30px;
}

.alert {
    padding: 15px 18px;
    border-radius: 8px;
    margin-bottom: 20px;
    line-height: 1.5;
}

.alert-danger {
    background: #fdeaea;
    color: #842029;
    border: 1px solid #f5c2c7;
}

.alert-success {
    background: #eaf7ee;
    color: #146c43;
    border: 1px solid #badbcc;
}

.section-title {
    font-size: 18px;
    font-weight: 700;
    margin: 25px 0 15px;
    color: #1f4e79;
}

label {
    display: block;
    font-weight: 600;
    margin-bottom: 6px;
}

input {
    width: 100%;
    padding: 11px 13px;
    border: 1px solid #ced4da;
    border-radius: 7px;
    font-size: 15px;
}

input:focus {
    outline: none;
    border-color: #1f4e79;
    box-shadow:
        0 0 0 3px rgba(31, 78, 121, 0.12);
}

.form-group {
    margin-bottom: 18px;
}

.btn-install {
    width: 100%;
    padding: 13px;
    border: 0;
    border-radius: 8px;
    background: #1f4e79;
    color: #ffffff;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
}

.btn-install:hover {
    background: #173a5c;
}

.warning-box {
    margin-top: 20px;
    padding: 15px;
    border-left: 4px solid #f0ad4e;
    background: #fff8e6;
    color: #664d03;
    border-radius: 5px;
}

@media (max-width: 600px) {

    .install-container {
        margin: 15px auto;
        padding: 10px;
    }

    .install-header {
        padding: 22px 15px;
    }

    .install-header h1 {
        font-size: 24px;
    }

    .install-header h2 {
        font-size: 17px;
    }

    .install-body {
        padding: 20px;
    }
}

</style>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body class="hold-transition login-page">

<div class="login-box"
     style="width: 550px;">

    <div class="card card-outline card-primary">

        <div class="card-header text-center">

            <h3 class="font-weight-bold">

                <i class="fas fa-users text-primary mr-2"></i>

                Clan Management System

            </h3>

            <small class="text-muted">
                Production Installation
            </small>

        </div>

        <div class="card-body">

            <?php if ($error !== ''): ?>

                <div class="alert alert-danger">

                    <i class="fas fa-exclamation-triangle mr-1"></i>

                    <?= htmlspecialchars($error) ?>

                </div>

            <?php endif; ?>


            <div class="alert alert-info">

                <i class="fas fa-info-circle mr-1"></i>

                This installation creates a clean production
                environment for one clan.

                Existing test clan/member data must not be imported.

            </div>


            <form method="POST"
                  action="process.php">


                <h5 class="text-primary">
                    <i class="fas fa-database mr-1"></i>
                    Database
                </h5>

                <hr>


                <div class="form-group">

                    <label>
                        Database Host
                    </label>

                    <input
                        type="text"
                        name="db_host"
                        class="form-control"
                        value="localhost"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Database Name
                    </label>

                    <input
                        type="text"
                        name="db_name"
                        class="form-control"
                        value="clan_management_system"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Database Username
                    </label>

                    <input
                        type="text"
                        name="db_user"
                        class="form-control"
                        value="root"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Database Password
                    </label>

                    <input
                        type="password"
                        name="db_password"
                        class="form-control"
                    >

                </div>


                <h5 class="text-primary mt-4">

                    <i class="fas fa-user-shield mr-1"></i>

                    First Administrator

                </h5>

                <hr>


                <div class="row">

                    <div class="col-md-6">

                        <div class="form-group">

                            <label>
                                First Name
                            </label>

                            <input
                                type="text"
                                name="first_name"
                                class="form-control"
                                required
                            >

                        </div>

                    </div>


                    <div class="col-md-6">

                        <div class="form-group">

                            <label>
                                Last Name
                            </label>

                            <input
                                type="text"
                                name="last_name"
                                class="form-control"
                                required
                            >

                        </div>

                    </div>

                </div>


                <div class="form-group">

                    <label>
                        Username
                    </label>

                    <input
                        type="text"
                        name="username"
                        class="form-control"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        required
                    >

                </div>


                <div class="row">

                    <div class="col-md-6">

                        <div class="form-group">

                            <label>
                                Password
                            </label>

                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                minlength="8"
                                required
                            >

                        </div>

                    </div>


                    <div class="col-md-6">

                        <div class="form-group">

                            <label>
                                Confirm Password
                            </label>

                            <input
                                type="password"
                                name="password_confirmation"
                                class="form-control"
                                minlength="8"
                                required
                            >

                        </div>

                    </div>

                </div>


                <div class="alert alert-warning mt-3">

                    <div class="custom-control custom-checkbox">

                        <input
                            type="checkbox"
                            class="custom-control-input"
                            id="confirm"
                            name="confirm"
                            value="1"
                            required
                        >

                        <label
                            class="custom-control-label"
                            for="confirm"
                        >

                            I understand that this is a production
                            installation and the installer will be
                            locked after successful installation.

                        </label>

                    </div>

                </div>


                <button
                    type="submit"
                    class="btn btn-primary btn-block btn-lg"
                >

                    <i class="fas fa-download mr-1"></i>

                    Install Production System

                </button>

            </form>

        </div>

    </div>

</div>

</body>
</html>