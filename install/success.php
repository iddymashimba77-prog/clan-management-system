<?php
declare(strict_types=1);

session_start();


if (
    empty($_SESSION['installation_completed']) ||
    empty($_SESSION['administrator_user_id'])
) {
    header('Location: index.php');
    exit;
}


$administratorUserId =
    (int)$_SESSION['administrator_user_id'];

$administratorUsername =
    $_SESSION['administrator_username'] ?? '';

$databaseName =
    $_SESSION['database_name'] ?? '';

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Installation Complete</title>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>


<body class="hold-transition login-page">

<div class="login-box"
     style="width: 550px;">

    <div class="card card-outline card-success">

        <div class="card-header text-center">

            <h3 class="font-weight-bold">

                <i class="fas fa-check-circle text-success mr-2"></i>

                Installation Complete

            </h3>

        </div>


        <div class="card-body">


            <div class="text-center mb-4">

                <i class="fas fa-circle-check text-success"
                   style="font-size:70px;"></i>

            </div>


            <div class="alert alert-success">

                <strong>
                    Production installation completed successfully.
                </strong>

                <br>

                Your clan system is now ready to use.

            </div>


            <table class="table table-bordered">

                <tr>

                    <th>
                        Database
                    </th>

                    <td>
                        <?= htmlspecialchars($databaseName) ?>
                    </td>

                </tr>


                <tr>

                    <th>
                        Administrator
                    </th>

                    <td>
                        <?= htmlspecialchars($administratorUsername) ?>
                    </td>

                </tr>


                <tr>

                    <th>
                        User ID
                    </th>

                    <td>
                        <?= $administratorUserId ?>
                    </td>

                </tr>


                <tr>

                    <th>
                        Role
                    </th>

                    <td>

                        <span class="badge badge-primary">

                            Administrator

                        </span>

                    </td>

                </tr>

            </table>


            <div class="alert alert-warning">

                <i class="fas fa-lock mr-1"></i>

                <strong>Installation has been locked.</strong>

                <br>

                The installer cannot be used again unless the
                system is deliberately reset by an authorized
                procedure.

            </div>


            <div class="alert alert-info">

                <i class="fas fa-users mr-1"></i>

                Your clan data is currently empty.

                The Administrator can now add the clan members,
                leadership users, family relationships and other
                clan information.

            </div>


            <a
                href="../index.php"
                class="btn btn-primary btn-lg btn-block"
            >

                <i class="fas fa-home mr-1"></i>

                Go to Clan Management System

            </a>


        </div>

    </div>

</div>

</body>
</html>