<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/constants.php';
include 'includes/header.php';
?>



<div class="login-page">

    <div class="login-container">

        <div class="login-logo">

            <!-- Baadaye hapa itawekwa logo kutoka database -->
            <h1><?= APP_NAME; ?></h1>

        </div>

        <div class="login-header">

            <h2>Welcome Back</h2>

            <p>Sign in to continue.</p>

        </div>

        <form action="login_process.php" method="POST" autocomplete="off">

            <div class="form-group">

                <label>
                    Member Code / Username / Email / Phone
                </label>

                <input
                    type="text"
                    name="login"
                    placeholder="Enter your login details"
                    required
                >

            </div>

            <div class="form-group">

                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                >

            </div>

            <button type="submit" class="btn btn-primary login-btn">

                Login

            </button>

        </form>

        <div class="login-footer">

            <a href="#">
                Forgot Password?
            </a>

        </div>

    </div>

</div>

<?php include 'includes/footer.php'; ?>